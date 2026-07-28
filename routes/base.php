<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Base;
use Pterodactyl\Http\Controllers\Billing;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

/*
|--------------------------------------------------------------------------
| Billing Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/billing/packages', [Billing\BillingController::class, 'packages'])->name('billing.packages');
    Route::get('/billing/packages/{slug}/checkout', [Billing\BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/packages/{slug}/pay', [Billing\BillingController::class, 'pay'])->name('billing.pay');
    Route::get('/billing/invoices/{invoice}', [Billing\InvoiceController::class, 'show'])->name('billing.invoice.show');
    Route::get('/billing/invoices/{invoice}/status', [Billing\InvoiceController::class, 'status'])->name('billing.invoice.status');
});

// Pakasir webhook — no auth, no csrf. Registered under web group; CSRF skipped via withoutMiddleware.
Route::post('/api/billing/pakasir/callback', [Billing\PakasirWebhookController::class, 'callback'])
    ->name('billing.pakasir.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|daemon|billing)).+');
