<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::get('/', [PublicController::class, 'homepage'])->name('homepage');
//!ROTTE REGISTRAZIONE CORSISTA
Route::get('/register/timekeeper', [RegisterController::class, 'showTimekeeperRegistrationForm'])->name('timekeeper.register.form');
Route::post('/register/timekeeper', [RegisterController::class, 'registerTimekeeper'])->name('timekeeper.register');
//!ROTTE REGISTRAZIONE ADMIN
Route::get('/register/admin', [RegisterController::class, 'showAdminRegistrationForm'])->name('admin.register.form');
Route::post('/register/admin', [RegisterController::class, 'registerAdmin'])->name('admin.register');
//!ROTTE LOGIN UTENTI
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
//!ROTTE RESET PASSWORD
Route::get('/password-dimenticata', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/password-dimenticata', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
//!MIDDLEWERE UTENTE LOGGATO
Route::middleware('auth')->group(function () {
    //!ROTTE CAMBIO PASSWORD
    Route::get('/password/change', [PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'changePassword']);
});