<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SSEController;
use Illuminate\Support\Facades\Route;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->get('/auth/{provider}/redirect', [AuthController::class, 'providerRedirectURL']);
Route::middleware('auth:sanctum')->post('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::get('/{oauth_id}/{provider}/sse', [SSEController::class, 'sse']);

Route::middleware('auth:sanctum')->get('/{provider}/folders', [EmailController::class, 'getFolders']);
Route::middleware('auth:sanctum')->get('/{provider}/emails/{folderId}', [EmailController::class, 'getEmailsByFolder']);

