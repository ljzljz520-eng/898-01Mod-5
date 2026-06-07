<?php

use Illuminate\Support\Facades\Route;

// 首页重定向到主题列表
Route::get('/', function () {
    return redirect()->route('topics.index');
});

// 前台认证路由
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 主题路由
Route::resource('topics', App\Http\Controllers\TopicController::class);
Route::post('topics/{topic}/replies', [App\Http\Controllers\ReplyController::class, 'store'])->name('replies.store')->middleware('auth');
Route::delete('replies/{reply}', [App\Http\Controllers\ReplyController::class, 'destroy'])->name('replies.destroy')->middleware('auth');

// 物业公告路由
Route::prefix('property-notices')->middleware('auth')->group(function () {
    Route::get('/create', [App\Http\Controllers\PropertyNoticeWebController::class, 'create'])->name('property-notices.create');
    Route::post('/', [App\Http\Controllers\PropertyNoticeWebController::class, 'store'])->name('property-notices.store');
    Route::get('/{topic}/edit', [App\Http\Controllers\PropertyNoticeWebController::class, 'edit'])->name('property-notices.edit');
    Route::put('/{topic}', [App\Http\Controllers\PropertyNoticeWebController::class, 'update'])->name('property-notices.update');
    Route::get('/{topic}/read-receipts', [App\Http\Controllers\PropertyNoticeWebController::class, 'readReceipts'])->name('property-notices.read-receipts')->middleware('can:admin');
    Route::get('/{topic}/phone-reminders', [App\Http\Controllers\PropertyNoticeWebController::class, 'phoneReminders'])->name('property-notices.phone-reminders')->middleware('can:admin');
    Route::get('/{topic}/version-history', [App\Http\Controllers\PropertyNoticeWebController::class, 'versionHistory'])->name('property-notices.version-history');
});
