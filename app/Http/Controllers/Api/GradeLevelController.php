<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    public function index()
    {
        return response()->json(GradeLevel::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:grade_levels,name',
            'stage' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
        ]);

        if (empty($validated['academic_year'])) {
            $validated['academic_year'] = date('Y') . '-' . (date('Y') + 1);
        }

        $gl = GradeLevel::create($validated);

        return response()->json($gl, 201);
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'stage' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
        ]);

        $gradeLevel->update($validated);

        return response()->json($gradeLevel);
    }

    public function destroy(GradeLevel $gradeLevel)
    {
        $sectionIds = \App\Models\Section::where('grade_level_id', $gradeLevel->id)->pluck('id');
        \App\Models\Subject::whereIn('class_id', $sectionIds)->delete();
        \DB::table('class_teacher')->whereIn('class_id', $sectionIds)->delete();
        \App\Models\Section::where('grade_level_id', $gradeLevel->id)->delete();
        $gradeLevel->delete();

        return response()->json(['message' => 'تم حذف المرحلة وجميع شعبها وموادها']);
    }

    public function distributeStudents(GradeLevel $gradeLevel)
    {
        $sections = \App\Models\Section::where('grade_level_id', $gradeLevel->id)->get();
        if ($sections->isEmpty()) {
            return response()->json(['message' => 'لا توجد شعب في هذه المرحلة'], 422);
        }

        $students = Student::whereIn('class_id', $sections->pluck('id'))
            ->orWhere(function ($q) use ($gradeLevel, $sections) {
                $q->whereNull('class_id');
            })
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'لا يوجد طلاب للتوزيع'], 422);
        }

        $shuffled = $students->shuffle();
        $sectionIds = $sections->pluck('id')->toArray();
        $count = 0;

        foreach ($shuffled as $i => $student) {
            $student->update(['class_id' => $sectionIds[$i % count($sectionIds)]]);
            $count++;
        }

        return response()->json(['message' => "تم توزيع {$count} طالب على " . count($sections) . ' شعب']);
    }
}
