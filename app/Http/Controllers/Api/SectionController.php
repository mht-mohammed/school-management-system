<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::with(['teacher', 'gradeLevel']);
        if ($request->has('grade_level_id')) {
            $query->where('grade_level_id', $request->grade_level_id);
        }
        return response()->json($query->latest()->get());
    }

    public function show($id)
    {
        $section = Section::with(['teacher', 'gradeLevel'])->findOrFail($id);
        return response()->json($section);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'section' => 'required|string|max:10',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:20',
        ]);

        if ($validated['teacher_id'] ?? null) {
            $this->ensureTeacherNotHomeroom($validated['teacher_id'], null);
        }

        $gl = \App\Models\GradeLevel::findOrFail($validated['grade_level_id']);
        $validated['name'] = $gl->name;
        $validated['stage'] = $gl->stage;
        $validated['academic_year'] = $validated['academic_year'] ?? (date('Y') . '-' . (date('Y') + 1));

        $section = Section::create($validated);

        $this->createDefaultSubjects($section->id);

        return response()->json($section->load(['teacher', 'gradeLevel']), 201);
    }

    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $validated = $request->validate([
            'section' => 'sometimes|string|max:10',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'nullable|string|max:20',
        ]);

        if (array_key_exists('teacher_id', $validated) && $validated['teacher_id'] !== $section->teacher_id) {
            if ($validated['teacher_id']) {
                $this->ensureTeacherNotHomeroom($validated['teacher_id'], $id);
            }
        }

        $section->update($validated);
        return response()->json($section->load(['teacher', 'gradeLevel']));
    }

    public function destroy($id)
    {
        $section = Section::findOrFail($id);

        Subject::where('class_id', $id)->delete();
        \DB::table('class_teacher')->where('class_id', $id)->delete();

        $section->delete();
        return response()->json(['message' => 'تم حذف الشعبة والمواد المرتبطة']);
    }

    private function createDefaultSubjects($classId)
    {
        $section = Section::find($classId);
        if (!$section || !$section->grade_level_id) return;

        // Check if subjects already exist for this grade level
        $existing = Subject::where('grade_level_id', $section->grade_level_id)->count();
        if ($existing > 0) return; // already created for this grade level

        $names = [
            'الرياضيات', 'العلوم الحياتية', 'اللغة العربية',
            'اللغة الإنجليزية', 'التربية الإسلامية',
            'الدراسات الاجتماعية', 'التكنولوجيا والحاسوب',
            'التربية الرياضية',
        ];
        foreach ($names as $name) {
            Subject::create([
                'name' => $name,
                'class_id' => $classId,
                'grade_level_id' => $section->grade_level_id,
            ]);
        }
    }

    private function ensureTeacherNotHomeroom($teacherId, $excludeSectionId)
    {
        $existing = Section::where('teacher_id', $teacherId)
            ->where('id', '!=', $excludeSectionId)
            ->exists();

        if ($existing) {
            abort(422, 'هذا المعلم مربي صف بالفعل — لا يمكن تعيينه لأكثر من شعبة');
        }
    }

}
