<?php

use Illuminate\Support\Facades\Route;

// Dashboard routes
Route::get('/admin', fn() => view('dashboard.admin.index'));
Route::get('/admin/enrollments', fn() => view('dashboard.admin.enrollments'));
Route::get('/admin/messages', fn() => view('dashboard.admin.messages'));
Route::get('/admin/students', fn() => view('dashboard.admin.students'));
Route::get('/admin/teachers', fn() => view('dashboard.admin.teachers'));
Route::get('/admin/classes', fn() => view('dashboard.admin.classes'));
Route::get('/admin/subjects', fn() => view('dashboard.admin.subjects'));
Route::get('/admin/schedules', function () {
    return view('dashboard.admin.schedules', [
        'gradeLevels' => \App\Models\GradeLevel::orderBy('name')->get(),
        'sections' => \App\Models\Section::with('gradeLevel')->get(),
        'subjects' => \App\Models\Subject::all(),
    ]);
});
Route::get('/admin/parents', fn() => view('dashboard.admin.parents'));
Route::get('/admin/grades-report', fn() => view('dashboard.admin.grades-report'));
Route::get('/admin/attendance-report', fn() => view('dashboard.admin.attendance-report'));
Route::get('/admin/profile-requests', fn() => view('dashboard.admin.profile-requests'));
Route::get('/admin/settings', fn() => view('dashboard.admin.settings'));
Route::get('/admin/e-learning', fn() => view('dashboard.admin.e-learning'));
Route::get('/admin/library', fn() => view('dashboard.admin.library'));

Route::get('/teacher', fn() => view('dashboard.teacher.index'));
Route::get('/teacher/grades', fn() => view('dashboard.teacher.grades'));
Route::get('/teacher/schedule', fn() => view('dashboard.teacher.schedule'));
Route::get('/teacher/e-learning', fn() => view('dashboard.teacher.e-learning'));
Route::get('/teacher/library', fn() => view('dashboard.teacher.library'));

Route::get('/student', fn() => view('dashboard.student.index'));
Route::get('/student/e-learning', fn() => view('dashboard.student.e-learning'));
Route::get('/student/library', fn() => view('dashboard.student.library'));
Route::get('/parent', fn() => view('dashboard.parent.index'));
Route::get('/profile', fn() => view('dashboard.profile'));

// Block old /admin/users route
Route::redirect('/admin/users', '/admin/parents', 301);

// Serve the SPA for all non-API, non-asset routes (including root /)
Route::get('/', fn() => response()->file(public_path('index.html')));
Route::get('/{any}', fn() => response()->file(public_path('index.html')))
    ->where('any', '.*');
