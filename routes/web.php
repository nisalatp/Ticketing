<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'error' => session('error'),
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client Ticketing
    Route::resource('tickets', \App\Http\Controllers\TicketController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('tickets/{ticket}/messages', [\App\Http\Controllers\TicketController::class, 'storeMessage'])->name('tickets.store-message');

    // Knowledge Base
    Route::get('/knowledge-base', [\App\Http\Controllers\KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    Route::get('/knowledge-base/{slug}', [\App\Http\Controllers\KnowledgeBaseController::class, 'show'])->name('knowledge-base.show');

    // Notifications
    Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Configuration / Master Data
    Route::prefix('configuration')->name('configuration.')->group(function () {
        Route::resource('departments', \App\Http\Controllers\Configuration\DepartmentController::class);
        Route::resource('users', \App\Http\Controllers\Configuration\UserController::class);
        Route::resource('topics', \App\Http\Controllers\Configuration\TopicController::class);
        Route::resource('levels', \App\Http\Controllers\Configuration\DepartmentLevelController::class);
        Route::post('levels/reorder', [\App\Http\Controllers\Configuration\DepartmentLevelController::class, 'reorder'])->name('levels.reorder');
        Route::resource('ticket-types', \App\Http\Controllers\Configuration\TicketTypeController::class);
        Route::resource('sla-policies', \App\Http\Controllers\Configuration\SlaPolicyController::class);
        Route::resource('transfer-reasons', \App\Http\Controllers\Configuration\TransferReasonController::class);
    });

    // Agent Workspace
    Route::prefix('agent')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Agent\AgentDashboardController::class, 'index'])->name('agent.dashboard');
        Route::get('/previous-tickets', [App\Http\Controllers\Agent\AgentDashboardController::class, 'previousTickets'])->name('agent.previous-tickets');
        Route::get('/workload', [App\Http\Controllers\Agent\WorkloadController::class, 'index'])->name('agent.workload');
        Route::put('/tickets/{ticket}/status', [App\Http\Controllers\Agent\AgentDashboardController::class, 'updateStatus'])->name('agent.tickets.update-status');
        Route::post('/tickets/{ticket}/messages', [App\Http\Controllers\Agent\AgentDashboardController::class, 'storeMessage'])->name('agent.tickets.store-message');
        Route::put('/tickets/{ticket}/escalate', [App\Http\Controllers\Agent\AgentDashboardController::class, 'escalate'])->name('agent.tickets.escalate');
        
        // Peer-to-Peer Transfers
        Route::post('/tickets/{ticket}/transfers', [App\Http\Controllers\Agent\AgentDashboardController::class, 'initiateTransfer'])->name('agent.tickets.transfers.initiate');
        Route::put('/tickets/{ticket}/transfers/accept', [App\Http\Controllers\Agent\AgentDashboardController::class, 'acceptTransfer'])->name('agent.tickets.transfers.accept');
        Route::put('/tickets/{ticket}/transfers/reject', [App\Http\Controllers\Agent\AgentDashboardController::class, 'rejectTransfer'])->name('agent.tickets.transfers.reject');
        Route::put('/tickets/{ticket}/transfers/cancel', [App\Http\Controllers\Agent\AgentDashboardController::class, 'cancelTransfer'])->name('agent.tickets.transfers.cancel');
    });

    // Administration
    Route::prefix('admin')->name('admin.')->group(function () {
        // Ticket Management (Replaces Assignment)
        Route::get('/tickets', [\App\Http\Controllers\Admin\TicketManagementController::class, 'index'])->name('tickets.index');
        Route::put('/tickets/{ticket}/assign', [\App\Http\Controllers\Admin\TicketManagementController::class, 'assign'])->name('tickets.assign');
        Route::delete('/tickets/{ticket}', [\App\Http\Controllers\Admin\TicketManagementController::class, 'destroy'])->name('tickets.destroy');
        
        Route::get('/agent-performance', [\App\Http\Controllers\Admin\AgentPerformanceController::class, 'index'])->name('agent-performance.index');
    });
});

// Database Setup Routes
Route::get('/setup', [\App\Http\Controllers\SetupController::class, 'index'])->name('setup.index');
Route::post('/setup/configure', [\App\Http\Controllers\SetupController::class, 'configure'])->name('setup.configure');
Route::post('/setup/check-state', [\App\Http\Controllers\SetupController::class, 'checkState'])->name('setup.check-state');
Route::post('/setup/backup', [\App\Http\Controllers\SetupController::class, 'backup'])->name('setup.backup');
Route::get('/setup/download-backup/{filename}', [\App\Http\Controllers\SetupController::class, 'downloadBackup'])->name('setup.download-backup');
Route::post('/setup/initialize', [\App\Http\Controllers\SetupController::class, 'initialize'])->name('setup.initialize');

require __DIR__.'/auth.php';
