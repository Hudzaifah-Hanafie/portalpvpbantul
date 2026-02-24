<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\OrgStructureController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\PesanController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\FlowStepController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TrainingServiceController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TrainingScheduleController;
use App\Http\Controllers\Admin\EmpowermentController;
use App\Http\Controllers\Admin\ProductivityController;
use App\Http\Controllers\Admin\JobVacancyController;
use App\Http\Controllers\Admin\CertificationContentController;
use App\Http\Controllers\Admin\CertificationSchemeController;
use App\Http\Controllers\Admin\PublicationSettingController;
use App\Http\Controllers\Admin\PublicationCategoryController;
use App\Http\Controllers\Admin\PublicationItemController;
use App\Http\Controllers\Admin\InfographicYearController;
use App\Http\Controllers\Admin\InfographicMetricController;
use App\Http\Controllers\Admin\InfographicCardController;
use App\Http\Controllers\Admin\InfographicEmbedController;
use App\Http\Controllers\Admin\PublicServiceSettingController;
use App\Http\Controllers\Admin\PublicServiceFlowController;
use App\Http\Controllers\Admin\FaqSettingController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqItemController;
use App\Http\Controllers\Admin\ContactSettingController;
use App\Http\Controllers\Admin\ContactChannelController;
use App\Http\Controllers\Admin\PpidSettingController;
use App\Http\Controllers\Admin\PpidHighlightController;
use App\Http\Controllers\Admin\PpidRequestController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandingKpiController;
use App\Http\Controllers\Admin\AlumniTracerController;
use App\Http\Controllers\Admin\AlumniForumModerationController;
use App\Http\Controllers\Admin\WeeklyChallengeController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\AlumniForumController;
use App\Http\Controllers\ForumReactionController;
use App\Http\Controllers\InvitationAcceptanceController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\CourseForumReportController;
use App\Http\Controllers\Admin\CourseProgressController;
use App\Http\Controllers\SurveyResponseController;
use App\Http\Controllers\Admin\CourseClassController;
use App\Http\Controllers\Admin\CourseSessionController;
use App\Http\Controllers\Admin\CourseAssignmentController;
use App\Http\Controllers\Admin\CourseAttendanceController;
use App\Http\Controllers\Admin\CourseSubmissionController;
use App\Http\Controllers\Admin\SurveyInstanceController;
use App\Http\Controllers\Admin\CourseEnrollmentController;
use App\Http\Controllers\Admin\CourseEnrollmentImportController;
use App\Http\Controllers\Admin\CourseAnnouncementController;
use App\Http\Controllers\Admin\TalentPoolController;
use App\Http\Controllers\Admin\SchedulePreviewController;
use App\Http\Controllers\Admin\InterviewSessionController;
use App\Http\Controllers\Admin\SkillhubSyncController;
use App\Http\Controllers\Instructor\InstructorScheduleController;

Route::get('/', [HomeController::class, 'index'])->name('home');


Route::get('/profil', function () { return view('profil'); })->name('profil');
Route::get('/program', [HomeController::class, 'katalogPelatihan'])->name('program');
Route::get('/kontak', [HomeController::class, 'kontak'])->name('kontak');
Route::get('/alumni/tracer', [HomeController::class, 'alumniTracerForm'])->name('alumni.tracer');
Route::post('/alumni/tracer', [HomeController::class, 'storeAlumniTracer'])->name('alumni.tracer.store');
Route::get('/alumni/profil', [HomeController::class, 'alumniProfileForm'])->name('alumni.profile.complete');
Route::post('/alumni/profil', [HomeController::class, 'storeAlumniProfile'])->name('alumni.profile.store');

Route::middleware(['auth', 'permission:access-alumni-forum'])->group(function () {
    Route::get('/alumni/forum', [AlumniForumController::class, 'index'])->name('alumni.forum.index');
    Route::post('/alumni/forum', [AlumniForumController::class, 'storeTopic'])
        ->name('alumni.forum.store')
        ->middleware('throttle:4,1');
    Route::get('/alumni/forum/{topic}', [AlumniForumController::class, 'show'])->name('alumni.forum.show');
    Route::post('/alumni/forum/{topic}/posts', [AlumniForumController::class, 'storePost'])
        ->name('alumni.forum.posts.store')
        ->middleware('throttle:8,1');
    Route::post('/alumni/forum/{topic}/react', [ForumReactionController::class, 'reactTopic'])
        ->name('alumni.forum.react')
        ->middleware('throttle:10,1');
    Route::post('/alumni/forum/post/{post}/react', [ForumReactionController::class, 'reactPost'])
        ->name('alumni.forum.post.react')
        ->middleware('throttle:10,1');
});

