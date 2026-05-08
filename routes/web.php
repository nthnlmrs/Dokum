<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Auth\ProviderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
Route::get('/', function () { return Inertia::render('Welcome', ['canLogin' => Route::has('login'), 'canRegister' => Route::has('register')]); });
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/docs/{documentation}/edit', [DashboardController::class, 'edit'])->name('docs.edit');
    Route::put('/docs/{documentation}', [DashboardController::class, 'update'])->name('docs.update');
    Route::get('/docs/{documentation}/export/pdf', [ExportController::class, 'exportPdf'])->name('docs.export.pdf');
    Route::post('/docs/{documentation}/export/google', [ExportController::class, 'exportGoogleDocs'])->name('docs.export.google');
});
Route::get('/auth/github/redirect', [ProviderController::class, 'redirect'])->name('github.login');
Route::get('/auth/github/callback', [ProviderController::class, 'callback']);
require __DIR__.'/auth.php';
