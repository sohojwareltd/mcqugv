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
