<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ProfileChangeRequest;
use Illuminate\Http\Request;

class ProfileRequestController extends Controller
{
    public function index()
    {
        return ProfileChangeRequest::with('user')
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->latest()
            ->paginate(50);
    }

    public function approve(ProfileChangeRequest $profile_change_request)
    {
        if ($profile_change_request->status !== 'pending') {
            return response()->json(['message' => 'الطلب تم معالجته مسبقاً'], 400);
        }

        $user = $profile_change_request->user;

        if (!$user) {
            $profile_change_request->update([
                'status' => 'rejected',
                'admin_note' => 'المستخدم غير موجود',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            return response()->json(['message' => 'المستخدم غير موجود، تم رفض الطلب'], 400);
        }
        $changes = $profile_change_request->changes;

        $userData = array_intersect_key($changes, array_flip(['name', 'email', 'phone']));
        if (!empty($userData)) {
            $user->update($userData);
        }

        // Handle avatar if changed
        if (!empty($changes['avatar'])) {
            $user->update(['avatar' => $changes['avatar']]);
        }

        if ($profile_change_request->role === 'teacher' && $user->teacher) {
            $teacherData = array_intersect_key($changes, array_flip(['specialization', 'qualification']));
            if (!empty($teacherData)) {
                $user->teacher->update($teacherData);
            }
        }

        if ($profile_change_request->role === 'parent' && $user->parent) {
            $parentData = array_intersect_key($changes, array_flip(['occupation', 'address']));
            if (!empty($parentData)) {
                $user->parent->update($parentData);
            }
        }

        if ($profile_change_request->role === 'student' && $user->student) {
            $studentData = array_intersect_key($changes, array_flip(['dob', 'address', 'guardian_phone']));
            if (!empty($studentData)) {
                $user->student->update($studentData);
            }
        }

        $profile_change_request->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'profile_change_approved',
            'title' => 'تم الموافقة على طلب التعديل',
            'message' => 'تمت الموافقة على طلب تعديل بيانات ملفك الشخصي من قبل الإدارة.',
        ]);

        return response()->json(['message' => 'تمت الموافقة على الطلب وتحديث البيانات']);
    }

    public function reject(Request $httpRequest, ProfileChangeRequest $profile_change_request)
    {
        if ($profile_change_request->status !== 'pending') {
            return response()->json(['message' => 'الطلب تم معالجته مسبقاً'], 400);
        }

        $note = $httpRequest->input('admin_note', 'لم يتم تقديم سبب');

        $profile_change_request->update([
            'status' => 'rejected',
            'admin_note' => $note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $profile_change_request->user_id,
            'type' => 'profile_change_rejected',
            'title' => 'تم رفض طلب التعديل',
            'message' => "تم رفض طلب تعديل بيانات ملفك الشخصي. السبب: {$note}",
        ]);

        return response()->json(['message' => 'تم رفض الطلب']);
    }
}
