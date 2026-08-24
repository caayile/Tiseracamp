<?php

use App\Http\Controllers\Admin\AchievementController as AdminAchievementController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\JobApplicationController as AdminJobApplicationController;
use App\Http\Controllers\Admin\SitePageController as AdminSitePageController;
use App\Http\Controllers\Admin\BatchController as AdminBatchController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\CvSubscriptionController as AdminCvSubscriptionController;
use App\Http\Controllers\Admin\CvPlanController as AdminCvPlanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GradeController as AdminGradeController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
use App\Http\Controllers\Admin\PaymentAccountController as AdminPaymentAccountController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ScreeningController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CvReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Mentor\AnnouncementController as MentorAnnouncementController;
use App\Http\Controllers\Mentor\ApplicationController as MentorApplicationController;
use App\Http\Controllers\Mentor\AssignmentController as MentorAssignmentController;
use App\Http\Controllers\Mentor\ChatController as MentorChatController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\DiscussionController as MentorDiscussionController;
use App\Http\Controllers\Mentor\GradeController as MentorGradeController;
use App\Http\Controllers\Mentor\LogbookController as MentorLogbookController;
use App\Http\Controllers\Mentor\ProgramController as MentorProgramController;
use App\Http\Controllers\Mentor\ScheduleController as MentorScheduleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [ProgramController::class, 'show'])->name('programs.show');
Route::get('/syarat-ketentuan', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/sertifikat/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
    Route::get('/forgot-password/otp', [AuthController::class, 'showOtp'])->name('password.otp');
    Route::post('/forgot-password/otp', [AuthController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::post('/forgot-password/otp/resend', [AuthController::class, 'resendOtp'])->name('password.otp.resend');
    Route::get('/reset-password', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyFromLink'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerification'])->name('verify.resend');

    Route::get('/screening', [ScreeningController::class, 'show'])->name('screening.show');
    Route::post('/screening', [ScreeningController::class, 'store'])->name('screening.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/applications', [ProfileController::class, 'applications'])->name('profile.applications');
    Route::get('/profile/logbook', [ProfileController::class, 'logbook'])->name('profile.logbook');

    Route::get('/internships/{program}/apply', [InternshipApplicationController::class, 'create'])->name('internships.apply');
    Route::post('/internships/{program}/apply', [InternshipApplicationController::class, 'store'])->name('internships.store');
    Route::get('/internships/{program}/status', [InternshipApplicationController::class, 'status'])->name('internships.status');
    Route::get('/internships/{program}/nilai', [InternshipApplicationController::class, 'grade'])->name('internships.grade');

    Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    Route::get('/logbook/export/pdf', [LogbookController::class, 'exportPdf'])->name('logbook.export.pdf');
    Route::get('/logbook/export/excel', [LogbookController::class, 'exportExcel'])->name('logbook.export.excel');
    Route::delete('/logbook/{logbook}', [LogbookController::class, 'destroy'])->name('logbook.destroy');

    Route::post('/programs/{program}/enroll', [DashboardController::class, 'enroll'])->name('programs.enroll');
    Route::get('/learn/{program}', [DashboardController::class, 'learn'])->name('learn.show');
    Route::get('/learn/{program}/lessons/{lesson}', [DashboardController::class, 'lesson'])->name('learn.lesson');
    Route::post('/learn/{program}/lessons/{lesson}/complete', [DashboardController::class, 'completeLesson'])->name('learn.complete');
    Route::post('/learn/{program}/lessons/{lesson}/submit', [DashboardController::class, 'submitAssignment'])->name('learn.submit');
    Route::post('/learn/{program}/lessons/{lesson}/note', [DashboardController::class, 'saveNote'])->name('learn.note');
    Route::post('/learn/{program}/feedback', [DashboardController::class, 'storeFeedback'])->name('learn.feedback');
    Route::get('/learn/{program}/certificate', [DashboardController::class, 'certificate'])->name('learn.certificate');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/checkout/{program}', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/payments/checkout/{program}', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');

    Route::get('/cv-review/plans', [CvReviewController::class, 'plans'])->name('cv-review.plans');
    Route::get('/cv-review/checkout/{plan}', [CvReviewController::class, 'checkout'])->name('cv-review.checkout');
    Route::post('/cv-review/checkout/{plan}', [CvReviewController::class, 'purchase'])->name('cv-review.purchase');
    Route::get('/cv-review', [CvReviewController::class, 'index'])->name('cv-review.index');
    Route::post('/cv-review', [CvReviewController::class, 'store'])
        ->middleware('throttle:8,1')
        ->name('cv-review.store');
    Route::get('/cv-review/{cvReview}', [CvReviewController::class, 'show'])->name('cv-review.show');
    Route::post('/cv-review/{cvReview}/cover-letter', [CvReviewController::class, 'generateCoverLetter'])
        ->middleware('throttle:6,1')
        ->name('cv-review.cover-letter');
    Route::post('/cv-review/{cvReview}/interview', [CvReviewController::class, 'generateInterview'])
        ->middleware('throttle:6,1')
        ->name('cv-review.interview');
    Route::post('/cv-review/{cvReview}/interview/answer', [CvReviewController::class, 'submitInterviewAnswer'])
        ->middleware('throttle:12,1')
        ->name('cv-review.interview.answer');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{conversation}/poll', [ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/{conversation}', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/programs/{program}/chat', [ChatController::class, 'start'])->name('chat.start');

    Route::get('/testimonials/{enrollment}/create', [TestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('/testimonials/{enrollment}', [TestimonialController::class, 'store'])->name('testimonials.store');

    Route::get('/career', [CareerController::class, 'index'])->name('career.index');
    Route::get('/career/portfolios', [CareerController::class, 'gallery'])->name('career.gallery');
    Route::get('/career/jobs', [CareerController::class, 'jobs'])->name('career.jobs');
    Route::post('/career/portfolio', [CareerController::class, 'storePortfolio'])->name('career.portfolio.store');
    Route::delete('/career/portfolio/{portfolio}', [CareerController::class, 'destroyPortfolio'])->name('career.portfolio.destroy');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    Route::get('/jobs/{program}/apply', [JobApplicationController::class, 'create'])->name('jobs.apply');
    Route::post('/jobs/{program}/apply', [JobApplicationController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{program}/status', [JobApplicationController::class, 'status'])->name('jobs.status');

    Route::post('/programs/{program}/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{discussion}/reply', [DiscussionController::class, 'reply'])->name('discussions.reply');

    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'active', 'mentor'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('/', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/programs', [MentorProgramController::class, 'index'])->name('programs.index');
    Route::get('/internships', [MentorProgramController::class, 'internships'])->name('internships.index');
    Route::get('/internships/create', [MentorProgramController::class, 'createInternship'])->name('internships.create');
    Route::post('/internships', [MentorProgramController::class, 'storeInternship'])->name('internships.store');
    Route::post('/internships/{program}/claim', [MentorProgramController::class, 'claimInternship'])->name('internships.claim');
    Route::get('/internships/{program}/edit', [MentorProgramController::class, 'editInternship'])->name('internships.edit');
    Route::put('/internships/{program}', [MentorProgramController::class, 'updateInternship'])->name('internships.update');
    Route::put('/internships/{program}/quota', [MentorProgramController::class, 'updateInternshipQuota'])->name('internships.quota');
    Route::get('/internships/{program}/curriculum', [MentorProgramController::class, 'internshipCurriculum'])->name('internships.curriculum');
    Route::get('/programs/create', [MentorProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [MentorProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [MentorProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [MentorProgramController::class, 'update'])->name('programs.update');
    Route::get('/programs/{program}/curriculum', [MentorProgramController::class, 'curriculum'])->name('programs.curriculum');
    Route::get('/programs/{program}/students', [MentorProgramController::class, 'students'])->name('programs.students');
    Route::post('/enrollments/{enrollment}/rate', [MentorProgramController::class, 'rateStudent'])->name('enrollments.rate');
    Route::post('/programs/{program}/modules', [MentorProgramController::class, 'storeModule'])->name('modules.store');
    Route::put('/modules/{module}', [MentorProgramController::class, 'updateModule'])->name('modules.update');
    Route::post('/modules/{module}/lessons', [MentorProgramController::class, 'storeLesson'])->name('lessons.store');
    Route::delete('/modules/{module}', [MentorProgramController::class, 'destroyModule'])->name('modules.destroy');
    Route::delete('/lessons/{lesson}', [MentorProgramController::class, 'destroyLesson'])->name('lessons.destroy');
    Route::post('/lessons/{lesson}/assignments', [MentorAssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/assignments/{assignment}/questions', [MentorAssignmentController::class, 'storeQuestion'])->name('assignments.questions');
    Route::get('/submissions', [MentorAssignmentController::class, 'submissions'])->name('submissions');
    Route::post('/submissions/{submission}/review', [MentorAssignmentController::class, 'review'])->name('submissions.review');
    Route::get('/schedules', [MentorScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [MentorScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [MentorScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [MentorScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{schedule}/recording', [MentorScheduleController::class, 'uploadRecording'])->name('schedules.recording');
    Route::get('/applications', [MentorApplicationController::class, 'index'])->name('applications.index');
    Route::get('/discussions', [MentorDiscussionController::class, 'index'])->name('discussions.index');
    Route::get('/discussions/{discussion}', [MentorDiscussionController::class, 'show'])->name('discussions.show');
    Route::get('/logbooks', [MentorLogbookController::class, 'index'])->name('logbooks.index');
    Route::get('/logbooks/{user}/export', [MentorLogbookController::class, 'exportExcel'])->name('logbooks.export');
    Route::get('/logbooks/{user}', [MentorLogbookController::class, 'show'])->name('logbooks.show');
    Route::get('/logbooks/{logbook}/dokumentasi', [MentorLogbookController::class, 'attachment'])->name('logbooks.attachment');
    Route::post('/logbooks/{logbook}/review', [MentorLogbookController::class, 'review'])->name('logbooks.review');
    Route::get('/grades', [MentorGradeController::class, 'index'])->name('grades.index');
    Route::put('/grades/{enrollment}', [MentorGradeController::class, 'update'])->name('grades.update');
    Route::get('/grades/{enrollment}/print', [MentorGradeController::class, 'print'])->name('grades.print');
    Route::get('/announcements', [MentorAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [MentorAnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [MentorAnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [MentorAnnouncementController::class, 'destroy'])->name('announcements.destroy');
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
    Route::post('/users/{user}/approve-tsu', [AdminUserController::class, 'approveTsu'])->name('users.approve-tsu');
    Route::post('/users/{user}/reject-tsu', [AdminUserController::class, 'rejectTsu'])->name('users.reject-tsu');
    Route::post('/users/{user}/revoke-tsu', [AdminUserController::class, 'revokeTsu'])->name('users.revoke-tsu');

    Route::get('/programs', [AdminProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [AdminProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [AdminProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [AdminProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [AdminProgramController::class, 'update'])->name('programs.update');
    Route::get('/programs/{program}/publikasi', [AdminProgramController::class, 'publikasi'])->name('programs.publikasi');
    Route::put('/programs/{program}/publikasi', [AdminProgramController::class, 'updatePublikasi'])->name('programs.publikasi.update');
    Route::delete('/programs/{program}', [AdminProgramController::class, 'destroy'])->name('programs.destroy');
    Route::post('/programs/{program}/approve', [AdminProgramController::class, 'approve'])->name('programs.approve');
    Route::post('/programs/{program}/reject', [AdminProgramController::class, 'reject'])->name('programs.reject');
    Route::post('/programs/{program}/toggle-open', [AdminProgramController::class, 'toggleOpen'])->name('programs.toggle-open');
    Route::get('/programs/{program}/curriculum', [AdminProgramController::class, 'curriculum'])->name('programs.curriculum');
    Route::post('/programs/{program}/mentor', [AdminProgramController::class, 'assignMentor'])->name('programs.mentor');
    Route::post('/programs/{program}/modules', [AdminProgramController::class, 'storeModule'])->name('modules.store');
    Route::put('/modules/{module}', [AdminProgramController::class, 'updateModule'])->name('modules.update');
    Route::post('/programs/{program}/batches', [AdminBatchController::class, 'store'])->name('batches.store');
    Route::delete('/modules/{module}', [AdminProgramController::class, 'destroyModule'])->name('modules.destroy');
    Route::post('/modules/{module}/lessons', [AdminProgramController::class, 'storeLesson'])->name('lessons.store');
    Route::delete('/lessons/{lesson}', [AdminProgramController::class, 'destroyLesson'])->name('lessons.destroy');

    Route::get('/pendaftar', [AdminApplicationController::class, 'index'])->name('applications.pendaftar');
    Route::get('/pendaftar/export', [AdminApplicationController::class, 'exportSpreadsheet'])->name('applications.export');
    Route::get('/pendaftar/berkas.zip', [AdminApplicationController::class, 'exportZip'])->name('applications.zip');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/berkas/{type}', [AdminApplicationController::class, 'document'])
        ->whereIn('type', ['cv', 'transcript', 'cover-letter', 'portfolio'])
        ->name('applications.document');
    Route::post('/applications/{application}/dates', [AdminApplicationController::class, 'updateDates'])->name('applications.dates');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/review', [AdminApplicationController::class, 'review'])->name('applications.review');

    Route::get('/job-applications', [AdminJobApplicationController::class, 'index'])->name('job-applications.index');
    Route::post('/job-applications/{application}/review', [AdminJobApplicationController::class, 'review'])->name('job-applications.review');

    Route::get('/grades', [AdminGradeController::class, 'index'])->name('grades.index');
    Route::put('/grades/{enrollment}', [AdminGradeController::class, 'update'])->name('grades.update');
    Route::get('/grades/{enrollment}/print', [AdminGradeController::class, 'print'])->name('grades.print');

    Route::get('/logbooks', [AdminLogbookController::class, 'index'])->name('logbooks.index');
    Route::get('/logbooks/{user}/export', [AdminLogbookController::class, 'exportExcel'])->name('logbooks.export');
    Route::get('/logbooks/{user}', [AdminLogbookController::class, 'show'])->name('logbooks.show');
    Route::get('/logbooks/{logbook}/dokumentasi', [AdminLogbookController::class, 'attachment'])->name('logbooks.attachment');
    Route::post('/logbooks/{logbook}/review', [AdminLogbookController::class, 'review'])->name('logbooks.review');

    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::get('/payment-account', [AdminPaymentAccountController::class, 'edit'])->name('payment-account.edit');
    Route::put('/payment-account', [AdminPaymentAccountController::class, 'update'])->name('payment-account.update');

    Route::get('/cv-subscriptions', [AdminCvSubscriptionController::class, 'index'])->name('cv-subscriptions.index');
    Route::post('/cv-subscriptions/{subscription}/verify', [AdminCvSubscriptionController::class, 'verify'])->name('cv-subscriptions.verify');

    Route::get('/cv-plans', [AdminCvPlanController::class, 'index'])->name('cv-plans.index');
    Route::get('/cv-plans/create', [AdminCvPlanController::class, 'create'])->name('cv-plans.create');
    Route::post('/cv-plans', [AdminCvPlanController::class, 'store'])->name('cv-plans.store');
    Route::get('/cv-plans/{cvPlan}/edit', [AdminCvPlanController::class, 'edit'])->name('cv-plans.edit');
    Route::put('/cv-plans/{cvPlan}', [AdminCvPlanController::class, 'update'])->name('cv-plans.update');
    Route::delete('/cv-plans/{cvPlan}', [AdminCvPlanController::class, 'destroy'])->name('cv-plans.destroy');

    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [AdminScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [AdminChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [AdminChatController::class, 'send'])->name('chat.send');

    Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
    Route::post('/content/articles', [AdminContentController::class, 'storeArticle'])->name('content.articles');
    Route::put('/content/articles/{article}', [AdminContentController::class, 'updateArticle'])->name('content.articles.update');
    Route::delete('/content/articles/{article}', [AdminContentController::class, 'destroyArticle'])->name('content.articles.destroy');
    Route::post('/content/banners', [AdminContentController::class, 'storeBanner'])->name('content.banners');
    Route::put('/content/banners/{banner}', [AdminContentController::class, 'updateBanner'])->name('content.banners.update');
    Route::delete('/content/banners/{banner}', [AdminContentController::class, 'destroyBanner'])->name('content.banners.destroy');
    Route::post('/content/faqs', [AdminContentController::class, 'storeFaq'])->name('content.faqs');
    Route::put('/content/faqs/{faq}', [AdminContentController::class, 'updateFaq'])->name('content.faqs.update');
    Route::delete('/content/faqs/{faq}', [AdminContentController::class, 'destroyFaq'])->name('content.faqs.destroy');
    Route::post('/content/categories', [AdminContentController::class, 'storeCategory'])->name('content.categories');
    Route::put('/content/categories/{category}', [AdminContentController::class, 'updateCategory'])->name('content.categories.update');
    Route::delete('/content/categories/{category}', [AdminContentController::class, 'destroyCategory'])->name('content.categories.destroy');
    Route::post('/content/broadcast', [AdminContentController::class, 'broadcast'])->name('content.broadcast');

    Route::get('/achievements', [AdminAchievementController::class, 'index'])->name('achievements.index');
    Route::post('/achievements', [AdminAchievementController::class, 'store'])->name('achievements.store');
    Route::put('/achievements/{achievement}', [AdminAchievementController::class, 'update'])->name('achievements.update');
    Route::delete('/achievements/{achievement}', [AdminAchievementController::class, 'destroy'])->name('achievements.destroy');

    Route::get('/site-pages', [AdminSitePageController::class, 'edit'])->name('site-pages.edit');
    Route::put('/site-pages/{sitePage}', [AdminSitePageController::class, 'update'])->name('site-pages.update');

    Route::get('/partners', [AdminPartnerController::class, 'index'])->name('partners.index');
    Route::post('/partners', [AdminPartnerController::class, 'store'])->name('partners.store');
    Route::put('/partners/{partner}', [AdminPartnerController::class, 'update'])->name('partners.update');
    Route::delete('/partners/{partner}', [AdminPartnerController::class, 'destroy'])->name('partners.destroy');

    Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimonials/{testimonial}/publish', [AdminTestimonialController::class, 'publish'])->name('testimonials.publish');
    Route::delete('/testimonials/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

    Route::get('/portfolios', [AdminPortfolioController::class, 'index'])->name('portfolios.index');
    Route::post('/portfolios', [AdminPortfolioController::class, 'store'])->name('portfolios.store');
    Route::delete('/portfolios/{portfolio}', [AdminPortfolioController::class, 'destroy'])->name('portfolios.destroy');
});