// Area peserta (tugas & presensi)
Route::middleware(['auth'])->group(function () {
    Route::get('/my/assignments', [\App\Http\Controllers\CourseParticipantController::class, 'assignments'])->name('participant.assignments');
    Route::get('/my/assignments/{assignment}', [\App\Http\Controllers\CourseParticipantController::class, 'showAssignment'])->name('participant.assignments.show');
    Route::post('/my/assignments/{assignment}/submit', [\App\Http\Controllers\CourseParticipantController::class, 'submitAssignment'])->name('participant.assignments.submit');
    Route::get('/my/submissions/{submission}/file', [\App\Http\Controllers\CourseParticipantController::class, 'downloadSubmissionFile'])->name('participant.submissions.file');
    Route::post('/my/sessions/{session}/scan', [\App\Http\Controllers\CourseParticipantController::class, 'scanAttendance'])->name('participant.sessions.scan');
    Route::post('/my/sessions/{session}/attendance', [\App\Http\Controllers\CourseParticipantController::class, 'markAttendance'])->name('participant.sessions.attendance');
    Route::post('/my/consent', [\App\Http\Controllers\CourseParticipantController::class, 'consent'])->name('participant.consent');
    Route::get('/my/classes', [\App\Http\Controllers\CourseParticipantController::class, 'myClasses'])->name('participant.classes');
    Route::get('/my/progress', [\App\Http\Controllers\CourseParticipantController::class, 'myProgress'])->name('participant.progress');
    Route::get('/my/classes/{class}/announcements', [\App\Http\Controllers\CourseParticipantController::class, 'classAnnouncements'])->name('participant.class.announcements.index');
    Route::get('/my/classes/{class}/announcements/{announcement}', [\App\Http\Controllers\CourseParticipantController::class, 'showAnnouncement'])->name('participant.class.announcements.show');
    Route::get('/my/classes/{class}/forum', [\App\Http\Controllers\CourseForumController::class, 'index'])->name('participant.class.forum.index');
    Route::post('/my/classes/{class}/forum', [\App\Http\Controllers\CourseForumController::class, 'storeTopic'])->name('participant.class.forum.store')->middleware('throttle:12,1');
    Route::get('/my/classes/{class}/forum/{topic}', [\App\Http\Controllers\CourseForumController::class, 'show'])->name('participant.class.forum.show');
    Route::post('/my/classes/{class}/forum/{topic}/post', [\App\Http\Controllers\CourseForumController::class, 'storePost'])->name('participant.class.forum.post')->middleware('throttle:20,1');
    Route::post('/my/forum/post/{post}/report', [\App\Http\Controllers\CourseForumController::class, 'reportPost'])->name('participant.class.forum.report')->middleware('throttle:8,1');
    Route::get('/my/interviews', [\App\Http\Controllers\ParticipantInterviewController::class, 'index'])->name('participant.interviews');
});

Route::get('/pengumuman', [HomeController::class, 'pengumumanIndex'])->name('pengumuman.index');
Route::get('/pengumuman/{slug}', [HomeController::class, 'pengumumanShow'])->name('pengumuman.show');

Route::redirect('/berita', '/berita/terkini');
Route::get('/berita/terkini', [HomeController::class, 'beritaTerkini'])->name('berita.terkini');
Route::get('/berita/lowongan', [HomeController::class, 'lowonganKerja'])->name('berita.lowongan');
Route::get('/berita/lowongan/{lowongan}', [HomeController::class, 'lowonganDetail'])->name('berita.lowongan.detail');
Route::get('/berita/{slug}', [HomeController::class, 'showBerita'])->name('berita.show');
Route::prefix('resource')->name('resource.')->group(function () {
    Route::get('/infografis-alumni', [HomeController::class, 'resourceInfografis'])->name('infografis');
    Route::get('/publikasi', [HomeController::class, 'resourcePublikasi'])->name('publikasi');
    Route::get('/pelayanan-publik', [HomeController::class, 'resourcePelayanan'])->name('pelayanan');
    Route::get('/faq', [HomeController::class, 'resourceFaq'])->name('faq');
    Route::get('/hubungi-kami', [HomeController::class, 'resourceHubungi'])->name('hubungi');
});


