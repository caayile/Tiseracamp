<?php

use App\Http\Controllers\Admin\BatchController as AdminBatchController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\Mentor\AnnouncementController as MentorAnnouncementController;
use App\Http\Controllers\Mentor\ApplicationController as MentorApplicationController;
use App\Http\Controllers\Mentor\AssignmentController as MentorAssignmentController;
use App\Http\Controllers\Mentor\ChatController as MentorChatController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\ProgramController as MentorProgramController;
use App\Http\Controllers\Mentor\ScheduleController as MentorScheduleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [ProgramController::class, 'show'])->name('programs.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify-email', [AuthController::class, 'verify'])->name('verify.submit');
    Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])->name('verify.resend');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/applications', [ProfileController::class, 'applications'])->name('profile.applications');
    Route::get('/profile/logbook', [ProfileController::class, 'logbook'])->name('profile.logbook');

    Route::get('/internships/{program}/apply', [InternshipApplicationController::class, 'create'])->name('internships.apply');
    Route::post('/internships/{program}/apply', [InternshipApplicationController::class, 'store'])->name('internships.store');
    Route::get('/internships/{program}/status', [InternshipApplicationController::class, 'status'])->name('internships.status');

    Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    Route::delete('/logbook/{logbook}', [LogbookController::class, 'destroy'])->name('logbook.destroy');

    Route::post('/programs/{program}/enroll', [DashboardController::class, 'enroll'])->name('programs.enroll');
    Route::get('/learn/{program}', [DashboardController::class, 'learn'])->name('learn.show');
    Route::get('/learn/{program}/lessons/{lesson}', [DashboardController::class, 'lesson'])->name('learn.lesson');
    Route::post('/learn/{program}/lessons/{lesson}/complete', [DashboardController::class, 'completeLesson'])->name('learn.complete');
    Route::post('/learn/{program}/lessons/{lesson}/submit', [DashboardController::class, 'submitAssignment'])->name('learn.submit');
    Route::post('/learn/{program}/lessons/{lesson}/note', [DashboardController::class, 'saveNote'])->name('learn.note');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/checkout/{program}', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/payments/checkout/{program}', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/programs/{program}/chat', [ChatController::class, 'start'])->name('chat.start');

    Route::get('/career', [CareerController::class, 'index'])->name('career.index');
    Route::post('/career/portfolio', [CareerController::class, 'storePortfolio'])->name('career.portfolio.store');
    Route::delete('/career/portfolio/{portfolio}', [CareerController::class, 'destroyPortfolio'])->name('career.portfolio.destroy');

    Route::post('/programs/{program}/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{discussion}/reply', [DiscussionController::class, 'reply'])->name('discussions.reply');

    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::middleware(['auth', 'active', 'mentor'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/programs', [MentorProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [MentorProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [MentorProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/curriculum', [MentorProgramController::class, 'curriculum'])->name('programs.curriculum');
    Route::get('/programs/{program}/students', [MentorProgramController::class, 'students'])->name('programs.students');
    Route::post('/programs/{program}/modules', [MentorProgramController::class, 'storeModule'])->name('modules.store');
    Route::post('/modules/{module}/lessons', [MentorProgramController::class, 'storeLesson'])->name('lessons.store');
    Route::post('/lessons/{lesson}/assignments', [MentorAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/submissions', [MentorAssignmentController::class, 'submissions'])->name('submissions');
    Route::post('/submissions/{submission}/review', [MentorAssignmentController::class, 'review'])->name('submissions.review');
    Route::get('/schedules', [MentorScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [MentorScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/{schedule}/recording', [MentorScheduleController::class, 'uploadRecording'])->name('schedules.recording');
    Route::post('/announcements', [MentorAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/applications', [MentorApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/{application}/review', [MentorApplicationController::class, 'review'])->name('applications.review');
    Route::get('/chat', [MentorChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [MentorChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [MentorChatController::class, 'send'])->name('chat.send');
});

Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/programs', [AdminProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [AdminProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [AdminProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [AdminProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [AdminProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [AdminProgramController::class, 'destroy'])->name('programs.destroy');
    Route::post('/programs/{program}/approve', [AdminProgramController::class, 'approve'])->name('programs.approve');
    Route::get('/programs/{program}/curriculum', [AdminProgramController::class, 'curriculum'])->name('programs.curriculum');
    Route::post('/programs/{program}/modules', [AdminProgramController::class, 'storeModule'])->name('modules.store');
    Route::post('/programs/{program}/batches', [AdminBatchController::class, 'store'])->name('batches.store');
    Route::delete('/modules/{module}', [AdminProgramController::class, 'destroyModule'])->name('modules.destroy');
    Route::post('/modules/{module}/lessons', [AdminProgramController::class, 'storeLesson'])->name('lessons.store');
    Route::delete('/lessons/{lesson}', [AdminProgramController::class, 'destroyLesson'])->name('lessons.destroy');

    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');

    Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
    Route::post('/content/articles', [AdminContentController::class, 'storeArticle'])->name('content.articles');
    Route::post('/content/banners', [AdminContentController::class, 'storeBanner'])->name('content.banners');
    Route::post('/content/faqs', [AdminContentController::class, 'storeFaq'])->name('content.faqs');
    Route::post('/content/categories', [AdminContentController::class, 'storeCategory'])->name('content.categories');
    Route::post('/content/broadcast', [AdminContentController::class, 'broadcast'])->name('content.broadcast');
});
