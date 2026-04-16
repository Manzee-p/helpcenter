<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\ClientSettingsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatusBoardController;
use App\Http\Controllers\StatusPageController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketFileController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorRatingController;
use App\Http\Controllers\VendorReportController;
use App\Http\Controllers\VendorSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// PUBLIC
Route::get('/', fn () => view('welcome'));

Route::get('/status', [StatusPageController::class, 'index'])->name('status');
Route::get('/status/{id}', [StatusPageController::class, 'show'])->name('status.detail');

Route::post('/auth/google/callback', [GoogleController::class, 'handleGoogle'])->name('auth.google.callback');

Auth::routes(['register' => false]);

// AUTHENTICATED
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/attachments/ticket/{attachment}', [TicketFileController::class, 'viewTicketAttachment'])
        ->name('attachments.ticket.view');
    Route::get('/attachments/additional-info/{additionalInfo}', [TicketFileController::class, 'viewAdditionalInfoAttachment'])
        ->name('attachments.additional-info.view');
    Route::get('/attachments/completion-proof/{ticket}', [TicketFileController::class, 'viewCompletionProof'])
        ->name('attachments.completion-proof.view');

    // NOTIFICATIONS
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ADMIN
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Tickets
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::patch('/tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign');
        Route::patch('/tickets/{ticket}/update-status', [AdminTicketController::class, 'updateStatus'])->name('tickets.update-status');
        Route::patch('/tickets/{ticket}/update-priority', [AdminTicketController::class, 'updatePriority'])->name('tickets.update-priority');
        Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])->name('tickets.destroy');
        Route::get('/ticket-deletion-requests', [AdminTicketController::class, 'deletionRequests'])->name('ticket-deletion-requests.index');
        Route::get('/ticket-deletion-requests/{id}', [AdminTicketController::class, 'showDeletionRequest'])->name('ticket-deletion-requests.show');
        Route::post('/ticket-deletion-requests/{id}/process', [AdminTicketController::class, 'processDeletionRequest'])->name('ticket-deletion-requests.process');
        Route::get('/reassign-requests', [AdminTicketController::class, 'reassignRequests'])->name('reassign-requests.index');
        Route::get('/reassign-requests/{id}', [AdminTicketController::class, 'showReassignRequest'])->name('reassign-requests.show');
        Route::post('/reassign-requests/{id}/process', [AdminTicketController::class, 'processReassignRequest'])->name('reassign-requests.process');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Vendors
        Route::get('/vendors', [AdminController::class, 'getVendors'])->name('vendors.index');
        Route::get('/vendors/{vendor}', [AdminController::class, 'getVendorDetail'])->name('vendors.show');

        // Vendor ratings
        Route::get('/vendor-ratings', [VendorRatingController::class, 'adminIndex'])->name('vendor-ratings.index');
        Route::delete('/vendor-ratings/{id}', [VendorRatingController::class, 'destroy'])->name('vendor-ratings.destroy');
        Route::post('/vendor-ratings/{vendorId}/warning', [VendorRatingController::class, 'sendAdminWarning'])->name('vendor-ratings.warning');

        // Categories
        Route::get('/categories', [TicketCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [TicketCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [TicketCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [TicketCategoryController::class, 'destroy'])->name('categories.destroy');

        // Analytics & reports
        Route::get('/analytics', [AdminController::class, 'getAnalytics'])->name('analytics');
        Route::get('/reports', [AdminController::class, 'getSystemReports'])->name('reports');

        // Status Board
        Route::get('/status-board', [StatusBoardController::class, 'index'])->name('status-board.index');
        Route::get('/status-board/create', [StatusBoardController::class, 'create'])->name('status-board.create');
        Route::get('/status-board/{id}', [StatusBoardController::class, 'detail'])->name('status-board.show');
        Route::post('/status-board', [StatusBoardController::class, 'store'])->name('status-board.store');
        Route::put('/status-board/{id}', [StatusBoardController::class, 'update'])->name('status-board.update');
        Route::delete('/status-board/{id}', [StatusBoardController::class, 'destroy'])->name('status-board.destroy');
        Route::post('/status-board/{id}/updates', [StatusBoardController::class, 'addUpdate'])->name('status-board.add-update');

        // Settings
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::post('/profile', [AdminSettingsController::class, 'updateProfile'])->name('profile');
            Route::delete('/avatar', [AdminSettingsController::class, 'deleteAvatar'])->name('avatar.delete');
            Route::post('/password', [AdminSettingsController::class, 'changePassword'])->name('password');
            Route::get('/login-history', [AdminSettingsController::class, 'getLoginHistory'])->name('login-history');
            Route::get('/sessions', [AdminSettingsController::class, 'getSessions'])->name('sessions');
            Route::get('/activity-logs', [AdminSettingsController::class, 'getActivityLogs'])->name('activity-logs');
        });
    });

    // VENDOR
    Route::middleware('role:vendor')->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tickets', [VendorController::class, 'myTickets'])->name('tickets.index');
        Route::get('/history', [VendorController::class, 'history'])->name('history');
        Route::get('/reports', [VendorReportController::class, 'index'])->name('reports');
        Route::get('/ratings', [VendorRatingController::class, 'vendorIndex'])->name('ratings');
        Route::get('/settings', [VendorSettingsController::class, 'index'])->name('settings');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::post('/profile', [VendorSettingsController::class, 'updateProfile'])->name('profile');
            Route::delete('/avatar', [VendorSettingsController::class, 'deleteAvatar'])->name('avatar.delete');
            Route::post('/password', [VendorSettingsController::class, 'changePassword'])->name('password');
            Route::get('/login-history', [VendorSettingsController::class, 'getLoginHistory'])->name('login-history');
            Route::get('/sessions', [VendorSettingsController::class, 'getSessions'])->name('sessions');
            Route::get('/activity-logs', [VendorSettingsController::class, 'getActivityLogs'])->name('activity-logs');
        });
        Route::get('/tickets/{ticket}', [VendorController::class, 'show'])->name('tickets.show');
        Route::patch('/tickets/{ticket}/status', [VendorController::class, 'updateTicketStatus'])->name('tickets.update-status');
        Route::post('/tickets/{ticket}/request-reassign', [VendorController::class, 'requestReassign'])->name('tickets.request-reassign');
        Route::get('/ticket-stats', [VendorController::class, 'ticketStats'])->name('ticket-stats');
    });

    // CLIENT
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

        // Tickets CRUD (keep /create before /{ticket})
        Route::get('/tickets', [ClientController::class, 'myTickets'])->name('tickets.index');
        Route::get('/tickets/create', [ClientController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [ClientController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [ClientController::class, 'show'])->name('tickets.show');

        // Ticket actions
        Route::post('/tickets/{ticket}/feedback', [ClientController::class, 'storeFeedback'])->name('tickets.feedback');
        Route::post('/tickets/{ticket}/additional-info', [ClientController::class, 'submitAdditionalInfo'])->name('tickets.additional-info');
        Route::post('/tickets/{ticket}/deletion-request', [ClientController::class, 'submitDeletionRequest'])->name('tickets.deletion-request');

        // History & misc
        Route::get('/history', [ClientController::class, 'ticketHistory'])->name('history');
        Route::get('/pending-ratings', [ClientController::class, 'pendingRatings'])->name('pending-ratings');
        Route::get('/settings', [ClientSettingsController::class, 'index'])->name('settings');

        // Settings actions
        Route::post('/settings/avatar', [ClientSettingsController::class, 'updateAvatar'])->name('settings.avatar');
        Route::post('/settings/profile', [ClientSettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('/settings/avatar/delete', [ClientSettingsController::class, 'deleteAvatar'])->name('settings.avatar.delete');
        Route::post('/settings/password', [ClientSettingsController::class, 'changePassword'])->name('settings.password');
    });
});
