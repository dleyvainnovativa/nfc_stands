<?php

use App\Http\Controllers\Admin\TagBatchController;
use App\Http\Controllers\Admin\TagQrController;
use App\Http\Controllers\Admin\TenantsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GooglePlaceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Public\PageController as PublicPageController;
use App\Http\Controllers\Public\StandRedirectController;
use App\Http\Controllers\Public\SurveyResponseController;
use App\Http\Controllers\StandClaimController;
use App\Http\Controllers\StandController;
use App\Http\Controllers\StandQrController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TenantSwitchController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public — no auth, no tenant scope
|--------------------------------------------------------------------------
*/
Route::get('/t/{code}', [StandRedirectController::class, 'show'])->name('stand.public');

Route::get('/p/{code}', [PublicPageController::class, 'show'])->name('page.public');
Route::get('/p/{code}/go/{i}', [PublicPageController::class, 'go'])->whereNumber('i')->name('page.link');

Route::get('/s/{code}', [SurveyResponseController::class, 'show'])->name('survey.public');
Route::post('/s/{code}', [SurveyResponseController::class, 'store'])->name('survey.public.submit');

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:' . User::ROLE_SUPER_ADMIN . ',' . User::ROLE_CLIENT_ADMIN])->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* ---- Stands ----
       Admin creates stands / blank tags / claims; clients only list, view and
       repoint existing stands. Admin-only routes are registered first so the
       literal paths win over /stands/{stand}. */
    Route::middleware('role:' . User::ROLE_SUPER_ADMIN)->group(function () {
        Route::get('/stands/claim', [StandClaimController::class, 'show'])->name('stands.claim');
        Route::post('/stands/claim', [StandClaimController::class, 'store'])->name('stands.claim.store');
        Route::get('/stands/create', [StandController::class, 'create'])->name('stands.create');
        Route::post('/stands', [StandController::class, 'store'])->name('stands.store');
        Route::delete('/stands/{stand}', [StandController::class, 'destroy'])->name('stands.destroy');
    });
    Route::get('/stands', [StandController::class, 'index'])->name('stands.index');
    Route::get('/stands/{stand}/edit', [StandController::class, 'edit'])->name('stands.edit');
    Route::put('/stands/{stand}', [StandController::class, 'update'])->name('stands.update');
    Route::get('/stands/{stand}', [StandController::class, 'show'])->name('stands.show');
    Route::get('/stands/{stand}/qr', [StandQrController::class, 'svg'])->name('stands.qr');
    Route::get('/stands/{stand}/qr/print', [StandQrController::class, 'print'])->name('stands.qr.print');

    /* ---- Surveys ---- */
    Route::resource('surveys', SurveyController::class);

    /* ---- Pages (linktree) ----
       Admin creates, publishes (toggle) and deletes; clients edit content. */
    Route::middleware('role:' . User::ROLE_SUPER_ADMIN)->group(function () {
        Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
        Route::post('/pages/{page}/toggle', [PageController::class, 'toggle'])->name('pages.toggle');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    });
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::get('/pages/{page}/qr', [PageController::class, 'qr'])->name('pages.qr');

    /* ---- Brand (per-tenant) ---- */
    Route::get('/marca', [BrandController::class, 'edit'])->name('brand.edit');
    Route::put('/marca', [BrandController::class, 'update'])->name('brand.update');

    /* ---- Google reviews ---- */
    Route::get('/google', [GooglePlaceController::class, 'index'])->name('google.index');
    Route::post('/google', [GooglePlaceController::class, 'store'])->name('google.store');
    Route::post('/google/sync', [GooglePlaceController::class, 'sync'])->name('google.sync');
    Route::post('/google/{place}/sync', [GooglePlaceController::class, 'sync'])->name('google.sync.one');
    Route::delete('/google/{place}', [GooglePlaceController::class, 'destroy'])->name('google.destroy');

    /* ---- Super-admin only ---- */
    Route::middleware('role:' . User::ROLE_SUPER_ADMIN)->group(function () {
        Route::post('/switch/{tenant}', [TenantSwitchController::class, 'enter'])->name('tenant.enter');
        Route::post('/switch-exit', [TenantSwitchController::class, 'exit'])->name('tenant.exit');

        Route::get('/admin/tenants', [TenantsController::class, 'index'])->name('admin.tenants.index');
        Route::post('/admin/tenants', [TenantsController::class, 'store'])->name('admin.tenants.store');

        Route::get('/admin/tags', [TagBatchController::class, 'index'])->name('admin.tags.index');
        Route::post('/admin/tags', [TagBatchController::class, 'store'])->name('admin.tags.store');
        Route::get('/admin/tags/export', [TagBatchController::class, 'export'])->name('admin.tags.export');
        Route::get('/admin/tags/qr-sheet', [TagQrController::class, 'sheet'])->name('admin.tags.qr_sheet');
    });
});
