<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            // بيانات ولي الأمر (مقدم الطلب)
            'guardian_name' => 'required|string|max:255',
            'guardian_email' => 'required|email|max:255',
            'guardian_phone' => 'required|string|max:20',
            // بيانات الطالب
            'student_name' => 'required|string|max:255',
            'student_dob' => 'required|date',
            'stage' => 'nullable|string',
        ]);

        // منع تكرار طلب لنفس الابن من نفس ولي الأمر
        $existing = Enrollment::where('guardian_email', $validated['guardian_email'])
            ->where('guardian_phone', $validated['guardian_phone'])
            ->where('student_name', $validated['student_name'])
            ->where('dob', $validated['student_dob'])
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        if ($existing) {
            return response()->json([
                'message' => '⚠️ لديك طلب مسبق لهذا الابن بنفس البيانات. يمكنك التقديم لأبناء آخرين.'
            ], 422);
        }

        $maxNum = Enrollment::max('enrollment_number') ?? 0;

        $enrollment = Enrollment::create([
            'enrollment_number' => $maxNum + 1,
            'guardian_name' => $validated['guardian_name'],
            'guardian_email' => $validated['guardian_email'],
            'guardian_phone' => $validated['guardian_phone'],
            'student_name' => $validated['student_name'],
            'dob' => $validated['student_dob'],
            'stage' => $validated['stage'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب التحاق ابنك بنجاح. سيتم التواصل معك عند الموافقة.',
            'enrollment' => $enrollment,
        ], 201);
    }

    public function index()
    {
        return response()->json(Enrollment::with('user')->latest()->get());
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $enrollment->update($validated);

        if ($validated['status'] === 'approved' && !$enrollment->user_id) {
            // --- 1. إنشاء حساب ولي الأمر (أو استخدام الموجود) ---
            // البحث بالبريد الإلكتروني أو رقم الجوال (لنفس ولي الأمر)
            $guardianUser = User::where('role', 'parent')
                ->where(function ($q) use ($enrollment) {
                    $q->where('email', $enrollment->guardian_email)
                      ->orWhere('phone', $enrollment->guardian_phone);
                })
                ->first();
            $isNewGuardian = false;

            if (!$guardianUser) {
                $guardianPassword = $enrollment->guardian_phone;
                $guardianUser = User::create([
                    'name' => $enrollment->guardian_name,
                    'email' => $enrollment->guardian_email,
                    'password' => Hash::make($guardianPassword),
                    'phone' => $enrollment->guardian_phone,
                    'role' => 'parent',
                ]);
                $isNewGuardian = true;
            }

            $parent = ParentModel::firstOrCreate(
                ['user_id' => $guardianUser->id],
                ['phone' => $enrollment->guardian_phone ?? $guardianUser->phone]
            );

            // --- 2. إنشاء حساب الطالب ---
            $studentPassword = $enrollment->guardian_phone;
            $childCount = Student::where('guardian_phone', $enrollment->guardian_phone)->count();
            $studentEmail = 'child' . ($childCount + 1) . '_' . $enrollment->guardian_phone . '@alebdaa.edu';

            $studentUser = User::create([
                'name' => $enrollment->student_name,
                'email' => $studentEmail,
                'password' => Hash::make($studentPassword),
                'phone' => $enrollment->guardian_phone,
                'role' => 'student',
            ]);

            // --- 3. تعيين الطالب لأول شعبة متاحة تناسب مرحلته ---
            $classId = null;
            if ($enrollment->stage) {
                $gradeLevel = \App\Models\GradeLevel::where('name', 'like', '%' . $enrollment->stage . '%')->orWhere('stage', 'like', '%' . $enrollment->stage . '%')->first();
                if (!$gradeLevel) {
                    $gradeLevel = \App\Models\GradeLevel::where('name', $enrollment->stage)->orWhere('id', $enrollment->stage)->first();
                }
                if ($gradeLevel) {
                    $firstSection = \App\Models\Section::where('grade_level_id', $gradeLevel->id)->orderBy('id')->first();
                    if ($firstSection) $classId = $firstSection->id;
                }
            }

            Student::create([
                'user_id' => $studentUser->id,
                'parent_id' => $parent->id,
                'class_id' => $classId,
                'guardian_phone' => $enrollment->guardian_phone,
                'dob' => $enrollment->dob,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);

            $enrollment->update(['user_id' => $studentUser->id]);

            return response()->json([
                'message' => '✅ تم قبول الطلب!',
                'enrollment' => $enrollment->fresh()->load('user'),
                'guardian' => [
                    'email' => $guardianUser->email,
                    'password' => $isNewGuardian ? $guardianPassword : '— (حساب موجود مسبقاً)',
                    'is_new' => $isNewGuardian,
                ],
                'student_user' => [
                    'name' => $studentUser->name,
                    'email' => $studentUser->email,
                    'password' => $studentPassword,
                ],
            ]);
        }

        return response()->json($enrollment);
    }
}
