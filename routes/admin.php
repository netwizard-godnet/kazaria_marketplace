<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\HeaderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PopupController;
use App\Http\Controllers\Admin\RoleController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/

Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Users Management
    Route::prefix('users')->name('users.')->middleware('permission:view_users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/sellers', [UserController::class, 'sellers'])->name('sellers');
        Route::get('/customers', [UserController::class, 'customers'])->name('customers');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        
        // Routes qui nécessitent d'autres permissions
        Route::middleware('permission:create_users')->group(function () {
            Route::post('/', [UserController::class, 'store'])->name('store');
        });
        
        Route::middleware('permission:edit_users')->group(function () {
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        Route::middleware('permission:delete_users')->group(function () {
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Products Management
    Route::prefix('products')->name('products.')->middleware('permission:view_products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        
        // Routes d'édition/création doivent précéder la route paramétrée /{product}
        Route::middleware('permission:edit_products')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::post('/{product}/approve', [ProductController::class, 'approve'])->name('approve');
            Route::post('/{product}/reject', [ProductController::class, 'reject'])->name('reject');
            Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Route paramétrée après pour ne pas matcher "create"
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        
        Route::middleware('permission:delete_products')->group(function () {
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::delete('/{product}/images/{index}', [ProductController::class, 'deleteImage'])->name('delete-image');
        });
    });
    
    // Orders Management
    Route::prefix('orders')->name('orders.')->middleware('permission:view_orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/stats', [OrderController::class, 'getStats'])->name('stats');
        Route::get('/{order}/available-statuses', [OrderController::class, 'getAvailableStatuses'])->name('available-statuses');
        
        Route::middleware('permission:manage_orders')->group(function () {
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::post('/{order}/status', [OrderController::class, 'changeStatus'])->name('change-status');
            Route::post('/{order}/payment-status', [OrderController::class, 'changePaymentStatus'])->name('change-payment-status');
        });
        
        Route::middleware('permission:cancel_orders')->group(function () {
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Stores Management
    Route::prefix('stores')->name('stores.')->middleware('permission:view_stores')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        
        Route::middleware('permission:approve_stores')->group(function () {
            Route::put('/{store}', [StoreController::class, 'update'])->name('update');
            Route::post('/{store}/toggle-official', [StoreController::class, 'toggleOfficial'])->name('toggle-official');
        });
        
        Route::middleware('permission:delete_stores')->group(function () {
            Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Messages
    Route::prefix('messages')->name('messages.')->middleware('permission:manage_messages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('index');
        Route::get('/{conversation}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('show');
        Route::post('/{conversation}', [\App\Http\Controllers\Admin\MessageController::class, 'store'])->name('store');
        Route::post('/create-conversation', [\App\Http\Controllers\Admin\MessageController::class, 'createConversation'])->name('create-conversation');
        Route::post('/{conversation}/toggle-important', [\App\Http\Controllers\Admin\MessageController::class, 'toggleImportant'])->name('toggle-important');
        Route::post('/{conversation}/archive', [\App\Http\Controllers\Admin\MessageController::class, 'archive'])->name('archive');
        Route::post('/{conversation}/unarchive', [\App\Http\Controllers\Admin\MessageController::class, 'unarchive'])->name('unarchive');
        Route::delete('/{conversation}', [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])->name('destroy');
        Route::get('/{conversation}/messages', [\App\Http\Controllers\Admin\MessageController::class, 'getMessages'])->name('get-messages');
        Route::post('/messages/{message}/mark-read', [\App\Http\Controllers\Admin\MessageController::class, 'markAsRead'])->name('mark-read');
        Route::get('/unread/conversations', [\App\Http\Controllers\Admin\MessageController::class, 'getUnreadConversations'])->name('unread-conversations');
    });
    
    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('show');
        Route::post('/{payment}/refund', [\App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('refund');
        Route::post('/{payment}/cancel', [\App\Http\Controllers\Admin\PaymentController::class, 'cancel'])->name('cancel');
        Route::post('/{payment}/mark-completed', [\App\Http\Controllers\Admin\PaymentController::class, 'markAsCompleted'])->name('mark-completed');
        Route::get('/stats/data', [\App\Http\Controllers\Admin\PaymentController::class, 'getStats'])->name('stats');
        Route::get('/export/csv', [\App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('export');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });


            // Banners
            Route::prefix('banners')->name('banners.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\BannerController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\BannerController::class, 'store'])->name('store');
                Route::post('/homepage-banner-1', [\App\Http\Controllers\Admin\BannerController::class, 'updateHomepageBanner1'])->name('update-homepage-banner-1');
                Route::post('/homepage-banner-2', [\App\Http\Controllers\Admin\BannerController::class, 'updateHomepageBanner2'])->name('update-homepage-banner-2');
                Route::post('/publicite-1', [\App\Http\Controllers\Admin\BannerController::class, 'updatePublicite1'])->name('update-publicite-1');
                Route::post('/publicite-2', [\App\Http\Controllers\Admin\BannerController::class, 'updatePublicite2'])->name('update-publicite-2');
                Route::post('/publicite-3', [\App\Http\Controllers\Admin\BannerController::class, 'updatePublicite3'])->name('update-publicite-3');
                Route::post('/publicite-4', [\App\Http\Controllers\Admin\BannerController::class, 'updatePublicite4'])->name('update-publicite-4');
                Route::post('/publicite-5', [\App\Http\Controllers\Admin\BannerController::class, 'updatePublicite5'])->name('update-publicite-5');
                Route::post('/boutique-carousel-1', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiqueCarousel1'])->name('update-boutique-carousel-1');
                Route::post('/boutique-carousel-2', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiqueCarousel2'])->name('update-boutique-carousel-2');
                Route::post('/boutique-carousel-3', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiqueCarousel3'])->name('update-boutique-carousel-3');
                Route::post('/boutique-carousel/add', [\App\Http\Controllers\Admin\BannerController::class, 'addBoutiqueCarouselImage'])->name('add-boutique-carousel-image');
                Route::post('/boutique-carousel/{banner}/update', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiqueCarouselImage'])->name('update-boutique-carousel-image');
                Route::delete('/boutique-carousel/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'removeBoutiqueCarouselImage'])->name('remove-boutique-carousel-image');
                Route::post('/boutique-pub-1', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiquePub1'])->name('update-boutique-pub-1');
                Route::post('/boutique-pub-2', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiquePub2'])->name('update-boutique-pub-2');
                Route::post('/boutique-pub-3', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiquePub3'])->name('update-boutique-pub-3');
                Route::post('/boutique-pub-4', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiquePub4'])->name('update-boutique-pub-4');
                Route::post('/boutique-pub-5', [\App\Http\Controllers\Admin\BannerController::class, 'updateBoutiquePub5'])->name('update-boutique-pub-5');
                Route::post('/categorie-pub-1', [\App\Http\Controllers\Admin\BannerController::class, 'updateCategoriePub1'])->name('update-categorie-pub-1');
                Route::post('/categorie-pub-2', [\App\Http\Controllers\Admin\BannerController::class, 'updateCategoriePub2'])->name('update-categorie-pub-2');
                Route::post('/categorie-pub-3', [\App\Http\Controllers\Admin\BannerController::class, 'updateCategoriePub3'])->name('update-categorie-pub-3');
                Route::post('/categorie-pub-4', [\App\Http\Controllers\Admin\BannerController::class, 'updateCategoriePub4'])->name('update-categorie-pub-4');
                Route::post('/categorie-pub-5', [\App\Http\Controllers\Admin\BannerController::class, 'updateCategoriePub5'])->name('update-categorie-pub-5');
                Route::get('/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'show'])->name('show');
                Route::put('/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'update'])->name('update');
                Route::delete('/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy'])->name('destroy');
                Route::post('/{banner}/toggle-status', [\App\Http\Controllers\Admin\BannerController::class, 'toggleStatus'])->name('toggle-status');
            });

            // Carousel
            Route::prefix('carousel')->name('carousel.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\CarouselController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\CarouselController::class, 'store'])->name('store');
                Route::get('/{slide}', [\App\Http\Controllers\Admin\CarouselController::class, 'show'])->name('show');
                Route::put('/{slide}', [\App\Http\Controllers\Admin\CarouselController::class, 'update'])->name('update');
                Route::delete('/{slide}', [\App\Http\Controllers\Admin\CarouselController::class, 'destroy'])->name('destroy');
            });
    
    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:manage_settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
        Route::post('/reset', [SettingController::class, 'reset'])->name('reset');
    });

    // Popups
    Route::prefix('popups')->name('popups.')->middleware('permission:manage_settings')->group(function () {
        Route::get('/', [PopupController::class, 'index'])->name('index');
        Route::get('/create', [PopupController::class, 'create'])->name('create');
        Route::post('/', [PopupController::class, 'store'])->name('store');
        Route::get('/{popup}/edit', [PopupController::class, 'edit'])->name('edit');
        Route::put('/{popup}', [PopupController::class, 'update'])->name('update');
        Route::delete('/{popup}', [PopupController::class, 'destroy'])->name('destroy');
        Route::post('/{popup}/toggle', [PopupController::class, 'toggle'])->name('toggle');
    });

    // Coupons (Codes promo)
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\CouponController::class, 'store'])->name('store');
        Route::post('/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponController::class, 'toggle'])->name('toggle');
        Route::delete('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('destroy');
    });
    
    // Roles & Permissions Management
    Route::prefix('roles')->name('roles.')->middleware('permission:manage_roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
    
    // Categories
    Route::prefix('categories')->name('categories.')->middleware('permission:manage_categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Subcategories
    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SubcategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SubcategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\SubcategoryController::class, 'store'])->name('store');
        Route::get('/{subcategory}', [\App\Http\Controllers\Admin\SubcategoryController::class, 'show'])->name('show');
        Route::get('/{subcategory}/edit', [\App\Http\Controllers\Admin\SubcategoryController::class, 'edit'])->name('edit');
        Route::put('/{subcategory}', [\App\Http\Controllers\Admin\SubcategoryController::class, 'update'])->name('update');
        Route::delete('/{subcategory}', [\App\Http\Controllers\Admin\SubcategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{subcategory}/toggle-status', [\App\Http\Controllers\Admin\SubcategoryController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Attributes
    Route::prefix('attributes')->name('attributes.')->group(function () {
        Route::get('/', [AttributeController::class, 'index'])->name('index');
        Route::get('/create', [AttributeController::class, 'create'])->name('create');
        Route::post('/', [AttributeController::class, 'store'])->name('store');
        Route::get('/{attribute}', [AttributeController::class, 'show'])->name('show');
        Route::get('/{attribute}/edit', [AttributeController::class, 'edit'])->name('edit');
        Route::put('/{attribute}', [AttributeController::class, 'update'])->name('update');
        Route::delete('/{attribute}', [AttributeController::class, 'destroy'])->name('destroy');
    });
    
    // Profile - routes supprimées car remplacées par ProfileController
    
    // Help & Documentation
    Route::get('/help', function () {
        return view('admin.help');
    })->name('help');
    
    Route::get('/documentation', function () {
        return view('admin.documentation');
    })->name('documentation');
    
    Route::get('/changelog', function () {
        return view('admin.changelog');
    })->name('changelog');
    
    // Routes pour le header (recherche, notifications, messages)
    Route::prefix('header')->name('header.')->group(function () {
        Route::get('/search', [HeaderController::class, 'search'])->name('search');
        Route::get('/notifications', [HeaderController::class, 'getNotifications'])->name('notifications');
        Route::get('/messages', [HeaderController::class, 'getMessages'])->name('messages');
        Route::post('/notifications/mark-read', [HeaderController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
        Route::post('/messages/mark-read', [HeaderController::class, 'markMessageAsRead'])->name('messages.mark-read');
    });
    
    // Routes pour le profil admin
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/profile-pic', [ProfileController::class, 'deleteProfilePic'])->name('profile-pic.delete');
    });
    
});

