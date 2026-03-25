<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopRegistrationController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// dd('sss');

Route::get('/shop-registration', [ShopRegistrationController::class, 'index'])->name('shop.registration');
Route::post('/shop-registration', [ShopRegistrationController::class, 'store']);
Route::get('/shop-registration/payment/success', [ShopRegistrationController::class, 'success'])
    ->name('shop.registration.payment.success');
Route::get('shop-registration/payment/status',  [PaymentController::class, 'checkStatus'])->name('setup.payment.check');

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([VerifyCsrfToken::class]);


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