Route::get('/program/{id}', [HomeController::class, 'showProgram'])->name('program.show');


Route::get('/profil/sejarah', [HomeController::class, 'sejarah'])->name('profil.sejarah');
Route::get('/profil/visi-misi', [HomeController::class, 'visiMisi'])->name('profil.visimisi');
Route::get('/profil/instansi', [HomeController::class, 'profilInstansi'])->name('profil.instansi');
Route::get('/profil/instruktur', [HomeController::class, 'profilInstruktur'])->name('profil.instruktur');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/sso/siapkerja', [\App\Http\Controllers\SiapKerjaSsoController::class, 'redirect'])->name('sso.siapkerja.redirect')->middleware('guest');
Route::get('/sso/siapkerja/callback', [\App\Http\Controllers\SiapKerjaSsoController::class, 'callback'])->name('sso.siapkerja.callback')->middleware('guest');
Route::get('/register', [RegistrationController::class, 'show'])->name('register')->middleware('guest');
Route::post('/register', [RegistrationController::class, 'register'])->name('register.post')->middleware('guest');
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('guest');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('guest');
Route::get('/two-factor', [AuthController::class, 'showTwoFactorForm'])->name('two-factor')->middleware('guest');
Route::post('/two-factor', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify')->middleware('guest');
Route::post('/two-factor/resend', [AuthController::class, 'resendTwoFactorCode'])->name('two-factor.resend')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/invite/{token}', [InvitationAcceptanceController::class, 'show'])->name('invite.show')->middleware('guest');
Route::post('/invite/{token}', [InvitationAcceptanceController::class, 'accept'])->name('invite.accept')->middleware('guest');



Route::prefix('admin')->name('admin.')->middleware(['auth', 'permission:access-admin'])->group(function () {
    
    Route::get('/', App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

    Route::resource('berita', BeritaController::class);
    Route::patch('berita/{berita}/submit', [BeritaController::class, 'submit'])->name('berita.submit');
    Route::patch('berita/{berita}/approve', [BeritaController::class, 'approve'])->name('berita.approve')->middleware('permission:approve-content');
    Route::resource('program', ProgramController::class)->only(['index', 'show', 'edit']); 
    Route::resource('galeri', GaleriController::class);   
    Route::resource('pengumuman', PengumumanController::class);
    Route::patch('pengumuman/{pengumuman}/submit', [PengumumanController::class, 'submit'])->name('pengumuman.submit')->middleware('permission:approve-content');
    Route::patch('pengumuman/{pengumuman}/approve', [PengumumanController::class, 'approve'])->name('pengumuman.approve')->middleware('permission:approve-content');
    Route::resource('struktur', OrgStructureController::class)->except(['show']);
    Route::get('pesan', [PesanController::class, 'index'])->name('pesan.index');
    Route::patch('pesan/{pesan}/status', [PesanController::class, 'updateStatus'])->name('pesan.status');
    Route::resource('partner', PartnerController::class)->except(['show']);
    Route::resource('instructor', InstructorController::class)->except(['show']);
    Route::resource('benefit', BenefitController::class)->except(['show']);
    Route::resource('flow', FlowStepController::class)->except(['show']);
    Route::post('skillhub/sync', SkillhubSyncController::class)->name('skillhub.sync');
    Route::get('alumni-tracer/dashboard', [AlumniTracerController::class, 'dashboard'])->name('alumni-tracer.dashboard')->middleware('permission:manage-users');
    Route::get('alumni-tracer/export', [AlumniTracerController::class, 'export'])->name('alumni-tracer.export')->middleware(['permission:manage-users']);
    Route::resource('alumni-tracer', AlumniTracerController::class)->only(['index','show','destroy'])->middleware('permission:manage-users');
    Route::resource('alumni', AlumniController::class)->except(['show']);
    Route::patch('alumni-tracer/{alumni_tracer}/verify', [AlumniTracerController::class, 'verify'])->name('alumni-tracer.verify')->middleware('permission:manage-users');
    Route::resource('testimonial', TestimonialController::class)->except(['show']);
    Route::resource('training-service', TrainingServiceController::class)->except(['show']);
    Route::resource('training-schedule', TrainingScheduleController::class)->only(['index', 'show', 'edit']);
    Route::resource('empowerment', EmpowermentController::class)->except(['show']);
    Route::resource('productivity', ProductivityController::class)->except(['show']);
    Route::resource('lowongan', JobVacancyController::class)->except(['show']);
    Route::resource('certification-content', CertificationContentController::class)->except(['show']);
    Route::resource('certification-scheme', CertificationSchemeController::class)->except(['show']);
    Route::get('publication/settings', [PublicationSettingController::class, 'edit'])->name('publication.settings');
    Route::put('publication/settings', [PublicationSettingController::class, 'update'])->name('publication.settings.update');
    Route::resource('publication-category', PublicationCategoryController::class)->except(['show']);
    Route::resource('publication-item', PublicationItemController::class)->except(['show']);
    Route::get('public-service/settings', [PublicServiceSettingController::class, 'edit'])->name('public-service.settings');
    Route::put('public-service/settings', [PublicServiceSettingController::class, 'update'])->name('public-service.settings.update');
    Route::resource('public-service-flow', PublicServiceFlowController::class)->except(['show']);
    Route::get('faq/settings', [FaqSettingController::class, 'edit'])->name('faq.settings');
    Route::put('faq/settings', [FaqSettingController::class, 'update'])->name('faq.settings.update');
    Route::resource('faq-category', FaqCategoryController::class)->except(['show']);
    Route::resource('faq-item', FaqItemController::class)->except(['show']);
    Route::get('contact/settings', [ContactSettingController::class, 'edit'])->name('contact.settings');
    Route::put('contact/settings', [ContactSettingController::class, 'update'])->name('contact.settings.update');
    Route::resource('contact-channel', ContactChannelController::class)->except(['show']);
    Route::get('surveys/{survey}/analytics', [AdminSurveyController::class, 'analytics'])->name('surveys.analytics');
    Route::get('surveys/{survey}/export', [AdminSurveyController::class, 'export'])->name('surveys.export');
    Route::get('surveys/{survey}/export-xlsx', [AdminSurveyController::class, 'exportXlsx'])->name('surveys.export-xlsx');
    Route::post('surveys/{survey}/duplicate', [AdminSurveyController::class, 'duplicate'])->name('surveys.duplicate');
    Route::post('surveys/{survey}/versions/{version}/restore', [AdminSurveyController::class, 'restoreVersion'])->name('surveys.restore');
    Route::post('surveys/{survey}/collaborators', [AdminSurveyController::class, 'addCollaborator'])->name('surveys.collaborators.add');
    Route::delete('surveys/{survey}/collaborators/{collaborator}', [AdminSurveyController::class, 'removeCollaborator'])->name('surveys.collaborators.remove');
    Route::get('surveys/answers/{answer}/download', [AdminSurveyController::class, 'downloadAttachment'])
        ->name('surveys.answers.download')
        ->middleware('permission:manage-surveys');
    Route::resource('surveys', AdminSurveyController::class)->except(['show']);
    Route::get('ppid/settings', [PpidSettingController::class, 'edit'])->name('ppid.settings');
    Route::put('ppid/settings', [PpidSettingController::class, 'update'])->name('ppid.settings.update');
    Route::resource('ppid-highlight', PpidHighlightController::class)->except(['show']);
    Route::resource('ppid-request', PpidRequestController::class)->only(['index', 'show', 'destroy']);
    Route::get('ppid-request/{ppid_request}/download', [PpidRequestController::class, 'download'])->name('ppid-request.download');
    Route::resource('infographic-year', InfographicYearController::class)->except(['show']);
    Route::resource('infographic-metric', InfographicMetricController::class)->except(['show']);
    Route::resource('infographic-card', InfographicCardController::class)->except(['show']);
    Route::resource('infographic-embed', InfographicEmbedController::class)->except(['show']);
    Route::resource('survey-instance', SurveyInstanceController::class)->except(['show'])->middleware('permission:manage-surveys');
    Route::get('survey-instance/{survey_instance}/report', [SurveyInstanceController::class, 'report'])->name('survey-instance.report')->middleware('permission:view-class-surveys');
    Route::get('survey-instance/{survey_instance}/export/responses', [SurveyInstanceController::class, 'exportResponses'])->name('survey-instance.export.responses')->middleware('permission:manage-surveys');
    Route::get('survey-instance/{survey_instance}/export/aggregates', [SurveyInstanceController::class, 'exportAggregates'])->name('survey-instance.export.aggregates')->middleware('permission:manage-surveys');
    Route::get('survey-instance-dashboard', [SurveyInstanceController::class, 'dashboard'])->name('survey-instance.dashboard')->middleware('permission:manage-surveys');
    Route::resource('course-class', CourseClassController::class)->except(['show']);
    Route::resource('course-session', CourseSessionController::class);
    Route::resource('course-assignment', CourseAssignmentController::class)->except(['show']);
    Route::get('course-assignment/{course_assignment}/export', [CourseAssignmentController::class, 'exportScores'])->name('course-assignment.export');
    Route::resource('course-attendance', CourseAttendanceController::class)->except(['show']);
    Route::get('course-attendance/export/csv', [CourseAttendanceController::class, 'exportCsv'])->name('course-attendance.export.csv');
    Route::get('course-session/{course_session}/qr', [CourseSessionController::class, 'qr'])->name('course-session.qr');
    Route::get('course-session/{course_session}/cards', [CourseSessionController::class, 'cards'])->name('course-session.cards');
    Route::resource('interview-session', InterviewSessionController::class);
    Route::post('interview-session/{interview_session}/allocations', [InterviewSessionController::class, 'storeAllocation'])->name('interview-session.allocations.store');
    Route::patch('interview-allocations/{allocation}/status', [InterviewSessionController::class, 'updateAllocationStatus'])->name('interview-allocations.status');
    Route::post('interview-allocations/{allocation}/score', [InterviewSessionController::class, 'storeScore'])->name('interview-allocations.score');
    Route::get('ops-dashboard', \App\Http\Controllers\Admin\OpsDashboardController::class)->name('ops-dashboard');
    Route::resource('course-submission', CourseSubmissionController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::get('course-submission/export/csv', [CourseSubmissionController::class, 'exportCsv'])->name('course-submission.export.csv');
    Route::resource('course-enrollment', CourseEnrollmentController::class)->except(['show'])->middleware('permission:manage-enrollment');
    Route::get('course-enrollment-import', [CourseEnrollmentImportController::class, 'create'])->name('course-enrollment.import')->middleware('permission:manage-enrollment');
    Route::post('course-enrollment-import', [CourseEnrollmentImportController::class, 'store'])->name('course-enrollment.import.store')->middleware('permission:manage-enrollment');
    Route::resource('course-announcement', CourseAnnouncementController::class)->except(['show']);
    Route::get('course-forum-reports', [CourseForumReportController::class, 'index'])->name('course-forum-reports.index')->middleware('permission:moderate-class-forum');
    Route::post('course-forum-reports/{course_forum_report}/resolve', [CourseForumReportController::class, 'resolve'])->name('course-forum-reports.resolve')->middleware('permission:moderate-class-forum');
    Route::post('course-forum-reports/{course_forum_report}/delete-post', [CourseForumReportController::class, 'deletePost'])->name('course-forum-reports.delete-post')->middleware('permission:moderate-class-forum');
    Route::post('course-forum-reports/{course_forum_report}/mute', [CourseForumReportController::class, 'mute'])->name('course-forum-reports.mute')->middleware('permission:moderate-class-forum');
    Route::get('course-progress', [CourseProgressController::class, 'index'])->name('course-progress.index');
    Route::resource('profile', ProfileController::class)->only(['index', 'edit', 'update']);
    Route::get('settings/site', [SiteSettingController::class, 'edit'])->name('settings.site');
    Route::put('settings/site', [SiteSettingController::class, 'update'])->name('settings.site.update');
    Route::post('impersonate/{user}', [ImpersonationController::class, 'start'])
        ->name('impersonate.start')
        ->middleware('permission:impersonate-users');
    Route::get('talent-pool', [TalentPoolController::class, 'index'])->name('talent-pool.index');
    Route::get('talent-pool/{course_class}/export', [TalentPoolController::class, 'export'])->name('talent-pool.export');
    Route::get('schedule/preview', SchedulePreviewController::class)->name('schedule.preview');
    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:manage-users');
    Route::resource('invitations', InvitationController::class)->only(['index', 'create', 'store', 'destroy'])->middleware('permission:manage-users');
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('permission:manage-access');
    Route::resource('permissions', PermissionController::class)->except(['show'])->middleware('permission:manage-access');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('permission:manage-audit');
    Route::delete('activity-logs/{activity_log}', [ActivityLogController::class, 'destroy'])->name('activity-logs.destroy')->middleware('permission:manage-audit');
    Route::delete('activity-logs', [ActivityLogController::class, 'clear'])->name('activity-logs.clear')->middleware('permission:manage-audit');
    Route::get('branding-kpi/{branding_kpi}/download', [BrandingKpiController::class, 'download'])->name('branding-kpi.download');
    Route::resource('branding-kpi', BrandingKpiController::class);

    Route::middleware('permission:moderate-alumni-forum')->group(function () {
        Route::get('alumni-forum/moderation', [AlumniForumModerationController::class, 'index'])->name('alumni-forum.moderation');
        Route::patch('alumni-forum/topic/{forum_topic}/approve', [AlumniForumModerationController::class, 'approveTopic'])->name('alumni-forum.topic.approve');
        Route::delete('alumni-forum/topic/{forum_topic}/reject', [AlumniForumModerationController::class, 'rejectTopic'])->name('alumni-forum.topic.reject');
        Route::patch('alumni-forum/post/{forum_post}/approve', [AlumniForumModerationController::class, 'approvePost'])->name('alumni-forum.post.approve');
        Route::delete('alumni-forum/post/{forum_post}/reject', [AlumniForumModerationController::class, 'rejectPost'])->name('alumni-forum.post.reject');
        Route::get('alumni-forum/challenges', [WeeklyChallengeController::class, 'index'])->name('alumni-forum.challenge.index');
        Route::get('alumni-forum/challenges/create', [WeeklyChallengeController::class, 'create'])->name('alumni-forum.challenge.create');
        Route::post('alumni-forum/challenges', [WeeklyChallengeController::class, 'store'])->name('alumni-forum.challenge.store');
        Route::get('alumni-forum/challenges/{weekly_challenge}/edit', [WeeklyChallengeController::class, 'edit'])->name('alumni-forum.challenge.edit');
        Route::put('alumni-forum/challenges/{weekly_challenge}', [WeeklyChallengeController::class, 'update'])->name('alumni-forum.challenge.update');
        Route::delete('alumni-forum/challenges/{weekly_challenge}', [WeeklyChallengeController::class, 'destroy'])->name('alumni-forum.challenge.destroy');
    });
});

Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])
    ->name('impersonate.stop')
    ->middleware('auth');

Route::prefix('instruktur')
    ->name('instructor.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('schedules/{schedule}/preview', [InstructorScheduleController::class, 'preview'])->name('schedules.preview');
        Route::resource('schedules', InstructorScheduleController::class)->parameters(['schedules' => 'schedule'])->except(['show']);
    });

