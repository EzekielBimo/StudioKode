<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Multi-page portfolio routes for StudioKode Landscape & Garden Architecture.
|
*/

// Home Showcase
Route::get('/', [HomeController::class, 'index'])->name('home');

// Project Portfolio
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');

// Studio & Informational Pages
Route::get('/studio', [PageController::class, 'studio'])->name('studio');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Consultations & Inquiries
Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
Route::post('/contact', [InquiryController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Administrative Suite Routes (StudioKode Admin)
|--------------------------------------------------------------------------
*/

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');
});

// Protected Administrative Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Projects Management (Features 16-19)
    Route::resource('projects', AdminProjectController::class)->parameters([
        'projects' => 'project:id',
    ]);
    Route::get('/projects/{project:id}/gallery', [AdminProjectController::class, 'edit'])->name('projects.gallery');

    // Gallery Management (Features 20-22)
    Route::post('/projects/{project:id}/images', [ProjectImageController::class, 'store'])->name('projects.images.store');
    Route::post('/projects/{project:id}/images/reorder', [ProjectImageController::class, 'reorder'])->name('projects.images.reorder');

    // Image Caption Update & Deletion (Supports both /admin/project-images/{image} and /admin/images/{image})
    Route::put('/project-images/{image}', [ProjectImageController::class, 'update'])->name('images.update');
    Route::put('/images/{image}', [ProjectImageController::class, 'update']);

    Route::delete('/project-images/{image}', [ProjectImageController::class, 'destroy'])->name('images.destroy');
    Route::delete('/images/{image}', [ProjectImageController::class, 'destroy']);

    // Inquiries Management (M2)
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
    Route::match(['put', 'patch'], '/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');
    Route::delete('/inquiries/{inquiry}', [AdminInquiryController::class, 'destroy'])->name('inquiries.destroy');

    // Profile & Password Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::match(['put', 'patch'], '/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
