<?php

use App\Http\Controllers\Api\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CharacterController as AdminCharacterController;
use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Api\Admin\KnowledgeController as AdminKnowledgeController;
use App\Http\Controllers\Api\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Api\Admin\MerchandiseController as AdminMerchandiseController;
use App\Http\Controllers\Api\Admin\ReleaseController as AdminReleaseController;
use App\Http\Controllers\Api\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CharacterController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MerchandiseController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public read endpoints
|--------------------------------------------------------------------------
*/

Route::get('/home', [HomeController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/content', [ContentController::class, 'index']);
Route::get('/content/{slug}', [ContentController::class, 'show']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/characters', [CharacterController::class, 'index']);
Route::get('/characters/{slug}', [CharacterController::class, 'show']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show']);
Route::get('/media', [MediaController::class, 'index']);
Route::get('/media/{slug}', [MediaController::class, 'show']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{slug}', [EventController::class, 'show']);
Route::get('/merchandise', [MerchandiseController::class, 'index']);
Route::get('/releases', [ReleaseController::class, 'index']);
Route::get('/meta', [MetaController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authentication (Sanctum personal access tokens)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Authenticated user endpoints
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::patch('/bookmarks/{bookmark}', [BookmarkController::class, 'update']);
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);

    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/fan-submissions', [SubmissionController::class, 'index']);
    Route::post('/fan-submissions', [SubmissionController::class, 'store'])->middleware('throttle:10,1');

    Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:20,1');

    Route::get('/preferences', [PreferenceController::class, 'show']);
    Route::put('/preferences', [PreferenceController::class, 'update']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}', [NotificationController::class, 'read']);
});

/*
|--------------------------------------------------------------------------
| Admin endpoints
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Admin endpoints
|--------------------------------------------------------------------------
|
| The apiResource names are prefixed with `api.admin.` so they never shadow
| the website's named routes (characters.index, events.index, ...): without
| it every route('characters.index') link resolved to /api/admin/characters.
|
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/analytics', [AdminAnalyticsController::class, 'index']);
    Route::get('/settings', [AdminSettingsController::class, 'show']);
    Route::put('/settings', [AdminSettingsController::class, 'update']);

    Route::name('api.admin.')->group(function () {
        Route::apiResource('users', AdminUserController::class)->except('show');
        Route::apiResource('categories', AdminCategoryController::class)->except('show');
        Route::apiResource('content', AdminContentController::class)->except('show');
        Route::apiResource('characters', AdminCharacterController::class)->except('show');
        Route::apiResource('articles', AdminArticleController::class)->except('show');
        Route::apiResource('media', AdminMediaController::class)->except('show');
        Route::apiResource('events', AdminEventController::class)->except('show');
        Route::apiResource('merchandise', AdminMerchandiseController::class)->except('show');
        Route::apiResource('releases', AdminReleaseController::class)->except('show');
    });

    Route::get('/submissions', [AdminSubmissionController::class, 'index']);
    Route::patch('/submissions/{submission}', [AdminSubmissionController::class, 'status']);
    Route::delete('/submissions/{submission}', [AdminSubmissionController::class, 'destroy']);

    Route::get('/feedback', [AdminFeedbackController::class, 'index']);
    Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update']);
    Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy']);

    Route::name('api.admin.')->group(function () {
        Route::apiResource('knowledge', AdminKnowledgeController::class)->except('show');
    });
    Route::get('/chat-queries', [AdminKnowledgeController::class, 'queries']);
});
