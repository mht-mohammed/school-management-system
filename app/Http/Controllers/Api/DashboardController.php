<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\ContactMessage;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'students_count' => Student::count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'classes_count' => SchoolClass::count(),
            'subjects_count' => Subject::count(),
            'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
            'unread_messages' => ContactMessage::where('is_read', false)->count(),
            'total_enrollments' => Enrollment::count(),
            'total_messages' => ContactMessage::count(),
            'active_students' => Student::where('status', 'active')->count(),
        ]);
    }
}
