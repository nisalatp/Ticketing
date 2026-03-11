<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SetupController extends Controller
{
    private function authorize()
    {
        if (session('is_setup_admin')) {
            return;
        }

        if (!auth()->check()) {
            abort(redirect()->route('login'));
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(redirect('/'));
        }
    }

    public function index()
    {
        $this->authorize();
        // Set session flag so the user stays authorized through the setup flow,
        // even if the session driver changes from database to file.
        session(['is_setup_admin' => true]);
        return Inertia::render('Setup');
    }

    public function configure(Request $request)
    {
        $this->authorize();
        $validated = $request->validate([
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'host' => 'required|string',
            'app_url' => 'required|url',
        ]);

        try {
            $this->updateEnv([
                'DB_DATABASE' => $validated['database'],
                'DB_USERNAME' => $validated['username'],
                'DB_PASSWORD' => $validated['password'] ?? '',
                'DB_HOST' => $validated['host'],
                'APP_URL' => $validated['app_url'],
                'OAUTH_REDIRECT_URI' => rtrim($validated['app_url'], '/') . '/callback',
                'OAUTH_APP_ID' => config('services.microsoft.client_id', 'YOUR_APP_ID'),
                'OAUTH_APP_SECRET' => config('services.microsoft.client_secret', 'YOUR_APP_SECRET'),
                'OAUTH_SCOPES' => 'openid profile offline_access user.read',
                'OAUTH_AUTHORITY' => 'https://login.microsoftonline.com/DrSulaimanAlHabib.onmicrosoft.com',
                'OAUTH_AUTHORIZE_ENDPOINT' => '/oauth2/v2.0/authorize',
                'OAUTH_TOKEN_ENDPOINT' => '/oauth2/v2.0/token',
                'SESSION_DRIVER' => 'file',
            ]);

            Artisan::call('config:clear');

            // Re-set the session flag after config clear to persist auth through the flow
            session(['is_setup_admin' => true]);

            return response()->json(['message' => 'Configuration updated. Session driver switched to file for resilience.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkState()
    {
        $this->authorize();
        try {
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);

            $hasData = false;
            if ($tableCount > 0) {
                try {
                    $hasData = DB::table('users')->count() > 0;
                } catch (\Exception $e) {
                    $hasData = false;
                }
            }

            return response()->json([
                'table_count' => $tableCount,
                'has_data' => $hasData,
                'tables' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'table_count' => 0,
                'has_data' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function backup()
    {
        $this->authorize();
        try {
            $backupDir = storage_path('backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = $backupDir . '/' . $filename;

            $this->dumpDatabase($path);

            return response()->json([
                'message' => 'Backup created successfully: ' . $filename,
                'filename' => $filename,
                'download_url' => route('setup.download-backup', ['filename' => $filename])
            ]);
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Backup failed: ' . $e->getMessage()], 500);
        }
    }

    public function downloadBackup($filename)
    {
        $this->authorize();
        $path = storage_path('backups/' . $filename);
        if (File::exists($path)) {
            return response()->download($path);
        }
        abort(404);
    }

    public function initialize()
    {
        $this->authorize();
        try {
            // Use Laravel's migrate:fresh to recreate all tables
            Artisan::call('migrate:fresh', ['--force' => true]);

            // Create the super admin user
            $adminEmail = config('app.super_admin_email', 'nisala.bandara@cloudsolutions.com.sa');
            $adminName = session('admin_name', 'Administrator');

            \App\Models\User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => bcrypt(str()->random(16)),
                ]
            );

            return response()->json(['message' => 'Database initialized successfully with fresh migrations and admin account created!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function dumpDatabase($path)
    {
        $tables = DB::select('SHOW TABLES');

        $sql = "-- Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableArray = (array)$table;
            $tableName = reset($tableArray);

            try {
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createTableArray = (array)$createTable;
                $createKey = array_key_exists('Create Table', $createTableArray) ? 'Create Table' : (array_key_exists('Create View', $createTableArray) ? 'Create View' : null);

                if (!$createKey) {
                    Log::warning("Could not find create SQL for table/view: {$tableName}");
                    continue;
                }

                $createTableSql = $createTableArray[$createKey];

                $sql .= "-- Table: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTableSql . ";\n\n";

                if ($createKey === 'Create Table') {
                    $rows = DB::table($tableName)->get();
                    if ($rows->count() > 0) {
                        $sql .= "INSERT INTO `{$tableName}` VALUES \n";
                        $insertRows = [];
                        foreach ($rows as $row) {
                            $values = array_map(function($value) {
                                if ($value === null) return 'NULL';
                                return DB::getPdo()->quote($value);
                            }, (array)$row);
                            $insertRows[] = "(" . implode(', ', $values) . ")";
                        }
                        $sql .= implode(",\n", $insertRows) . ";\n\n";
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to dump table {$tableName}: " . $e->getMessage());
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($path, $sql);
    }

    private function updateEnv(array $values)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            File::put($path, "");
        }

        $content = File::get($path);

        foreach ($values as $key => $value) {
            if (strpos($value, ' ') !== false && strpos($value, '"') === false) {
                $value = '"' . $value . '"';
            }

            $line = "{$key}={$value}";

            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=(.*)$/m",
                    $line,
                    $content
                );
            } else {
                $content .= "\n{$line}";
            }
        }

        $content = preg_replace("/\n\n+/", "\n\n", $content);

        File::put($path, trim($content) . "\n");
    }
}
