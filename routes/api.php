<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\XssSanitization;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\SubTaskController;

Route::group(['middleware' => [XssSanitization::class]], function() {
    Route::controller(TaskController::class)->prefix('/v1/task')->as('v1/task/')->group( function() {
        Route::post('add', 'store')->name('v1.task.add');
        Route::get('list', 'filtering')->name('v1.task.list');
        Route::get('edit/{id}', 'editRecord')->name('v1.task.edit');
        Route::put('update/{id}', 'updateRecord')->name('v1.task.update_record');
        Route::delete('delete/{id}', 'delete')->name('v1.task.delete');
    });

    Route::controller(SubTaskController::class)->prefix('/v1/sub-task')->as('v1/sub-task/')->group( function() {
        Route::post('add', 'store')->name('v1.sub_task.add');
        Route::get('list', 'filtering')->name('v1.sub_task.list');
        Route::get('edit/{id}', 'editRecord')->name('v1.sub_task.edit');
        Route::put('update/{id}', 'updateRecord')->name('v1.task.update_record');
        Route::delete('delete/{id}', 'delete')->name('v1.sub_task.delete');
    });

    Route::get('v1/check-for-permanently-delete', [TaskController::class,'runScheduler'])->name('v1.scheduler');
});