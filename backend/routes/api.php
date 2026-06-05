<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Topics routes
Route::apiResource('topics', App\Http\Controllers\Api\TopicController::class)->except(['store', 'update', 'destroy']);
Route::post('topics', [App\Http\Controllers\Api\TopicController::class, 'store'])->middleware('auth:sanctum');
Route::put('topics/{topic}', [App\Http\Controllers\Api\TopicController::class, 'update'])->middleware('auth:sanctum');
Route::patch('topics/{topic}', [App\Http\Controllers\Api\TopicController::class, 'update'])->middleware('auth:sanctum');
Route::delete('topics/{topic}', [App\Http\Controllers\Api\TopicController::class, 'destroy'])->middleware('auth:sanctum');

// Topic replies routes
Route::get('topics/{topic}/replies', [App\Http\Controllers\Api\ReplyController::class, 'index']);
Route::post('topics/{topic}/replies', [App\Http\Controllers\Api\ReplyController::class, 'store'])->middleware('auth:sanctum');

// Replies routes (for update/delete)
Route::apiResource('replies', App\Http\Controllers\Api\ReplyController::class)->except(['index', 'store']);

// Lost Pets routes
Route::get('lost-pets/map-markers', [App\Http\Controllers\Api\LostPetController::class, 'mapMarkers']);
Route::apiResource('lost-pets', App\Http\Controllers\Api\LostPetController::class)->except(['store', 'update', 'destroy']);
Route::post('lost-pets', [App\Http\Controllers\Api\LostPetController::class, 'store'])->middleware('auth:sanctum');
Route::put('lost-pets/{lostPet}', [App\Http\Controllers\Api\LostPetController::class, 'update'])->middleware('auth:sanctum');
Route::patch('lost-pets/{lostPet}', [App\Http\Controllers\Api\LostPetController::class, 'update'])->middleware('auth:sanctum');
Route::delete('lost-pets/{lostPet}', [App\Http\Controllers\Api\LostPetController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('lost-pets/{lostPet}/mark-found', [App\Http\Controllers\Api\LostPetController::class, 'markFound'])->middleware('auth:sanctum');
Route::post('lost-pets/{lostPet}/close', [App\Http\Controllers\Api\LostPetController::class, 'close'])->middleware('auth:sanctum');

// Pet Clues routes
Route::get('lost-pets/{lostPet}/clues', [App\Http\Controllers\Api\PetClueController::class, 'index']);
Route::post('lost-pets/{lostPet}/clues', [App\Http\Controllers\Api\PetClueController::class, 'store'])->middleware('auth:sanctum');
Route::apiResource('pet-clues', App\Http\Controllers\Api\PetClueController::class)->except(['index', 'store']);
Route::post('pet-clues/{clue}/verify', [App\Http\Controllers\Api\PetClueController::class, 'verify'])->middleware('auth:sanctum');
