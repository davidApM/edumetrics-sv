<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentScoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\GradebookReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('students', StudentController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])
        ->name('students.toggle-status');

    Route::resource('assessments', AssessmentController::class)
        ->only(['index', 'create', 'store', 'destroy']);

    Route::get('assessments/{assessment}/scores', [AssessmentScoreController::class, 'edit'])
        ->name('assessments.scores.edit');
    Route::put('assessments/{assessment}/scores', [AssessmentScoreController::class, 'update'])
        ->name('assessments.scores.update');

    Route::get('gradebook', [GradebookController::class, 'index'])
        ->name('gradebook.index');

    Route::get('gradebook/export/section', [GradebookReportController::class, 'sectionPdf'])
        ->name('gradebook.export.section');
    Route::get('gradebook/export/student/{student}', [GradebookReportController::class, 'studentPdf'])
        ->name('gradebook.export.student');
});

require __DIR__.'/auth.php';