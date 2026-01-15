<?php

use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', [ExamController::class, 'home'])->name('home');
Route::get('/exam/form', [ExamController::class, 'form'])->name('exam.form');
Route::get('/exam/{token}/rules', [ExamController::class, 'rules'])->name('exam.rules');
Route::get('/exam/{token}', [ExamController::class, 'exam'])->name('exam.screen');
Route::get('/exam/{token}/complete', [ExamController::class, 'complete'])->name('exam.complete');
Route::get('/leaderboard', [ExamController::class, 'leaderboard'])->name('leaderboard');

// Time travel test route (only in non-production)
if (!app()->isProduction()) {
    Route::get('/test-time', function () {
        return response()->json([
            'test_time' => \Carbon\Carbon::getTestNow()?->format('Y-m-d H:i:s T'),
            'immutable_test_time' => \Carbon\CarbonImmutable::getTestNow()?->format('Y-m-d H:i:s T'),
            'now_helper' => now()->format('Y-m-d H:i:s T'),
            'carbon_now' => \Carbon\Carbon::now()->format('Y-m-d H:i:s T'),
            'date_facade' => \Illuminate\Support\Facades\Date::now()->format('Y-m-d H:i:s T'),
            'env_test_time' => env('APP_TEST_TIME'),
            'is_production' => app()->isProduction(),
        ]);
    })->name('test.time');
}

// Login route - redirects to Filament admin login
// Handle both GET and POST to avoid MethodNotAllowedHttpException
Route::match(['get', 'post'], '/login', function () {
    return redirect('/admin/login');
})->name('login');

// Force Livewire routes to be registered (fixes route:cache issue in production)
// This ensures Filament login works correctly when routes are cached
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/livewire/update', $handle);
});
