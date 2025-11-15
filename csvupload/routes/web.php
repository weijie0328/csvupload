<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsvController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [CsvController::class, 'index'])->name('csv.index');
Route::post('/upload', [CsvController::class, 'upload'])->name('csv.upload');
Route::get('/status', [CsvController::class, 'status'])->name('csv.status');

Route::get('/upload', function () {
    return view('upload');
});

