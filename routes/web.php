<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\CsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\DisplayController;

/*
|--------------------------------------------------------------------------
| Web Routes - Indibiz Queue System
|--------------------------------------------------------------------------
*/

// Redirect Halaman Utama ke Antrean
Route::get('/', function () {
    return redirect()->route('antrean.index');
});

// 1. MODUL PELANGGAN (PUBLIC)
Route::get('/antrean', [TiketController::class, 'index'])->name('antrean.index');
Route::post('/antrean', [TiketController::class, 'store'])->name('antrean.store');
Route::get('/antrean/{id}', [TiketController::class, 'showTiket'])->name('antrean.tiket'); 

// Halaman Publik Monitor TV Antrean & Endpoint Datanya
Route::get('/display-antrean', [DisplayController::class, 'index'])->name('antrean.display');
Route::get('/api/display-antrean-data', [DisplayController::class, 'getDataJson']);
// Proxy ElevenLabs TTS untuk Display Monitor TV
Route::post('/api/elevenlabs-tts', [DisplayController::class, 'ttsElevenLabs']);

// 2. MODUL AUTHENTICATION (LOGIN/LOGOUT)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route Pilih Meja CS Console
Route::middleware(['auth'])->group(function () {
    Route::get('/cs/pilih-meja', [CsController::class, 'selectMeja'])->name('cs.select-meja');
    Route::post('/cs/pilih-meja', [CsController::class, 'setMeja'])->name('cs.process-meja');
    Route::get('/cs/leave', [CsController::class, 'leaveConsole'])->name('cs.leave');
    
    // ENDPOINT HEARTBEAT PING CS DESK
    Route::post('/cs/ping-heartbeat', [CsController::class, 'pingHeartbeat'])->name('cs.ping');
});

// 3. MODUL DENGAN PROTEKSI LOGIN
Route::middleware(['auth'])->group(function () {
    
    // Console CS (Dapat diakses CS maupun Admin)
    Route::get('/cs-desk', [CsController::class, 'index'])->name('cs.index');
    Route::post('/cs-desk/panggil-selanjutnya', [CsController::class, 'panggilSelanjutnya'])->name('cs.panggil_selanjutnya');
    Route::post('/cs-desk/panggil-spesifik/{id}', [CsController::class, 'panggilSpesifik'])->name('cs.panggil_spesifik');
    Route::post('/cs-desk/batal-atau-kembalikan/{id}', [CsController::class, 'batalAtauKembalikan'])->name('cs.batal_atau_kembalikan');
    Route::post('/cs-desk/selesaikan/{id}', [CsController::class, 'selesaikanTiket'])->name('cs.selesaikan');

    // 4. MODUL SUPER ADMIN (KHUSUS ROLE ADMIN)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin-dashboard/export', [AdminController::class, 'exportCsv'])->name('admin.export');
        Route::get('/admin-dashboard/pdf', [AdminController::class, 'cetakPdf'])->name('admin.pdf');

        // CRUD Staff CS
        Route::post('/admin/staff/store', [StaffManagementController::class, 'storeUser'])->name('admin.staff.store');
        Route::post('/admin/staff/update-status/{id}', [StaffManagementController::class, 'updateStatusUser'])->name('admin.staff.update_status');
        Route::delete('/admin/staff/delete/{id}', [StaffManagementController::class, 'destroyUser'])->name('admin.staff.delete');

        // CRUD Master Meja
        Route::post('/admin/meja/store', [StaffManagementController::class, 'storeMeja'])->name('admin.meja.store');
        Route::post('/admin/meja/toggle/{id}', [StaffManagementController::class, 'toggleMeja'])->name('admin.meja.toggle');
        Route::delete('/admin/meja/delete/{id}', [StaffManagementController::class, 'destroyMeja'])->name('admin.meja.delete');

    });
});