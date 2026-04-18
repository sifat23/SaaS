<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ShopRegistrationController;
use App\Http\Controllers\Stripe\WebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/shop-registration', [ShopRegistrationController::class, 'index'])->name('shop.registration');
Route::post('/shop-registration', [ShopRegistrationController::class, 'store']);
Route::get('/shop-registration/payment/success', [ShopRegistrationController::class, 'success'])
    ->name('shop.registration.payment.success');
Route::get('shop-registration/payment/status',  [PaymentController::class, 'checkStatus'])->name('setup.payment.check');
Route::get('shop-registration/payment/canceled', [PaymentController::class, 'canceled'])->name('setup.payment.canceled');
Route::get('/', function () {
    return Inertia::render('Welcome');
});


Route::resource('/user-registration', RegistrationController::class)->names([
        'index' => 'registration.index',
        'create' => 'registration.create',
        'store' => 'registration.store',
        'show' => 'registration.show',
        'edit' => 'registration.edit',
        'update' => 'registration.update',
        'destroy' => 'registration.destroy',
    ]);;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
//     ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([VerifyCsrfToken::class]);


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/test-stripe-clock', function (\App\Helpers\StripeHelper $service) {
    $result = $service->createTestSubscriptionWithClock();
    return response()->json($result);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
