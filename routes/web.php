<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [JobsController::class, 'index'])->name('jobs');
Route::group(['prefix' => 'account'], function() {
    // Guest routes
    Route::group(['middleware' => 'guest'], function() {
        Route::get('register', [AccountController::class, 'registration'])->name('account.registration');
        Route::post('process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::get('login', [AccountController::class, 'login'])->name('account.login');
        Route::post('authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
    });

    // Authenticated routes
    Route::group(['middleware' => 'auth'], function() {
        Route::get('profile', [AccountController::class, 'profile'])->name('account.profile');
        Route::put('update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        Route::get('logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');
        Route::get('create-job', [AccountController::class, 'createJob'])->name('account.createJob');
        Route::post('save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
        Route::get('my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
        Route::get('my-jobs/edit/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');
        Route::post('update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
        Route::post('delete-job/{jobId}', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
    });
});


