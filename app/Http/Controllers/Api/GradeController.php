<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
            'term' => 'required|string',
            'academic_year' => 'required|string',
        ]);

        $validated['teacher_id'] = $request->user()->id;

        $grade = Grade::create($validated);

        return response()->json($grade->load('subject'), 201);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'exam_type' => 'sometimes|string',
        ]);

        $grade->update($validated);

        return response()->json($grade->load('subject'));
    }

    public function report(Request $request)
    {
        $query = Grade::with(['student.user', 'subject.class', 'teacher']);

        if ($request->has('class_id')) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        return response()->json(
            $query->orderBy('teacher_id')->orderBy('subject_id')->orderBy('student_id')->latest()->get()
        );
    }
}
