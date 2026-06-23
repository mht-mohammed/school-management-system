<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Notification::where('user_id', $request->user()->id)
                ->latest()
                ->take(50)
                ->get()
        );
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => Notification::where('user_id', $request->user()->id)
                ->unread()
                ->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
        $notification->update(['is_read' => true]);
        return response()->json($notification);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        return response()->json(['message' => 'تم تحديد الكل كمقروء']);
    }

    // دالة مساعدة لإنشاء إشعار (تُستدعى من الكونترولرات الأخرى)
    public static function createAlert($userId, $type, $title, $message)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    // إنشاء إشعار لولي الأمر عند إضافة درجة
    public static function notifyParentOnGrade($studentId, $score, $subjectName)
    {
        $student = \App\Models\Student::with('parent.user')->find($studentId);
        if (!$student || !$student->parent || !$student->parent->user) return;

        $parentUser = $student->parent->user;
        $title = $score < 50 ? '⚡ تنبيه: درجة متدنية' : '📝 درجة جديدة';
        $message = "ابنك {$student->user->name} حصل على {$score} في {$subjectName}" . ($score < 50 ? ' — يرجى المتابعة' : '');

        self::createAlert($parentUser->id, $score < 50 ? 'grade_alert' : 'grade', $title, $message);
    }

    // إنشاء إشعار لولي الأمر عند تسجيل غياب
    public static function notifyParentOnAbsent($studentId, $date, $className)
    {
        $student = \App\Models\Student::with('parent.user')->find($studentId);
        if (!$student || !$student->parent || !$student->parent->user) return;

        $parentUser = $student->parent->user;

        // تحقق من الغياب المتكرر (3 أيام متتالية)
        $recentAbsent = Attendance::where('student_id', $studentId)
            ->where('status', 'absent')
            ->where('date', '>=', now()->subDays(5))
            ->count();

        $title = $recentAbsent >= 3 ? '⚠️ إنذار: غياب متكرر' : '📋 تسجيل غياب';
        $message = "ابنك {$student->user->name} غائب عن {$className} بتاريخ {$date}";
        if ($recentAbsent >= 3) {
            $message .= " — هذا الغياب رقم {$recentAbsent} خلال 5 أيام";
        }

        self::createAlert($parentUser->id, 'attendance_alert', $title, $message);
    }
}
