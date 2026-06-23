<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        return response()->json(
            SchoolClass::with(['teacher', 'gradeLevel', 'students.user'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'section' => 'nullable|string|max:10',
            'stage' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:255',
        ]);

        if (empty($validated['academic_year'])) {
            $validated['academic_year'] = date('Y') . '-' . (date('Y') + 1);
        }
        if (empty($validated['section'])) {
            $validated['section'] = 'أ';
        }

        $class = SchoolClass::create($validated);

        return response()->json($class->load(['teacher', 'gradeLevel']), 201);
    }

    public function show(SchoolClass $class)
    {
        return response()->json($class->load(['teacher', 'gradeLevel', 'students.user', 'subjects', 'schedules']));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'grade_level_id' => 'sometimes|exists:grade_levels,id',
            'section' => 'nullable|string|max:10',
            'stage' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:255',
        ]);

        $class->update($validated);

        return response()->json($class->load(['teacher', 'gradeLevel']));
    }

    public function destroy(SchoolClass $class)
    {
        \App\Models\Subject::where('class_id', $class->id)->delete();
        \DB::table('class_teacher')->where('class_id', $class->id)->delete();
        $class->delete();

        return response()->json(['message' => 'تم حذف الصف والمواد المرتبطة']);
    }
}
