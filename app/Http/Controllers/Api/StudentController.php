<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'class', 'section', 'parent.user']);

        if (request('search')) {
            $search = request('search');
            $students->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )->orWhere('guardian_phone', 'like', "%{$search}%");
        }

        if (request('grade_level_id')) {
            $sectionIds = \App\Models\Section::where('grade_level_id', request('grade_level_id'))->pluck('id');
            $students->whereIn('class_id', $sectionIds);
        }

        if (request('section_id')) {
            $students->where('class_id', request('section_id'));
        }

        if (request('per_page')) {
            return response()->json($students->paginate(request('per_page')));
        }

        return response()->json($students->get());
    }

    public function show(Student $student)
    {
        return response()->json($student->load(['user', 'class', 'section', 'parent.user', 'grades.subject']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6|max:255',
            'dob' => 'nullable|date',
            'guardian_phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password'] ?? '12345678'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'dob' => $validated['dob'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'status' => 'active',
        ]);

        return response()->json($student->load('user'), 201);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $student->user_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|max:255',
            'class_id' => 'nullable|exists:school_classes,id',
            'section_id' => 'nullable|exists:school_classes,id',
            'parent_id' => 'nullable|exists:parents,id',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'guardian_phone' => 'nullable|string|max:20',
            'enrollment_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'avatar' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $student->user;

        $userData = [];
        if (isset($validated['name'])) $userData['name'] = $validated['name'];
        if (isset($validated['email'])) $userData['email'] = $validated['email'];
        if (isset($validated['phone'])) $userData['phone'] = $validated['phone'];
        if (!empty($validated['password'])) $userData['password'] = bcrypt($validated['password']);
        if (!empty($userData)) {
            $user->update($userData);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);
        }

        if ($request->input('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => null]);
        }

        $student->update($validated);

        return response()->json($student->load(['user', 'class', 'section']));
    }

    public function destroy(Student $student)
    {
        $userId = $student->user_id;

        DB::transaction(function () use ($student, $userId) {
            $student->grades()->delete();
            $student->attendance()->delete();

            $parent = $student->parent;
            if ($parent && $parent->children()->count() <= 1) {
                $parentUser = $parent->user;
                $parent->delete();
                if ($parentUser) $parentUser->delete();
            }

            $student->delete();
            User::where('id', $userId)->delete();
        });

        return response()->json(['message' => 'تم حذف الطالب']);
    }

    public function grades(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return response()->json([]);
        }

        return response()->json(
            $student->grades()->with('subject')->latest()->get()
        );
    }

    public function attendance(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return response()->json([]);
        }

        return response()->json(
            $student->attendance()->with('class')->latest()->get()
        );
    }

    public function schedule(Request $request)
    {
        $student = $request->user()->student;

        if (!$student || !$student->class) {
            return response()->json([]);
        }

        return response()->json(
            $student->class->schedules()->with('subject', 'teacher')->orderBy('day_of_week')->orderBy('period_number')->get()
        );
    }

    public function average(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return response()->json(['average' => null, 'subjects' => []]);
        }

        $grades = $student->grades()->with('subject')->get();
        $bySubject = $grades->groupBy('subject_id');
        $subjects = [];
        $total = 0;
        $count = 0;

        foreach ($bySubject as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject;

            $finalGrade = $subjectGrades->firstWhere('exam_type', 'الدرجة النهائية');
            $score = $finalGrade ? round((float)$finalGrade->score, 1) : round($subjectGrades->avg('score'), 1);

            $subjects[] = [
                'name' => $subject->name ?? 'مادة',
                'average' => $score,
                'count' => $subjectGrades->count(),
                'is_final' => $finalGrade ? true : false,
            ];

            $total += $score;
            $count++;
        }

        $user = $request->user();

        return response()->json([
            'average' => $count > 0 ? round($total / $count, 1) : null,
            'subjects' => $subjects,
            'total_grades' => $grades->count(),
            'name' => $user->name,
            'class_name' => $student->class?->name ?? '—',
        ]);
    }
}
