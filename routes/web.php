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

    // Settings Routes
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/github-token', [\App\Http\Controllers\SettingController::class, 'updateGithubToken'])->name('settings.github-token');

    Route::post('/settings/ai-config/fetch-models', [\App\Http\Controllers\SettingController::class, 'fetchModels'])->name('settings.ai.fetch-models');
    Route::post('/settings/ai-config', [\App\Http\Controllers\SettingController::class, 'storeAiConfiguration'])->name('settings.ai.store');
    Route::put('/settings/ai-config/{aiConfiguration}', [\App\Http\Controllers\SettingController::class, 'updateAiConfiguration'])->name('settings.ai.update');
    Route::delete('/settings/ai-config/{aiConfiguration}', [\App\Http\Controllers\SettingController::class, 'deleteAiConfiguration'])->name('settings.ai.destroy');
    Route::post('/settings/ai-config/{aiConfiguration}/activate', [\App\Http\Controllers\SettingController::class, 'setActiveAiConfiguration'])->name('settings.ai.activate');
});
Route::get('/auth/github/redirect', [ProviderController::class, 'redirect'])->name('github.login');
Route::get('/auth/github/callback', [ProviderController::class, 'callback']);
require __DIR__.'/auth.php';
