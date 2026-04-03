<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\VoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class,'logout']);
    Route::post('/user', [AuthController::class,'user']);

    // Posts
    Route::apiResource('posts', PostController::class)->except(['create, edit']);
    Route::get('posts/{post}/similar', [PostController::class, 'similar']);

    // Votes
    Route::get('votes/{vote}/vote', [VoteController::class, 'vote']);

    // Comments
    Route::apiResource('comments', CommentController::class)->except(['create, edit, show']);
    Route::get('comments/{comment}/vote', [CommentController::class, 'vote']);

    // Activities
    Route::get('activities', [ActivityController::class, 'index']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);

    // Statuses
    Route::get('statuses', [StatusController::class, 'index']);

    // Exports
    Route::get('exports', [ExportController::class, 'export']);

    // Admin Routes
    Route::middleware('is_admin')->group(function() {
        Route::put('admin/posts/{post}/status', [AdminController::class, 'updateStatus']);
        Route::post('admin/comments/{comment}/official', [AdminController::class, 'markOfficial']);
        Route::apiResource('admin/categories', CategoryController::class)->except(['index, show']);
        Route::apiResource('admin/statuses', StatusController::class)->except(['index, show']);
        Route::get('admin/stats', [AdminController::class,'stats']);
        Route::delete('admin/posts/{post}', [AdminController::class,'deletePost']);
        Route::delete('admin/comments/{comment}', [AdminController::class,'deleteComment']);
    });
});