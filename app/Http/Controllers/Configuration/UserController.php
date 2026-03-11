<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentLevel;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function create()
    {
        return redirect()->route('configuration.users.index', ['create' => true]);
    }

    public function index(Request $request)
    {
        $query = User::with(['departments', 'topics']);

        if (!$request->user()->isSuperAdmin()) {
            $query->excludeSuperAdmin();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', function($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->filled('topic_id')) {
            $query->whereHas('topics', function($q) use ($request) {
                $q->where('topics.id', $request->topic_id);
            });
        }

        return Inertia::render('Configuration/Users/Index', [
            'users' => $query->orderBy('name')->get(),
            'departments' => Department::with(['levels', 'topics'])->orderBy('name')->get(),
            'topics' => Topic::orderBy('name')->get(),
            'filters' => $request->only(['search', 'department_id', 'topic_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'job_title' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'is_manager' => 'boolean',
        ]);

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'job_title' => $request->job_title,
            'password' => Hash::make($temporaryPassword),
            'is_admin' => $request->is_admin ?? false,
            'is_manager' => $request->is_manager ?? false,
            'auth_type' => 'local',
            'must_reset_password' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('message', "User created successfully. Temporary password: {$temporaryPassword}");
    }

    public function update(Request $request, User $user)
    {
        if ($user->isSuperAdmin() && !$request->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'memberships' => 'array',
            'memberships.*.department_id' => 'required|exists:departments,id',
            'memberships.*.level_id' => 'nullable|exists:department_levels,id',
            'topic_ids' => 'array',
            'topic_ids.*' => 'exists:topics,id',
            'is_admin' => 'boolean',
            'is_manager' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'is_admin' => $request->is_admin,
            'is_manager' => $request->is_manager,
            'is_active' => $request->is_active,
            'job_title' => $request->job_title,
            'must_reset_password' => $request->must_reset_password ?? $user->must_reset_password,
        ]);

        // Sync departments with levels
        $syncData = [];
        foreach ($request->memberships as $membership) {
            $syncData[$membership['department_id']] = [
                'level_id' => $membership['level_id'] ?? null
            ];
        }
        $user->departments()->sync($syncData);

        if ($request->has('topic_ids')) {
            $user->topics()->sync($request->topic_ids);
        }

        return redirect()->back()->with('message', 'User updated successfully.');
    }
}
