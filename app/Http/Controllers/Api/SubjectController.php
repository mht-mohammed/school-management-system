<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    private function normalize($s)
    {
        $s = str_replace(['أ', 'إ', 'آ'], 'ا', $s);
        $s = str_replace('ة', 'ه', $s);
        $s = preg_replace('/[\s_]+/', '', $s);
        $s = str_replace('ال', '', $s);
        return $s;
    }

    private function specializationMatches($spec, $subjectName)
    {
        if (!$spec) return false;
        $nSpec = $this->normalize($spec);
        $nSubj = $this->normalize($subjectName);
        return str_contains($nSubj, $nSpec) || str_contains($nSpec, $nSubj);
    }

    public function index(Request $request)
    {
        $subjects = Subject::with('class')->orderBy('grade_level_id');

        if ($request->has('grade_level_id')) {
            $subjects->where('grade_level_id', $request->grade_level_id);
        } elseif ($request->has('class_id')) {
            $section = \App\Models\Section::find($request->class_id);
            if ($section && $section->grade_level_id) {
                $subjects->where('grade_level_id', $section->grade_level_id);
            } else {
                $subjects->where('class_id', $request->class_id);
            }
        }
        $subjects = $subjects->get();

        // Determine which grade levels we're querying
        $gradeIds = $subjects->pluck('grade_level_id')->unique()->values()->toArray();

        // Build set of section IDs belonging to these grade levels
        $sectionsInGrades = \App\Models\Section::whereIn('grade_level_id', $gradeIds)->pluck('id');

        // 1) Teachers from assignedClasses
        $assignedTeachers = User::where('role', 'teacher')
            ->whereHas('teacher.assignedClasses', fn($q) => $q->whereIn('school_classes.id', $sectionsInGrades))
            ->with('teacher.assignedClasses')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'specialization' => $u->teacher->specialization,
                'class_ids' => $u->teacher->assignedClasses->pluck('id')->toArray(),
            ]);

        // 2) Teachers from actual schedule assignments in these grade levels
        $schedTeachers = \App\Models\Schedule::whereIn('section_id', $sectionsInGrades)
            ->whereNotNull('teacher_id')
            ->select('teacher_id', 'section_id')
            ->distinct()
            ->get()
            ->groupBy('teacher_id');

        $schedUserIds = $schedTeachers->keys()->toArray();
        $schedUsers = [];
        if (!empty($schedUserIds)) {
            $schedUsers = User::whereIn('id', $schedUserIds)->with('teacher')->get()->keyBy('id');
        }

        // Merge, avoiding duplicates
        $seenIds = $assignedTeachers->pluck('id')->unique()->values()->toArray();
        $teachers = collect($assignedTeachers);
        foreach ($schedTeachers as $userId => $scheds) {
            if (in_array($userId, $seenIds)) continue;
            $u = $schedUsers->get($userId);
            if (!$u || !$u->teacher) continue;
            $teachers->push([
                'id' => $u->id,
                'name' => $u->name,
                'specialization' => $u->teacher->specialization,
                'class_ids' => $scheds->pluck('section_id')->unique()->values()->toArray(),
            ]);
        }

        $result = $subjects->map(function ($subject) use ($teachers) {
            $sectionsInGrade = \App\Models\Section::where('grade_level_id', $subject->grade_level_id)->pluck('id')->toArray();
            $matched = $teachers->filter(fn($t) =>
                !empty(array_intersect($sectionsInGrade, $t['class_ids'])) &&
                $this->specializationMatches($t['specialization'], $subject->name)
            )->values()->map(fn($t) => ['id' => $t['id'], 'name' => $t['name'], 'specialization' => $t['specialization']]);

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'class_id' => $subject->class_id,
                'grade_level_id' => $subject->grade_level_id,
                'periods_per_week' => $subject->periods_per_week ?? 5,
                'teachers' => $matched,
                'class' => $subject->class,
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'periods_per_week' => 'nullable|integer|min:1|max:5',
        ]);

        $section = \App\Models\Section::where('grade_level_id', $validated['grade_level_id'])->first();
        if (!$section) {
            return response()->json(['message' => 'لا توجد شعب لهذا الصف'], 422);
        }

        $subject = Subject::create([
            'name' => $validated['name'],
            'class_id' => $section->id,
            'grade_level_id' => $validated['grade_level_id'],
            'periods_per_week' => $validated['periods_per_week'] ?? 5,
        ]);

        return response()->json($subject->load('class'), 201);
    }

    public function show(Subject $subject)
    {
        return response()->json($subject->load('class'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'periods_per_week' => 'nullable|integer|min:1|max:5',
        ]);

        $subject->update($validated);

        return response()->json($subject->load('class'));
    }

    public function destroy(Subject $subject)
    {
        $subject->grades()->delete();
        $subject->delete();
        return response()->json(['message' => 'تم حذف المادة']);
    }
}
