<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:student,parent,teacher,admin',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'student',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'role' => 'required|string|in:admin,teacher,student,parent',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            ]);
        }

        if ($user->role !== $request->role) {
            throw ValidationException::withMessages([
                'email' => ['هذا الحساب غير مسجل كـ ' . ($request->role === 'teacher' ? 'معلم' : ($request->role === 'student' ? 'طالب' : ($request->role === 'parent' ? 'ولي أمر' : 'مدير')))],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        $relations = [];
        if ($user->isStudent()) $relations[] = 'student.class';
        if ($user->isTeacher()) $relations[] = 'teacher.assignedClasses';
        if ($user->isParent()) $relations[] = 'parent.children.user';

        if (!empty($relations)) {
            $user->load($relations);
        }

        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:6',
            'avatar' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($user->role === 'teacher') {
            $rules['specialization'] = 'sometimes|string|max:255';
            $rules['qualification'] = 'sometimes|string|max:255';
        }
        if ($user->role === 'student') {
            $rules['dob'] = 'sometimes|date';
            $rules['address'] = 'sometimes|string|max:500';
            $rules['guardian_phone'] = 'sometimes|string|max:20';
        }
        if ($user->role === 'parent') {
            $rules['occupation'] = 'sometimes|string|max:255';
            $rules['address'] = 'sometimes|string|max:500';
        }

        $validated = $request->validate($rules);

        // Password and avatar are saved directly (security + file handling)
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
            $user->update(['password' => $validated['password']]);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $avatarPath]);
        }

        if ($request->input('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => null]);
        }

        // Admin saves directly
        if ($user->role === 'admin') {
            $user->update(collect($validated)->except(['password', 'avatar'])->toArray());

            return response()->json($user->fresh()->load(['student', 'teacher', 'parent']));
        }

        // Parent: save occupation/address directly, request approval for name/email/phone
        if ($user->role === 'parent') {
            $directFields = array_intersect_key($validated, array_flip(['occupation', 'address']));
            $requestCandidates = array_intersect_key($validated, array_flip(['name', 'email', 'phone']));

            // Only keep fields that actually changed
            $requestFields = array_filter($requestCandidates, function ($v, $k) use ($user) {
                if ($k === 'name') return $v !== $user->name;
                if ($k === 'email') return $v !== $user->email;
                if ($k === 'phone') return $v !== $user->phone;
                return false;
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($directFields) && $user->parent) {
                $user->parent->update($directFields);
            }

            if (!empty($requestFields)) {
                $changeRequest = ProfileChangeRequest::create([
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'changes' => $requestFields,
                    'status' => 'pending',
                ]);

                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'profile_change',
                        'title' => 'طلب تعديل بيانات',
                        'message' => "ولي أمر {$user->name} طلب تعديل بياناته. #{$changeRequest->id}",
                    ]);
                }

                return response()->json([
                    'message' => 'تم حفظ المهنة والعنوان، وإرسال طلب تعديل الاسم/البريد/الجوال للمدير',
                    'request_id' => $changeRequest->id,
                    'status' => 'pending',
                    'user' => $user->fresh()->load(['parent.children']),
                ]);
            }

            // Load user data to return consistent response
            $freshUser = $user->fresh()->load(['parent.children']);
            if (!empty($directFields)) {
                return response()->json([
                    'message' => 'تم الحفظ',
                    'user' => $freshUser,
                ]);
            }

            return response()->json($freshUser);
        }

        // Teacher/student: all text changes need approval — only if actually changed
        $textCandidates = collect($validated)->except(['password', 'avatar'])->filter(fn($v) => $v !== null)->toArray();

        $textChanges = array_filter($textCandidates, function ($v, $k) use ($user) {
            if ($user->teacher && in_array($k, ['qualification', 'specialization'])) {
                return $v !== $user->teacher->$k;
            }
            if ($user->student && in_array($k, ['dob', 'address', 'guardian_phone'])) {
                return $v !== $user->student->$k;
            }
            return ($user->$k ?? null) !== $v;
        }, ARRAY_FILTER_USE_BOTH);

        if (empty($textChanges)) {
            return response()->json($user->fresh()->load(['student', 'teacher', 'parent']));
        }

        if (!empty($textChanges)) {
            $changeRequest = ProfileChangeRequest::create([
                'user_id' => $user->id,
                'role' => $user->role,
                'changes' => $textChanges,
                'status' => 'pending',
            ]);

            // Notify all admins
            $admins = User::where('role', 'admin')->get();
            $roleLabel = $user->role === 'teacher' ? 'معلم' : ($user->role === 'parent' ? 'ولي أمر' : 'طالب');
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'profile_change',
                    'title' => 'طلب تعديل بيانات',
                    'message' => "{$roleLabel} {$user->name} طلب تعديل بياناته. #{$changeRequest->id}",
                ]);
            }
        }

        return response()->json([
            'message' => 'تم إرسال طلب التعديل للمدير للموافقة',
            'request_id' => $changeRequest->id ?? null,
            'status' => 'pending',
            'user' => $user->fresh()->load(['student', 'teacher', 'parent']),
        ]);
    }
}
