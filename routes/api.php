<?php

use Illuminate\Support\Facades\Route;

Route::post('/start', [App\Http\Controllers\Api\ExamController::class, 'start']);
Route::get('/session/{token}/question', [App\Http\Controllers\Api\ExamController::class, 'getQuestion']);
Route::post('/session/{token}/answer', [App\Http\Controllers\Api\ExamController::class, 'submitAnswer']);
Route::post('/session/{token}/finish', [App\Http\Controllers\Api\ExamController::class, 'finish']);
Route::get('/exams/{exam}/leaderboard', [App\Http\Controllers\Api\ExamController::class, 'leaderboard']);
