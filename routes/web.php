<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CharacterController as AdminCharacterController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\KnowledgeController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MerchandiseController as AdminMerchandiseController;
use App\Http\Controllers\Admin\ReleaseController as AdminReleaseController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeExploreController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MerchandiseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeExploreController::class, 'home'])->name('home');
Route::get('/explore', [HomeExploreController::class, 'explore'])->name('explore');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/content/{slug}', [ContentController::class, 'show'])->name('content.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
Route::get('/characters/{slug}', [CharacterController::class, 'show'])->name('characters.show');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/multimedia', [MediaController::class, 'index'])->name('media.index');
Route::get('/multimedia/{slug}', [MediaController::class, 'show'])->name('media.show');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/merchandise', [MerchandiseController::class, 'index'])->name('merchandise.index');
Route::get('/merchandise/{slug}', [MerchandiseController::class, 'show'])->name('merchandise.show');
Route::get('/releases', [ReleaseController::class, 'index'])->name('releases.index');

Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/community/{slug}', [CommunityController::class, 'show'])->name('community.show');

Route::get('/sitemap', [SitemapController::class, 'show'])->name('sitemap');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('feedback.store');
Route::post('/newsletter', [FeedbackController::class, 'subscribe'])
    ->middleware('throttle:6,1')
    ->name('newsletter.store');

// Generated placeholder artwork (deterministic SVG)
Route::get('/ph/{label}.svg', [PlaceholderController::class, 'show'])->name('ph');

/*
|--------------------------------------------------------------------------
| Authentication (session based for the website, Sanctum for /api)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:5,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

/*
|--------------------------------------------------------------------------
| User area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::put('/profile/favorites', [ProfileController::class, 'favorites'])->name('profile.favorites');

    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::put('/bookmarks/{bookmark}', [BookmarkController::class, 'update'])->name('bookmarks.update');
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/security', [SettingsController::class, 'security'])->name('settings.security');

    Route::get('/submit-content', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submit-content', [SubmissionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('submissions.store');
    Route::get('/my-submissions', [SubmissionController::class, 'index'])->name('submissions.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.readAll');
});

// Guests may use these too (rating/bookmark endpoints JSON-guard themselves)
Route::post('/rate', [RatingController::class, 'store'])->name('rate');
Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:20,1')->name('chat.store');
Route::get('/chat/history', [ChatController::class, 'history'])->name('chat.history');

/*
|--------------------------------------------------------------------------
| Admin panel (auth + admin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    Route::resource('users', AdminUserController::class)->except('show');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('content', AdminContentController::class)->except('show');
    Route::resource('characters', AdminCharacterController::class)->except('show');
    Route::resource('articles', AdminArticleController::class)->except('show');
    Route::resource('media', AdminMediaController::class)->except('show');
    Route::resource('events', AdminEventController::class)->except('show');
    Route::resource('merchandise', AdminMerchandiseController::class)->except('show');
    Route::resource('releases', AdminReleaseController::class)->except('show');

    Route::get('submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::patch('submissions/{submission}', [AdminSubmissionController::class, 'status'])->name('submissions.status');
    Route::delete('submissions/{submission}', [AdminSubmissionController::class, 'destroy'])->name('submissions.destroy');

    Route::get('feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::patch('feedback/{feedback}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');

    Route::resource('chatbot', KnowledgeController::class)
        ->except('show')
        ->parameters(['chatbot' => 'knowledge']);
    Route::get('chatbot-queries', [KnowledgeController::class, 'queries'])->name('chatbot.queries');

    Route::get('settings', [AdminSettingsController::class, 'show'])->name('settings.show');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
