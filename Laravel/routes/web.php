<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AdminMappingController;
use App\Http\Controllers\AdminDataMaintenanceController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KMeansController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ResultController;

// ==============================
// AUTH (hanya untuk tamu/belum login)
// ==============================
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==============================
// DASHBOARD REDIRECT (setelah login)
// Dipakai oleh middleware 'guest' saat user yang sudah login
// menekan tombol Back dan mencoba kembali ke halaman login.
// ==============================
Route::middleware('auth')->get('/dashboard', function () {
    $role = auth()->user()->role;
    return match($role) {
        'admin' => redirect()->route('admin.users.index'),
        'guru'  => redirect()->route('guru.questions.index'),
        default => redirect()->route('siswa.exams.index'),
    };
})->name('dashboard');

// ==============================
// PROTECTED ROUTES
// ==============================
Route::middleware('auth')->group(function () {
    // ==============================
    // ADMIN ROUTES
    // ==============================
    Route::get('/admin/users',                    [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users',                   [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/users/{user}',          [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/import',            [AdminUserController::class, 'import'])->name('admin.users.import');
    Route::get('/admin/users/template',           [AdminUserController::class, 'downloadTemplate'])->name('admin.users.template');
    Route::get('/admin/cetak-kartu',              [AdminUserController::class, 'printCards'])->name('admin.print-cards');
    Route::post('/admin/users/bulk-delete',       [AdminUserController::class, 'bulkDelete'])->name('admin.users.bulk-delete');
    Route::get('/admin/data/backup',              [AdminDataMaintenanceController::class, 'backup'])->name('admin.data.backup');
    Route::post('/admin/data/restore',            [AdminDataMaintenanceController::class, 'restore'])->name('admin.data.restore');
    Route::post('/admin/data/clear',              [AdminDataMaintenanceController::class, 'clear'])->name('admin.data.clear');

    Route::get('/admin/subjects',              [AdminSubjectController::class, 'index'])->name('admin.subjects.index');
    Route::post('/admin/subjects',             [AdminSubjectController::class, 'store'])->name('admin.subjects.store');
    Route::delete('/admin/subjects/{subject}', [AdminSubjectController::class, 'destroy'])->name('admin.subjects.destroy');

    // Note: {adminClass} digunakan karena 'class' adalah reserved word PHP
    Route::get('/admin/classes',               [AdminClassController::class, 'index'])->name('admin.classes.index');
    Route::post('/admin/classes',              [AdminClassController::class, 'store'])->name('admin.classes.store');
    Route::delete('/admin/classes/{adminClass}', [AdminClassController::class, 'destroy'])->name('admin.classes.destroy');

    Route::get('/admin/mappings',              [AdminMappingController::class, 'index'])->name('admin.mappings.index');
    Route::post('/admin/mappings',             [AdminMappingController::class, 'store'])->name('admin.mappings.store');
    Route::delete('/admin/mappings/{mapping}', [AdminMappingController::class, 'destroy'])->name('admin.mappings.destroy');

    // ==============================
    // GURU ROUTES
    // ==============================
    Route::get('/guru/questions',               [QuestionController::class, 'index'])->name('guru.questions.index');
    Route::post('/guru/questions',              [QuestionController::class, 'store'])->name('guru.questions.store');
    Route::post('/guru/questions/bulk-delete',  [QuestionController::class, 'bulkDelete'])->name('guru.questions.bulk-delete');
    Route::delete('/guru/questions/{question}', [QuestionController::class, 'destroy'])->name('guru.questions.destroy');
    Route::post('/guru/questions/import',       [QuestionController::class, 'importQuestions'])->name('guru.questions.import');
    Route::get('/guru/questions/template',      [QuestionController::class, 'downloadQuestionTemplate'])->name('guru.questions.template');
    Route::post('/guru/questions/copy-to-class',[QuestionController::class, 'copyToClass'])->name('guru.questions.copy');

    Route::get('/guru/schedules',                          [ScheduleController::class, 'index'])->name('guru.schedules.index');
    Route::post('/guru/schedules',                         [ScheduleController::class, 'store'])->name('guru.schedules.store');
    Route::post('/guru/schedules/{schedule}/toggle',       [ScheduleController::class, 'toggleActive'])->name('guru.schedules.toggle');
    Route::post('/guru/schedules/{schedule}/duration',     [ScheduleController::class, 'updateDuration'])->name('guru.schedules.duration');
    Route::delete('/guru/schedules/{schedule}',            [ScheduleController::class, 'destroy'])->name('guru.schedules.destroy');

    // API endpoints (schedule timer)
    Route::get('/api/schedules/{schedule}/remaining',             [ScheduleController::class, 'remainingTime'])->name('guru.schedules.remaining');
    Route::post('/api/schedules/{schedule}/expire',               [ScheduleController::class, 'expire'])->name('guru.schedules.expire');

    // Izin siswa & tambah waktu
    Route::post('/guru/schedules/{schedule}/permission/{student}', [\App\Http\Controllers\ExamPermissionController::class, 'toggle'])->name('guru.schedules.permission');
    Route::post('/guru/schedules/{schedule}/add-time',             [\App\Http\Controllers\ExamPermissionController::class, 'addTime'])->name('guru.schedules.addtime');

    Route::get('/guru/reports',   [ReportController::class, 'index'])->name('guru.reports.index');
    Route::get('/guru/analysis',  [KMeansController::class, 'index'])->name('guru.analysis.index');
    Route::post('/guru/analysis/{id}', [KMeansController::class, 'analyze'])->name('guru.analysis.run');
    Route::get('/guru/exam-results/{schedule}', [ScheduleController::class, 'examResults'])->name('guru.exam-results');
    Route::get('/guru/exam-results/{schedule}/export', [ScheduleController::class, 'exportResults'])->name('guru.exam-results.export');

    // ==============================
    // SISWA ROUTES
    // ==============================
    Route::get('/siswa/exams',              [ExamController::class, 'index'])->name('siswa.exams.index');
    Route::get('/siswa/exams/{id}/take',    [ExamController::class, 'take'])->name('siswa.exams.take');
    Route::post('/siswa/exams/{id}/submit', [ExamController::class, 'submit'])->name('siswa.exams.submit');

    Route::get('/siswa/results',          [ResultController::class, 'index'])->name('siswa.results.index');
    Route::get('/siswa/results/{id}',     [ResultController::class, 'show'])->name('siswa.results.show');
});
