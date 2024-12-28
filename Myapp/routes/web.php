<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('/home', function () {
    return view('Home');
})->middleware(['auth', 'verified'])->name('Home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Upload routes
    Route::get('/upload/{view}', [FileController::class, 'showUploadForm'])->name('upload.form');
    Route::post('/upload', [FileController::class, 'uploadFile'])->name('upload.chunk');
    Route::post('/upload/decrypt', [FileController::class, 'uploadEncryptedFileChunked'])->name('upload.decrypted.file');
    // dawnload routes
    Route::get('/download/{filename}', [FileController::class, 'downloadFile'])->name('download.file');
    Route::get('/downloadDecrypted/{filename}', [FileController::class, 'downloadDecrypted'])->name('downloadDecrypted.file');
});

require __DIR__.'/auth.php';
