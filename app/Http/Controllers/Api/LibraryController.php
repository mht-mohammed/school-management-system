<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    // --- Teacher ---
    public function teacherBooks(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json([]);

        $books = Book::where('teacher_id', $teacher->id)
            ->with('classes')
            ->latest()
            ->get();

        return response()->json($books);
    }

    public function teacherStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|string|max:500',
            'description' => 'nullable|string',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $teacher = $request->user()->teacher;

        $book = Book::create([
            'teacher_id' => $teacher->id,
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $book->classes()->sync($validated['class_ids']);

        return response()->json(['message' => '✅ تمت إضافة الكتاب', 'book' => $book->load('classes')], 201);
    }

    public function teacherUpdate(Request $request, Book $book)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $book->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'link' => 'required|string|max:500',
            'description' => 'nullable|string',
            'class_ids' => 'sometimes|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $book->update(collect($validated)->except('class_ids')->toArray());

        if (isset($validated['class_ids'])) {
            $book->classes()->sync($validated['class_ids']);
        }

        return response()->json(['message' => '✅ تم تحديث الكتاب', 'book' => $book->load('classes')]);
    }

    public function teacherDestroy(Request $request, Book $book)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $book->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $book->delete();
        return response()->json(['message' => '🗑️ تم حذف الكتاب']);
    }

    // --- Admin ---
    public function adminBooks()
    {
        $books = Book::with('teacher.user', 'teacher.taughtSubjects', 'classes.gradeLevel')->latest()->get();
        return response()->json($books);
    }

    public function adminDestroy(Book $book)
    {
        $book->delete();
        return response()->json(['message' => '🗑️ تم حذف الكتاب']);
    }

    // --- Student ---
    public function studentBooks(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) return response()->json([]);

        $classIds = SchoolClass::whereHas('students', fn($q) => $q->where('students.id', $student->id))
            ->pluck('id');

        $books = Book::whereHas('classes', fn($q) => $q->whereIn('school_classes.id', $classIds))
            ->with('teacher.user')
            ->latest()
            ->get();

        return response()->json($books);
    }
}
