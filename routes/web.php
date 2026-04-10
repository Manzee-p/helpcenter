    <?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\Auth\GoogleController;
    use App\Http\Controllers\AdminDashboardController;
    use App\Http\Controllers\VendorDashboardController;
    use App\Http\Controllers\ClientDashboardController;
    use App\Http\Controllers\TicketCategoryController;
    use App\Http\Controllers\StatusPageController;
    use App\Http\Controllers\NotificationController;

    // ═══ PUBLIC ═══
    Route::get('/', fn () => view('welcome'));

    Route::get('/status',      [StatusPageController::class, 'index'])->name('status');
    Route::get('/status/{id}', [StatusPageController::class, 'show'])->name('status.detail');

    Route::post('/auth/google/callback', [GoogleController::class, 'handleGoogle'])->name('auth.google.callback');

    Auth::routes(['register' => false]);

    // ═══ AUTHENTICATED ═══
    Route::middleware(['auth'])->group(function () {

        Route::get('/home', [HomeController::class, 'index'])->name('home');

        // ─── NOTIFIKASI ───────────────────────────────────────────────
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                [NotificationController::class, 'index'])        ->name('index');
            Route::get('/unread-count',    [NotificationController::class, 'unreadCount'])  ->name('unread-count');
            Route::post('/mark-all-read',  [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/{id}/read',      [NotificationController::class, 'markAsRead'])   ->name('mark-read');
            Route::delete('/{id}',         [NotificationController::class, 'destroy'])      ->name('destroy');
        });

        // ─── ADMIN ───────────────────────────────────────────────────
        Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Tickets
            Route::get('/tickets',          [App\Http\Controllers\AdminTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [App\Http\Controllers\AdminTicketController::class, 'show'])->name('tickets.show');
            Route::patch('/tickets/{ticket}/assign',          [App\Http\Controllers\AdminTicketController::class, 'assign'])         ->name('tickets.assign');
            Route::patch('/tickets/{ticket}/update-status',   [App\Http\Controllers\AdminTicketController::class, 'updateStatus'])   ->name('tickets.update-status');
            Route::patch('/tickets/{ticket}/update-priority', [App\Http\Controllers\AdminTicketController::class, 'updatePriority']) ->name('tickets.update-priority');
            Route::delete('/tickets/{ticket}',                [App\Http\Controllers\AdminTicketController::class, 'destroy'])        ->name('tickets.destroy');
            Route::get('/ticket-deletion-requests',           [App\Http\Controllers\AdminTicketController::class, 'deletionRequests'])->name('ticket-deletion-requests.index');
            Route::get('/ticket-deletion-requests/{id}',      [App\Http\Controllers\AdminTicketController::class, 'showDeletionRequest'])->name('ticket-deletion-requests.show');
            Route::post('/ticket-deletion-requests/{id}/process', [App\Http\Controllers\AdminTicketController::class, 'processDeletionRequest'])->name('ticket-deletion-requests.process');

            // Users
            Route::get   ('/users',                      [App\Http\Controllers\AdminUserController::class, 'index'])        ->name('users.index');
            Route::post  ('/users',                      [App\Http\Controllers\AdminUserController::class, 'store'])        ->name('users.store');
            Route::put   ('/users/{user}',               [App\Http\Controllers\AdminUserController::class, 'update'])       ->name('users.update');
            Route::post  ('/users/{user}/toggle-status', [App\Http\Controllers\AdminUserController::class, 'toggleStatus']) ->name('users.toggle-status');
            Route::delete('/users/{user}',               [App\Http\Controllers\AdminUserController::class, 'destroy'])      ->name('users.destroy');

            // Vendors
            Route::get('/vendors',          [App\Http\Controllers\AdminController::class, 'getVendors'])     ->name('vendors.index');
            Route::get('/vendors/{vendor}', [App\Http\Controllers\AdminController::class, 'getVendorDetail'])->name('vendors.show');

            // Vendor ratings
            Route::get   ('/vendor-ratings',                     [App\Http\Controllers\VendorRatingController::class, 'adminIndex'])      ->name('vendor-ratings.index');
            Route::delete('/vendor-ratings/{id}',                [App\Http\Controllers\VendorRatingController::class, 'destroy'])         ->name('vendor-ratings.destroy');
            Route::post  ('/vendor-ratings/{vendorId}/warning',  [App\Http\Controllers\VendorRatingController::class, 'sendAdminWarning'])->name('vendor-ratings.warning');

            // Categories
            Route::get   ('/categories',      [TicketCategoryController::class, 'index'])  ->name('categories.index');
            Route::post  ('/categories',      [TicketCategoryController::class, 'store'])  ->name('categories.store');
            Route::put   ('/categories/{id}', [TicketCategoryController::class, 'update']) ->name('categories.update');
            Route::delete('/categories/{id}', [TicketCategoryController::class, 'destroy'])->name('categories.destroy');

            // Analytics
            Route::get('/analytics', [App\Http\Controllers\AdminController::class, 'getAnalytics'])->name('analytics');

            // Reports
            Route::get('/reports', [App\Http\Controllers\AdminController::class, 'getSystemReports'])->name('reports');

            // Status Board
            Route::get ('/status-board',        [App\Http\Controllers\StatusBoardController::class, 'index']) ->name('status-board.index');
            Route::get ('/status-board/create', [App\Http\Controllers\StatusBoardController::class, 'create'])->name('status-board.create');
            Route::get ('/status-board/{id}',   [App\Http\Controllers\StatusBoardController::class, 'detail'])->name('status-board.show');
            Route::post('/status-board',        [App\Http\Controllers\StatusBoardController::class, 'store']) ->name('status-board.store');
            Route::put('/status-board/{id}',    [App\Http\Controllers\StatusBoardController::class, 'update'])->name('status-board.update');
            Route::delete('/status-board/{id}', [App\Http\Controllers\StatusBoardController::class, 'destroy'])->name('status-board.destroy');
            Route::post('/status-board/{id}/updates', [App\Http\Controllers\StatusBoardController::class, 'addUpdate'])->name('status-board.add-update');

            // Settings
            Route::get('/settings', [App\Http\Controllers\AdminSettingsController::class, 'index'])->name('settings');
        });

        // ─── VENDOR ──────────────────────────────────────────────────
        Route::middleware(['role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
            Route::get('/dashboard', [VendorDashboardController::class, 'index'])                         ->name('dashboard');
            Route::get('/tickets',   [App\Http\Controllers\VendorController::class, 'myTickets'])         ->name('tickets.index');
            Route::get('/history',   [App\Http\Controllers\VendorController::class, 'history'])           ->name('history');
            Route::get('/reports',   [App\Http\Controllers\VendorReportController::class, 'index'])       ->name('reports');
            Route::get('/ratings',   [App\Http\Controllers\VendorRatingController::class, 'vendorIndex']) ->name('ratings');
            Route::get('/settings',  [App\Http\Controllers\AdminSettingsController::class, 'index'])      ->name('settings');
            Route::get('/tickets/{ticket}',          [App\Http\Controllers\VendorController::class, 'show'])               ->name('tickets.show');
            Route::patch('/tickets/{ticket}/status', [App\Http\Controllers\VendorController::class, 'updateTicketStatus']) ->name('tickets.update-status');
            Route::get('/ticket-stats',              [App\Http\Controllers\VendorController::class, 'ticketStats'])        ->name('ticket-stats');
        });

        // ─── CLIENT ──────────────────────────────────────────────────
        Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
            Route::get ('/dashboard',                   [ClientDashboardController::class, 'index'])        ->name('dashboard');

            // Tickets CRUD — urutan penting: /create harus sebelum /{ticket}
            Route::get ('/tickets',                     [App\Http\Controllers\ClientController::class, 'myTickets'])    ->name('tickets.index');
            Route::get ('/tickets/create',              [App\Http\Controllers\ClientController::class, 'create'])       ->name('tickets.create');
            Route::post('/tickets',                     [App\Http\Controllers\ClientController::class, 'store'])        ->name('tickets.store');
            Route::get ('/tickets/{ticket}',            [App\Http\Controllers\ClientController::class, 'show'])         ->name('tickets.show');

            // Feedback
            Route::post('/tickets/{ticket}/feedback',   [App\Http\Controllers\ClientController::class, 'storeFeedback'])->name('tickets.feedback');
            Route::post('/tickets/{ticket}/additional-info', [App\Http\Controllers\ClientController::class, 'submitAdditionalInfo'])->name('tickets.additional-info');
            Route::post('/tickets/{ticket}/deletion-request', [App\Http\Controllers\ClientController::class, 'submitDeletionRequest'])->name('tickets.deletion-request');

            // History & misc
            Route::get ('/history',                     [App\Http\Controllers\ClientController::class, 'ticketHistory'])->name('history');
            Route::get ('/pending-ratings',             [App\Http\Controllers\ClientController::class, 'pendingRatings'])->name('pending-ratings');
            Route::get ('/settings',                    [App\Http\Controllers\ClientSettingsController::class, 'index'])->name('settings');

            Route::post('/settings/avatar',        [App\Http\Controllers\ClientSettingsController::class, 'updateAvatar'])       ->name('settings.avatar');

            Route::post('/settings/profile',       [App\Http\Controllers\ClientSettingsController::class, 'updateProfile'])  ->name('settings.profile');
            Route::post('/settings/avatar/delete', [App\Http\Controllers\ClientSettingsController::class, 'deleteAvatar'])   ->name('settings.avatar.delete');
            Route::post('/settings/password',      [App\Http\Controllers\ClientSettingsController::class, 'changePassword']) ->name('settings.password');
            Route::post('/settings/notifications', [App\Http\Controllers\ClientSettingsController::class, 'saveNotifications'])->name('settings.notifications');
            Route::post('/settings/preferences',   [App\Http\Controllers\ClientSettingsController::class, 'savePreferences'])->name('settings.preferences');
        });
    });
