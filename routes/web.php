<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\LibraryController;
use App\Http\Controllers\Student\PortfolioController;
use App\Http\Controllers\Student\QuizController;
use App\Http\Controllers\Student\SubjectController;
use App\Http\Controllers\Student\VideoController;
use App\Http\Controllers\Student\WorksheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'index'])->middleware('auth')->name('dashboard');

Route::get('/storage/{path}', function (string $path) {
    abort_unless(\Illuminate\Support\Facades\Storage::disk('public')->exists($path), 404);
    return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.local');

Route::get('/material-temp-preview/{filename}', function (string $filename) {
    $safeName = basename($filename);
    $candidates = [
        storage_path('app/livewire-tmp/'.$safeName),
        storage_path('app/private/livewire-tmp/'.$safeName),
        storage_path('app/public/livewire-tmp/'.$safeName),
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            ]);
        }
    }

    abort(404);
})->middleware('auth')->name('material.temp-preview');

Route::middleware(['auth', 'role:student'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mapel', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/perpustakaan', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/katalog', [LibraryController::class, 'index'])->name('catalog.index');
    Route::get('/rak', [LibraryController::class, 'shelf'])->name('shelf.index');
    Route::get('/riwayat-bacaan', [LibraryController::class, 'history'])->name('reading-history.index');
    Route::post('/katalog/{material}/rak', [LibraryController::class, 'toggleSaved'])->name('shelf.toggle');
    Route::get('/perpustakaan/{material}', [LibraryController::class, 'show'])->name('library.show');
    Route::get('/video', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/lembar-kerja', [WorksheetController::class, 'index'])->name('worksheets.index');
    Route::get('/lembar-kerja/{assignment}', [WorksheetController::class, 'show'])->name('worksheets.show');
    Route::post('/lembar-kerja/{assignment}/submit', [WorksheetController::class, 'submit'])->name('worksheets.submit');
    Route::get('/kuis', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/kuis/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/kuis/{quiz}/finish', [QuizController::class, 'finish'])->name('quizzes.finish');
    Route::get('/portofolio', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('/nilai', [GradeController::class, 'index'])->name('grades.index');
});

// Teacher (Guru) Routes
Route::middleware(['auth', 'role:teacher'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perpustakaan', [\App\Http\Controllers\Teacher\LibraryController::class, 'index'])->name('library.index');
    Route::post('/perpustakaan', [\App\Http\Controllers\Teacher\LibraryController::class, 'store'])->name('library.store');
    Route::get('/materi', [\App\Http\Controllers\Teacher\MaterialController::class, 'index'])->name('materials.index');
    Route::post('/materi', [\App\Http\Controllers\Teacher\MaterialController::class, 'store'])->name('materials.store');
    Route::get('/video', [\App\Http\Controllers\Teacher\VideoController::class, 'index'])->name('videos.index');
    Route::post('/video', [\App\Http\Controllers\Teacher\VideoController::class, 'store'])->name('videos.store');
    Route::delete('/materi/{material}', [\App\Http\Controllers\Teacher\MaterialController::class, 'destroy'])->name('materials.destroy');
    Route::get('/tugas', [\App\Http\Controllers\Teacher\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tugas', [\App\Http\Controllers\Teacher\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/pengumpulan', [\App\Http\Controllers\Teacher\SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('/pengumpulan/{submission}/grade', [\App\Http\Controllers\Teacher\SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/nilai', [\App\Http\Controllers\Teacher\GradeController::class, 'index'])->name('grades.index');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengguna', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/pengguna', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/pengguna/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/pengguna/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/pengguna/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/kelas', [\App\Http\Controllers\Admin\ClassroomController::class, 'index'])->name('classes.index');
    Route::post('/kelas', [\App\Http\Controllers\Admin\ClassroomController::class, 'store'])->name('classes.store');
    Route::put('/kelas/{classroom}', [\App\Http\Controllers\Admin\ClassroomController::class, 'update'])->name('classes.update');
    Route::delete('/kelas/{classroom}', [\App\Http\Controllers\Admin\ClassroomController::class, 'destroy'])->name('classes.destroy');
    Route::post('/kelas/{classroom}/toggle-active', [\App\Http\Controllers\Admin\ClassroomController::class, 'toggleActive'])->name('classes.toggle-active');
    Route::get('/mapel', [\App\Http\Controllers\Admin\SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/mapel', [\App\Http\Controllers\Admin\SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/mapel/{subject}', [\App\Http\Controllers\Admin\SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/mapel/{subject}', [\App\Http\Controllers\Admin\SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/bantuan-akun', [\App\Http\Controllers\Admin\AccountRequestController::class, 'index'])->name('account-requests.index');
    Route::post('/bantuan-akun/{accountRequest}/resolve', [\App\Http\Controllers\Admin\AccountRequestController::class, 'resolve'])->name('account-requests.resolve');
    Route::get('/aktivitas', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/pengaturan', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/pengaturan', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
