<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RaceTempController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TimekeeperController;
use App\Http\Controllers\SecretariatController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RecordAttachmentController;
use App\Http\Controllers\Auth\ForgotPasswordController;



// Route::get('/admin/create/race', [AdminController::class, 'createRaceShow'])->name('admin.createRace.form');
// Route::post('/admin/race/create', [AdminController::class, 'storeRace'])->name('race.store');
Route::get('/guest/create/race', [RaceTempController::class, 'createRaceTempShow'])->name('guest.createRaceTemp.form');
Route::post('/guest/race/create', [RaceTempController::class, 'storeRaceTemp'])->name('raceTemp.store');

Route::post('/admin/race-temp/{race}/accept', [RaceTempController::class, 'accept'])->name('race-temp.accept');
Route::delete('/admin/race-temp/{race}/reject', [RaceTempController::class, 'reject'])->name('race-temp.reject');



//!ROTTE CONFERMA RECORDS
Route::post('/records/{record}/confirm', [TimekeeperController::class, 'confirm'])->name('records.confirm');
Route::post('/races/{race}/records/confirm-all', [TimekeeperController::class, 'confirmAll'])->name('records.confirm.all');



//!ROTTE VISUALIZZAZIONE FILE
Route::get('/attachments/{attachment}', [RecordAttachmentController::class, 'show'])->name('attachments.show');


Route::get('/', [PublicController::class, 'homepage'])->name('homepage');
//!ROTTE REGISTRAZIONE CORSISTA
Route::get('/register/timekeeper', [RegisterController::class, 'showTimekeeperRegistrationForm'])->name('timekeeper.register.form');
Route::post('/register/timekeeper', [RegisterController::class, 'registerTimekeeper'])->name('timekeeper.register');
//!ROTTE REGISTRAZIONE ADMIN
Route::get('/register/admin', [RegisterController::class, 'showAdminRegistrationForm'])->name('admin.register.form');
Route::post('/register/admin', [RegisterController::class, 'registerAdmin'])->name('admin.register');
//!ROTTE REGISTRAZIONE SECRETARIAT
Route::get('/register/secretariat', [RegisterController::class, 'showSecretariatRegistrationForm'])->name('secretariat.register.form');
Route::post('/register/secretariat', [RegisterController::class, 'registerSecretariat'])->name('secretariat.register');
//!ROTTE SECRETARIAT
Route::get('/secretariat/dashboard', [SecretariatController::class, 'dashboard'])->name('secretariat.dashboard');
//!ROTTE LOGIN UTENTI
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
//!ROTTE RESET PASSWORD
Route::get('/password-dimenticata', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/password-dimenticata', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
//!ROTTE ADMIN
Route::get('/admin/race/{race}/edit', [AdminController::class, 'editRace'])->name('admin.race.edit');
Route::put('/admin/race/{race}', [AdminController::class, 'updateRace'])->name('admin.race.update');
Route::delete('/admin/race/{race}', [AdminController::class, 'destroyRace'])->name('admin.race.destroy');
Route::get('/admin/race/{race}/report', [AdminController::class, 'raceReport'])->name('admin.raceReport');
Route::get('/admin/timekeeper/{user}/report', [AdminController::class, 'timekeeperReport'])->name('admin.timekeeperReport');
Route::get('/admin/race/{race}/timekeepers', [AdminController::class, 'selectTimekeepers'])->name('race.timekeepers.select');
Route::post('/admin/race/{race}/timekeepers', [AdminController::class, 'assignTimekeepers'])->name('race.timekeepers.assign');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/timekeeperList', [AdminController::class, 'timekeeperListShow'])->name('admin.timekeeperList');
Route::get('/admin/racesList', [AdminController::class, 'racesListShow'])->name('admin.racesList');

Route::get('/admin/racesTempList', [AdminController::class, 'racesTempListShow'])->name('admin.racesTempList');

Route::get('/admin/create/race', [AdminController::class, 'createRaceShow'])->name('admin.createRace.form');
Route::get('/admin/create/availability', [AdminController::class, 'storeAvailabilityForm'])->name('admin.createAvailability.form');
Route::get('/admin/timekeeperDetails/{timekeeper}', [AdminController::class, 'timekeeperDetailsShow'])->name('admin.timekeeperDetails');
Route::post('/admin/timekeeperDetails/update/{timekeeper}', [AdminController::class, 'updateTimekeeper'])->name('update.timekeeper');
Route::post('/admin/race/create', [AdminController::class, 'storeRace'])->name('race.store');
Route::post('/admin/availability/store', [AdminController::class, 'storeAvailability'])->name('availability.store');
//!ROTTE TIMEKEEPER
Route::get('/races/{race}/records', [TimekeeperController::class, 'manage'])->name('records.manage');
Route::post('/races/{race}/records', [TimekeeperController::class, 'store'])->name('records.store');
Route::put('/records/{record}', [TimekeeperController::class, 'update'])->name('records.update');
Route::delete('/records/{record}', [TimekeeperController::class, 'destroy'])->name('records.destroy');
Route::get('/timekeeper/dashboard', [TimekeeperController::class, 'dashboard'])->name('timekeeper.dashboard');
Route::get('/timekeeper/availability', [TimekeeperController::class, 'showForUser'])->name('availability.show');
Route::get('/timekeeper/races', [TimekeeperController::class, 'racesListShow'])->name('timekeeper.racesList');
Route::post('/timekeeper/availability/store', [TimekeeperController::class, 'storeForUser'])->name('availability.storeUser');

//!MIDDLEWERE UTENTE LOGGATO
Route::middleware('auth')->group(function () {
    //!ROTTE CAMBIO PASSWORD
    Route::get('/password/change', [PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'changePassword']);
    Route::delete('/account/delete', [LoginController::class, 'destroy'])->name('user.destroy');
});