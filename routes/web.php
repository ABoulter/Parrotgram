<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\parrotcontroller;


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

Route::get('/', [UserController::class, "showCorrectHomepage"]);

Route::get('/about', [parrotcontroller::class, "aboutPage"]);

route::post('/register', [UserController::class, 'register']);

route::post('/login', [UserController::class, 'login']);