<?php

use Illuminate\Support\Facades\Route;
use App\Facades\AuthenticateAspect;
use App\Http\Controllers\FileControllerPC;
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

Route::get('/', function () {
    return view('welcome');
});
// Route group with the aspect middleware
Route::get('/protected-route', function () {
    // Use the AuthenticateAspect Facade
    return AuthenticateAspect::handle(request(), function ($request) {
        return 'This is a protected route';
    });
})->middleware('auth.aspect');


Route::get('/files', [FileControllerPC::class, 'index'])->name('file.index');
Route::post('/files/upload', [FileControllerPC::class, 'upload'])->name('file.upload');
Route::delete('/files/{fileName}', [FileControllerPC::class, 'delete'])->name('file.delete');
