<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

// Migrtions
Route::get('/db-migrate', function () {
    $exitCode = Artisan::call('migrate:fresh', [
        '--force' => true,
    ]);
    print_r("Migrations completed suceesfully");die;
});
