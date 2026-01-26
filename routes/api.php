<?php

use Illuminate\Support\Facades\Route;
use Opscale\NovaServiceDesk\Http\Controllers\ToolController;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('tasks', [ToolController::class, 'index']);
Route::get('statuses', [ToolController::class, 'getStatuses']);
Route::put('tasks/{id}/status', [ToolController::class, 'updateStatus']);
