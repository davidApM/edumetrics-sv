<?php

use App\Http\Controllers\AcademicManagementController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentScoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\GradebookReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('academic')->name('academic.')->group(function () {
        Route::get('/', [AcademicManagementController::class, 'index'])->name('index');
        Route::post('/subjects', [AcademicManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [AcademicManagementController::class, 'editSubject'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [AcademicManagementController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [AcademicManagementController::class, 'destroySubject'])->name('subjects.destroy');
        Route::post('/sections', [AcademicManagementController::class, 'storeSection'])->name('sections.store');
        Route::delete('/sections/{section}', [AcademicManagementController::class, 'destroySection'])->name('sections.destroy');
        Route::put('/periods', [AcademicManagementController::class, 'updatePeriods'])->name('periods.update');
    });

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