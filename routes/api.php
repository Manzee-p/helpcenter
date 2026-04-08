<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VendorReportController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\StatusBoardController;
use App\Http\Controllers\VendorRatingController;
use App\Http\Controllers\ClientSettingsController;

// ==================== PUBLIC ROUTES (NO AUTH) ====================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// ✅ Public Status Board (accessible without authentication)
Route::get('/status-board', [StatusBoardController::class, 'publicIndex']);
Route::get('/status-board/{id}', [StatusBoardController::class, 'show']);

// ==================== PROTECTED ROUTES ====================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Ticket Categories
    Route::get('/ticket-categories', [TicketCategoryController::class, 'index']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // ==================== ADMIN ROUTES ====================
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // User Management
        Route::get('/users', [AdminController::class, 'listUsers']); 
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::patch('/users/{id}', [AdminController::class, 'updateUserStatus']); 
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Vendor Management
        Route::get('/vendors', [AdminController::class, 'getVendors']);
        Route::get('/vendors/{id}', [AdminController::class, 'getVendor']);
        Route::put('/vendors/{id}', [AdminController::class, 'updateVendorInfo']);

        // Ticket Management
        Route::post('/tickets/create-for-user', [AdminController::class, 'createTicketForUser']);
        Route::post('/tickets/{ticket}/assign', [AdminController::class, 'assignTicket']);
        Route::put('/tickets/{ticket}/priority', [AdminController::class, 'updateTicketPriority']);
        Route::get('/tickets/no-priority/first', [AdminController::class, 'getFirstNoPriorityTicket']);
        
        // Analytics & Reports
        Route::get('/analytics', [AdminController::class, 'getAnalytics']);
        Route::get('/reports', [AdminController::class, 'getSystemReports']);
        Route::get('/vendor-ratings', [VendorRatingController::class, 'adminIndex']);
        Route::post('/vendor-ratings/{vendorId}/warning', [VendorRatingController::class, 'sendAdminWarning']);
        Route::delete('/vendor-ratings/{id}', [VendorRatingController::class, 'destroy']);
        
        // Vendor Reports
        Route::get('/vendor-reports', [VendorReportController::class, 'index']);
        Route::post('/vendor-reports/generate', [VendorReportController::class, 'generate']);
        Route::get('/vendor-reports/{id}', [VendorReportController::class, 'show']);
        Route::delete('/vendor-reports/{id}', [VendorReportController::class, 'destroy']);
        
        // Category Management
        Route::post('/categories', [TicketCategoryController::class, 'store']);
        Route::put('/categories/{id}', [TicketCategoryController::class, 'update']);
        Route::delete('/categories/{id}', [TicketCategoryController::class, 'destroy']);

        // ==================== STATUS BOARD (ADMIN) ====================
        // ✅ CRITICAL FIX: Statistics MUST be BEFORE {id} route
        Route::prefix('status-board')->group(function () {
            Route::get('/statistics', [StatusBoardController::class, 'statistics']); // ← HARUS PERTAMA
            Route::get('/', [StatusBoardController::class, 'index']);
            Route::post('/', [StatusBoardController::class, 'store']);
            Route::get('/{id}', [StatusBoardController::class, 'show']); // ← Setelah /statistics
            Route::put('/{id}', [StatusBoardController::class, 'update']);
            Route::delete('/{id}', [StatusBoardController::class, 'destroy']);
            Route::post('/{id}/updates', [StatusBoardController::class, 'addUpdate']);
        });
    });

    // ==================== ADMIN SETTINGS ====================
    Route::middleware('role:admin')->prefix('admin/settings')->group(function () {
        Route::post('/profile', [AdminSettingsController::class, 'updateProfile']);
        Route::delete('/avatar', [AdminSettingsController::class, 'deleteAvatar']);
        Route::post('/password', [AdminSettingsController::class, 'changePassword']);
        Route::get('/login-history', [AdminSettingsController::class, 'getLoginHistory']);
        Route::get('/sessions', [AdminSettingsController::class, 'getSessions']);
        Route::get('/activity-logs', [AdminSettingsController::class, 'getActivityLogs']);
        Route::post('/notifications', [AdminSettingsController::class, 'saveNotifications']);
        Route::post('/preferences', [AdminSettingsController::class, 'savePreferences']);
        Route::get('/export-data', [AdminSettingsController::class, 'exportData']);
    });

    // ==================== VENDOR ROUTES ====================
    Route::middleware('role:vendor')->prefix('vendor')->group(function () {
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        Route::get('/tickets', [VendorController::class, 'myTickets']);
        Route::get('/tickets/{id}', [VendorController::class, 'show']);
        
        Route::patch('/tickets/{id}/status', [VendorController::class, 'updateTicketStatus']);
        Route::get('/performance', [VendorController::class, 'performance']);
        Route::get('/history', [VendorController::class, 'history']);
        Route::get('/ratings', [VendorRatingController::class, 'vendorIndex']);
        Route::get('/ticket-stats', [VendorController::class, 'ticketStats']);
        Route::put('/profile', [VendorController::class, 'updateProfile']);
        Route::post('/change-password', [VendorController::class, 'changePassword']);
        Route::get('/reports', [VendorReportController::class, 'index']);
        Route::get('/reports/current', [VendorReportController::class, 'current']);
        Route::get('/reports/{id}', [VendorReportController::class, 'show']);
    }); 

    // ==================== CLIENT ROUTES ====================
    Route::middleware('role:client')->prefix('client')->group(function () {
        Route::get('/tickets', [ClientController::class, 'myTickets']);
        Route::get('/tickets/history', [ClientController::class, 'ticketHistory']);    // ← HARUS PERTAMA
        Route::get('/tickets/{ticket}', [ClientController::class, 'show']);            // ← Baru {ticket}
        Route::post('/tickets/{ticket}/additional-info', [ClientController::class, 'submitAdditionalInfo']);
        Route::post('/tickets/{ticket}/feedback', [ClientController::class, 'submitFeedback']);
        Route::prefix('settings')->group(function () {
            Route::post('/profile', [ClientSettingsController::class, 'updateProfile']);
            Route::delete('/avatar', [ClientSettingsController::class, 'deleteAvatar']);
            Route::post('/password', [ClientSettingsController::class, 'changePassword']);
            Route::get('/communications', [ClientSettingsController::class, 'getCommunications']);
            Route::get('/last-login', [ClientSettingsController::class, 'getLastLogin']);
            Route::post('/support', [ClientSettingsController::class, 'sendSupportMessage']);
            Route::post('/notifications', [ClientSettingsController::class, 'saveNotifications']);
            Route::post('/preferences', [ClientSettingsController::class, 'savePreferences']);
            Route::get('/export-data', [ClientSettingsController::class, 'exportData']);
        });
    });

    // Tickets (All authenticated users)
    Route::apiResource('tickets', TicketController::class);
});
