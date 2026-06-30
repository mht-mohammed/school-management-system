<?php

use App\Http\Controllers\Api\AdminParentController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TeacherImportController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileRequestController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/enrollments', [EnrollmentController::class, 'store']);
Route::post('/contact', [ContactController::class, 'store']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::match(['put', 'post'], '/user/profile', [AuthController::class, 'updateProfile']);

    // Student routes
    Route::get('/student/grades', [StudentController::class, 'grades']);
    Route::get('/student/attendance', [StudentController::class, 'attendance']);
    Route::get('/student/schedule', [StudentController::class, 'schedule']);
    Route::get('/student/average', [StudentController::class, 'average']);

    // Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/classes', [TeacherController::class, 'classes']);
        Route::get('/schedule', [TeacherController::class, 'schedule']);
        Route::get('/stats', [TeacherController::class, 'stats']);
        Route::get('/classes/{class}/students', [TeacherController::class, 'studentsByClass']);
        Route::get('/classes/{class}/sections', [TeacherController::class, 'classSections']);
        Route::get('/grades/template', [TeacherController::class, 'downloadGradeTemplate']);
        Route::get('/grades/distribution', [TeacherController::class, 'getGradeDistribution']);
        Route::put('/grades/distribution', [TeacherController::class, 'saveGradeDistribution']);
        Route::get('/grades', [TeacherController::class, 'getGrades']);
        Route::get('/grades/sections', [TeacherController::class, 'sectionsWithGrades']);
        Route::post('/grades/bulk', [TeacherController::class, 'storeGradesBulk']);
        Route::post('/import/grades', [TeacherImportController::class, 'importGrades']);
    });

    // Teacher grading routes (legacy single-entry endpoints)
    Route::middleware('role:teacher')->group(function () {
        Route::post('/grades', [GradeController::class, 'store']);
        Route::put('/grades/{grade}', [GradeController::class, 'update']);
    });

    // E-Learning - Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/elearning/sections', [\App\Http\Controllers\Api\ELearningController::class, 'teacherSections']);
        Route::get('/elearning/{sectionId}/materials', [\App\Http\Controllers\Api\ELearningController::class, 'materials']);
        Route::post('/elearning/{sectionId}/materials', [\App\Http\Controllers\Api\ELearningController::class, 'storeMaterial']);
        Route::put('/elearning/materials/{material}', [\App\Http\Controllers\Api\ELearningController::class, 'updateMaterial']);
        Route::delete('/elearning/materials/{material}', [\App\Http\Controllers\Api\ELearningController::class, 'destroyMaterial']);
        Route::get('/elearning/{sectionId}/quizzes', [\App\Http\Controllers\Api\ELearningController::class, 'quizzes']);
        Route::post('/elearning/{sectionId}/quizzes', [\App\Http\Controllers\Api\ELearningController::class, 'storeQuiz']);
        Route::put('/elearning/quizzes/{quiz}', [\App\Http\Controllers\Api\ELearningController::class, 'updateQuiz']);
        Route::delete('/elearning/quizzes/{quiz}', [\App\Http\Controllers\Api\ELearningController::class, 'destroyQuiz']);
        Route::post('/elearning/quizzes/{quiz}/questions', [\App\Http\Controllers\Api\ELearningController::class, 'storeQuestion']);
        Route::delete('/elearning/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\Api\ELearningController::class, 'destroyQuestion']);
        Route::get('/elearning/{sectionId}/sessions', [\App\Http\Controllers\Api\ELearningController::class, 'sessions']);
        Route::post('/elearning/{sectionId}/sessions', [\App\Http\Controllers\Api\ELearningController::class, 'storeSession']);
        Route::put('/elearning/sessions/{session}', [\App\Http\Controllers\Api\ELearningController::class, 'updateSession']);
        Route::delete('/elearning/sessions/{session}', [\App\Http\Controllers\Api\ELearningController::class, 'destroySession']);
        Route::get('/elearning/quizzes/{quizId}/attempts', [\App\Http\Controllers\Api\ELearningController::class, 'quizAttempts']);
        Route::post('/elearning/attempts/{attemptId}/grade', [\App\Http\Controllers\Api\ELearningController::class, 'gradeAttempt']);
        Route::post('/elearning/attempts/{attemptId}/toggle-visibility', [\App\Http\Controllers\Api\ELearningController::class, 'toggleAttemptVisibility']);
        Route::post('/elearning/attempts/{attemptId}/set-best', [\App\Http\Controllers\Api\ELearningController::class, 'setBestAttempt']);
        Route::delete('/elearning/attempts/{attemptId}', [\App\Http\Controllers\Api\ELearningController::class, 'deleteAttempt']);
    });

    // E-Learning - Student routes
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/elearning/sections', [\App\Http\Controllers\Api\ELearningController::class, 'studentSections']);
        Route::get('/elearning/{sectionId}/content', [\App\Http\Controllers\Api\ELearningController::class, 'studentSectionContent']);
        Route::post('/elearning/quizzes/{quiz}/start', [\App\Http\Controllers\Api\ELearningController::class, 'startQuiz']);
        Route::post('/elearning/quizzes/{quiz}/attempts/{attempt}/submit', [\App\Http\Controllers\Api\ELearningController::class, 'submitQuiz']);
        Route::get('/elearning/attempts/{attemptId}', [\App\Http\Controllers\Api\ELearningController::class, 'studentAttempt']);
    });

    // Library - Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/library', [\App\Http\Controllers\Api\LibraryController::class, 'teacherBooks']);
        Route::post('/library', [\App\Http\Controllers\Api\LibraryController::class, 'teacherStore']);
        Route::put('/library/{book}', [\App\Http\Controllers\Api\LibraryController::class, 'teacherUpdate']);
        Route::delete('/library/{book}', [\App\Http\Controllers\Api\LibraryController::class, 'teacherDestroy']);
    });

    // Library - Student routes
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/library', [\App\Http\Controllers\Api\LibraryController::class, 'studentBooks']);
    });

    // Parent routes
    Route::middleware('role:parent')->prefix('parent')->group(function () {
        Route::get('/children', [ParentController::class, 'children']);
        Route::get('/child/{student}/grades', [ParentController::class, 'childGrades']);
        Route::get('/child/{student}/attendance', [ParentController::class, 'childAttendance']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // Template downloads
    Route::get('/templates/students', [TemplateController::class, 'students']);
    Route::get('/templates/grades', [TemplateController::class, 'grades']);
    Route::get('/templates/attendance', [TemplateController::class, 'attendance']);

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'stats']);
        Route::get('/enrollments', [EnrollmentController::class, 'index']);
        Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
        Route::get('/contact-messages', [ContactController::class, 'index']);
        Route::get('/contact-messages/{contactMessage}', [ContactController::class, 'show']);
        Route::delete('/contact-messages/{contactMessage}', [ContactController::class, 'destroy']);
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        Route::put('/students/{student}', [StudentController::class, 'update']);
        Route::delete('/students/{student}', [StudentController::class, 'destroy']);

        // Teacher management (admin)
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::put('/teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
        Route::get('/teachers/{teacher}/classes', [TeacherController::class, 'getAssignedClasses']);
        Route::put('/teachers/{teacher}/classes', [TeacherController::class, 'assignClasses']);
        Route::get('/teachers-by-class/{class}', [TeacherController::class, 'byClass']);

        // Admin CRUD
        Route::apiResource('/classes', ClassController::class)->except(['create', 'edit']);
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);
        // Schedule routes - grid/generate MUST come before apiResource
        Route::post('/schedules/generate/{section}', [ScheduleController::class, 'generate']);
        Route::post('/schedules/bulk-delete-by-sections', [ScheduleController::class, 'bulkDeleteBySections']);
        Route::get('/schedules-grid', [ScheduleController::class, 'grid']);
        Route::post('/schedules/generate-from-teachers', [ScheduleController::class, 'generateFromTeachers']);
        Route::apiResource('/schedules', ScheduleController::class)->except(['create', 'edit']);
        Route::apiResource('/sections', SectionController::class)->except(['create', 'edit']);
        Route::apiResource('/grade-levels', \App\Http\Controllers\Api\GradeLevelController::class)->except(['create', 'edit']);
        Route::post('/grade-levels/{gradeLevel}/distribute-students', [\App\Http\Controllers\Api\GradeLevelController::class, 'distributeStudents']);
        Route::get('/grades-report', [GradeController::class, 'report']);
        Route::get('/attendance-report', [AttendanceController::class, 'report']);
        Route::get('/attendance/template', [AttendanceController::class, 'downloadMonthlyTemplate']);
        Route::post('/attendance/import', [AttendanceController::class, 'importMonthly']);

        // Admin user management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
        Route::get('/teachers-list', [AdminUserController::class, 'teachers']);

        // Admin parents management
        Route::get('/parents', [AdminParentController::class, 'index']);
        Route::post('/parents', [AdminParentController::class, 'store']);
        Route::put('/parents/{id}', [AdminParentController::class, 'update']);
        Route::delete('/parents/{id}', [AdminParentController::class, 'destroy']);

        // Profile change requests
        Route::get('/profile-requests', [ProfileRequestController::class, 'index']);
        Route::post('/profile-requests/{profile_change_request}/approve', [ProfileRequestController::class, 'approve']);
        Route::post('/profile-requests/{profile_change_request}/reject', [ProfileRequestController::class, 'reject']);

        // School settings
        Route::get('/settings', [SettingsController::class, 'show']);
        Route::put('/settings', [SettingsController::class, 'update']);

        // Admin E-Learning
        Route::get('/elearning/dashboard', [\App\Http\Controllers\Api\AdminELearningController::class, 'dashboard']);
        Route::get('/elearning/sections', [\App\Http\Controllers\Api\AdminELearningController::class, 'sections']);
        Route::get('/elearning/{sectionId}/materials', [\App\Http\Controllers\Api\AdminELearningController::class, 'materials']);
        Route::post('/elearning/{sectionId}/materials', [\App\Http\Controllers\Api\AdminELearningController::class, 'storeMaterial']);
        Route::put('/elearning/materials/{material}', [\App\Http\Controllers\Api\AdminELearningController::class, 'updateMaterial']);
        Route::delete('/elearning/materials/{material}', [\App\Http\Controllers\Api\AdminELearningController::class, 'destroyMaterial']);
        Route::get('/elearning/{sectionId}/quizzes', [\App\Http\Controllers\Api\AdminELearningController::class, 'quizzes']);
        Route::post('/elearning/{sectionId}/quizzes', [\App\Http\Controllers\Api\AdminELearningController::class, 'storeQuiz']);
        Route::put('/elearning/quizzes/{quiz}', [\App\Http\Controllers\Api\AdminELearningController::class, 'updateQuiz']);
        Route::delete('/elearning/quizzes/{quiz}', [\App\Http\Controllers\Api\AdminELearningController::class, 'destroyQuiz']);
        Route::get('/elearning/quizzes/{quiz}/questions', [\App\Http\Controllers\Api\AdminELearningController::class, 'questions']);
        Route::post('/elearning/quizzes/{quiz}/questions', [\App\Http\Controllers\Api\AdminELearningController::class, 'storeQuestion']);
        Route::delete('/elearning/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\Api\AdminELearningController::class, 'destroyQuestion']);
        Route::get('/elearning/quizzes/{quiz}/attempts', [\App\Http\Controllers\Api\AdminELearningController::class, 'quizAttempts']);
        Route::post('/elearning/attempts/{attempt}/grade', [\App\Http\Controllers\Api\AdminELearningController::class, 'gradeAttempt']);
        Route::post('/elearning/attempts/{attempt}/set-best', [\App\Http\Controllers\Api\AdminELearningController::class, 'setBestAttempt']);
        Route::post('/elearning/attempts/{attempt}/toggle-visibility', [\App\Http\Controllers\Api\AdminELearningController::class, 'toggleVisibility']);
        Route::delete('/elearning/attempts/{attempt}', [\App\Http\Controllers\Api\AdminELearningController::class, 'deleteAttempt']);
        Route::get('/elearning/{sectionId}/sessions', [\App\Http\Controllers\Api\AdminELearningController::class, 'sessions']);
        Route::post('/elearning/{sectionId}/sessions', [\App\Http\Controllers\Api\AdminELearningController::class, 'storeSession']);
        Route::put('/elearning/sessions/{session}', [\App\Http\Controllers\Api\AdminELearningController::class, 'updateSession']);
        Route::delete('/elearning/sessions/{session}', [\App\Http\Controllers\Api\AdminELearningController::class, 'destroySession']);
        Route::get('/library', [\App\Http\Controllers\Api\LibraryController::class, 'adminBooks']);
        Route::delete('/library/{book}', [\App\Http\Controllers\Api\LibraryController::class, 'adminDestroy']);
    });
});