Route::get('/profil/sejarah', [HomeController::class, 'sejarah'])->name('profil.sejarah');
Route::get('/profil/struktur-organisasi', [HomeController::class, 'struktur'])->name('profil.struktur');
Route::get('/kontak', [App\Http\Controllers\HomeController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [App\Http\Controllers\HomeController::class, 'storeKontak'])->name('kontak.store');

Route::get('/pencarian', [HomeController::class, 'search'])->name('search');
Route::get('/pelatihan/katalog', [HomeController::class, 'katalogPelatihan'])->name('pelatihan.katalog');
Route::get('/pelatihan/jadwal', [HomeController::class, 'jadwalPelatihan'])->name('pelatihan.jadwal');
Route::get('/pelatihan/pemberdayaan', [HomeController::class, 'pemberdayaan'])->name('pelatihan.pemberdayaan');
Route::get('/pelatihan/produktivitas', [HomeController::class, 'produktivitas'])->name('pelatihan.produktivitas');
Route::get('/sertifikasi', [HomeController::class, 'sertifikasi'])->name('sertifikasi');
Route::get('/ppid', [HomeController::class, 'ppid'])->name('ppid');
Route::post('/ppid/permohonan', [HomeController::class, 'storePpidRequest'])
    ->name('ppid.store')
    ->middleware('throttle:5,1');
Route::get('/survei/{survey:slug}', [SurveyResponseController::class, 'show'])->name('surveys.show');
Route::post('/survei/{survey:slug}', [SurveyResponseController::class, 'store'])
    ->name('surveys.submit')
    ->middleware('throttle:5,1');
Route::post('/survei/{survey:slug}/draft', [SurveyResponseController::class, 'saveDraft'])
    ->name('surveys.draft')
    ->middleware('throttle:20,1');
Route::get('/survei/embed/{token}', [SurveyResponseController::class, 'embed'])->name('surveys.embed');
// Profile admin routes now under admin prefix
