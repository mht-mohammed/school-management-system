<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with(['student.class', 'teacher', 'parent'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,teacher,student,parent',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ]);

        if ($request->role === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
                'qualification' => $request->qualification,
                'specialization' => $request->specialization,
                'hire_date' => $request->hire_date ?? now(),
                'salary' => $request->salary,
            ]);
        }

        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'dob' => $request->dob,
                'address' => $request->address,
                'guardian_phone' => $request->guardian_phone,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }

        if ($request->role === 'parent') {
            ParentModel::create([
                'user_id' => $user->id,
                'occupation' => $request->occupation,
            ]);
        }

        return response()->json(User::with(['teacher', 'student.class', 'parent'])->find($user->id), 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:admin,teacher,student,parent',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load(['student.class', 'teacher', 'parent']));
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'لا يمكن حذف آخر أدمن'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'تم حذف المستخدم']);
    }

    public function teachers()
    {
        return response()->json(
            User::where('role', 'teacher')
                ->whereHas('teacher')
                ->with(['teacher.assignedClasses'])
                ->get()
                ->map(function ($u) {
                    $classIds = $u->teacher->assignedClasses->pluck('id')->toArray();
                    $gradeLevelIds = $u->teacher->assignedClasses->pluck('grade_level_id')->unique()->values()->toArray();
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'specialization' => $u->teacher->specialization,
                        'class_ids' => $classIds,
                        'grade_level_ids' => $gradeLevelIds,
                    ];
                })
        );
    }
}
