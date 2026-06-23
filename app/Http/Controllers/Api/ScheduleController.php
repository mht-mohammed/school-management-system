<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    private $periodTimes = [
        1 => '08:00 - 08:45',
        2 => '08:50 - 09:35',
        3 => '09:40 - 10:25',
        4 => '10:55 - 11:40',
        5 => '11:45 - 12:30',
    ];

    public function index(Request $request)
    {
        $query = Schedule::with(['class', 'section', 'subject', 'teacher']);

        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->has('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('period_number')->get();

        // Attach period time labels
        $schedules->each(function ($s) {
            $s->period_label = $this->periodTimes[$s->period_number] ?? '';
        });

        return response()->json($schedules);
    }

    public function grid(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:school_classes,id']);
        $sectionId = $request->section_id;

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('section_id', $sectionId)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        // Build 5x5 grid: days x periods
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $grid = [];
        foreach ($days as $day) {
            $row = ['day' => $day, 'periods' => []];
            for ($p = 1; $p <= 5; $p++) {
                $entry = $schedules->firstWhere(fn($s) => $s->day_of_week === $day && $s->period_number === $p);
                $row['periods'][$p] = $entry ? [
                    'id' => $entry->id,
                    'subject_id' => $entry->subject_id,
                    'subject_name' => $entry->subject->name ?? '',
                    'teacher_id' => $entry->teacher_id,
                    'teacher_name' => $entry->teacher->name ?? '',
                    'room' => $entry->room,
                ] : null;
            }
            $grid[] = $row;
        }

        return response()->json(['section_id' => $sectionId, 'grid' => $grid]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|in:sunday,monday,tuesday,wednesday,thursday',
            'period_number' => 'required|integer|min:1|max:5',
            'room' => 'nullable|string|max:255',
        ]);

        // Auto-set class_id from section
        $section = Section::findOrFail($validated['section_id']);
        $validated['class_id'] = $section->id;

        // Validate unique: section + day + period
        $exists = Schedule::where('section_id', $validated['section_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('period_number', $validated['period_number'])
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'هذه الحصة محجوزة مسبقاً - يوجد تعارض في اليوم والحصة'], 422);
        }

        // Validate subject belongs to section's grade level
        $subject = Subject::findOrFail($validated['subject_id']);
        if ($subject->grade_level_id != $section->grade_level_id) {
            return response()->json(['message' => 'المادة لا تنتمي إلى هذا الصف'], 422);
        }

        // Validate no duplicate subject on the same day for this section
        $sameSubjectSameDay = Schedule::where('section_id', $validated['section_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();
        if ($sameSubjectSameDay) {
            return response()->json(['message' => 'هذه المادة تُدرّس بالفعل في نفس اليوم لهذه الشعبة - لا يمكن التكرار'], 422);
        }

        // Validate teacher specialization matches subject
        if (!empty($validated['teacher_id'])) {
            $teacherModel = Teacher::where('user_id', $validated['teacher_id'])->first();
            $user = User::find($validated['teacher_id']);
            $spec = $user?->teacher?->specialization ?? '';
            if ($spec) {
                $normalize = fn($s) => str_replace(['أ','إ','آ'],'ا', str_replace('ة','ه', preg_replace('/[\s_]+/','', str_replace('ال','',$s))));
                $specNorm = $normalize($spec);
                $subjNorm = $normalize($subject->name);
                if (!str_contains($specNorm, $subjNorm) && !str_contains($subjNorm, $specNorm)) {
                    return response()->json(['message' => "تخصص المعلم ({$spec}) لا يتوافق مع المادة ({$subject->name})"], 422);
                }
            }
        }

        // Validate teacher not double-booked
        if (!empty($validated['teacher_id'])) {
            $teacherBusy = Schedule::where('teacher_id', $validated['teacher_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('period_number', $validated['period_number'])
                ->exists();
            if ($teacherBusy) {
                return response()->json(['message' => 'المعلم مشغول في هذه الحصة مع شعبة أخرى'], 422);
            }
        }

        $schedule = Schedule::create($validated);

        return response()->json($schedule->load(['class', 'section', 'subject', 'teacher']), 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json($schedule->load(['class', 'section', 'subject', 'teacher']));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'room' => 'nullable|string|max:255',
        ]);

        // If subject changed, re-validate
        if (isset($validated['subject_id'])) {
            $section = Section::find($schedule->section_id);
            $subject = Subject::findOrFail($validated['subject_id']);
            if ($section && $subject->grade_level_id != $section->grade_level_id) {
                return response()->json(['message' => 'المادة لا تنتمي إلى هذا الصف'], 422);
            }
            // Check no duplicate subject same day (exclude self)
            $sameDayExists = Schedule::where('section_id', $schedule->section_id)
                ->where('day_of_week', $schedule->day_of_week)
                ->where('subject_id', $validated['subject_id'])
                ->where('id', '!=', $schedule->id)
                ->exists();
            if ($sameDayExists) {
                return response()->json(['message' => 'هذه المادة تُدرّس بالفعل في نفس اليوم لهذه الشعبة - لا يمكن التكرار'], 422);
            }
        }

        $schedule->update($validated);

        return response()->json($schedule->load(['class', 'section', 'subject', 'teacher']));
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(['message' => 'تم حذف الحصة']);
    }

    public function bulkDeleteBySections(Request $request)
    {
        $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:school_classes,id',
        ]);

        $deleted = Schedule::whereIn('section_id', $request->section_ids)->delete();
        return response()->json(['message' => "تم حذف $deleted حصة"]);
    }

    public function generateFromTeachers()
    {
        $normalize = fn($s) => str_replace(['أ','إ','آ'],'ا', str_replace('ة','ه', preg_replace('/[\s_]+/','', str_replace('ال','',$s))));
        $days = ['sunday','monday','tuesday','wednesday','thursday'];
        $created = 0;

        // Collect all (section, subject, teacher) assignments
        $sectionAssignments = [];

        $teachers = Teacher::with('user', 'assignedClasses')->get();
        foreach ($teachers as $teacher) {
            $spec = $teacher->specialization;
            if (!$spec) continue;
            $specNorm = $normalize($spec);

            $subjects = Subject::whereNotNull('grade_level_id')->get()->filter(function ($sub) use ($specNorm, $normalize) {
                $subjNorm = $normalize($sub->name);
                return str_contains($specNorm, $subjNorm) || str_contains($subjNorm, $specNorm);
            });

            if ($subjects->isEmpty()) continue;

            foreach ($teacher->assignedClasses as $section) {
                foreach ($subjects as $subject) {
                    $sectionAssignments[$section->id][] = [
                        'subject' => $subject,
                        'teacher_user_id' => $teacher->user_id,
                    ];
                }
            }
        }

        foreach ($sectionAssignments as $sectionId => $assignments) {
            $section = Section::find($sectionId);
            if (!$section) continue;

            // Deduplicate
            $unique = [];
            foreach ($assignments as $a) {
                $key = $a['subject']->id . '_' . $a['teacher_user_id'];
                $unique[$key] = $a;
            }
            $unique = collect($unique)->sortByDesc(fn($a) => $a['subject']->periods_per_week ?? 0);

            // Delete existing schedules for these subjects in this section
            $subjectIds = $unique->pluck('subject.id')->unique();
            Schedule::where('section_id', $sectionId)->whereIn('subject_id', $subjectIds)->delete();

            // Build day buckets: for each subject, distribute to days (max 1/day)
            $dayBuckets = [[], [], [], [], []];
            foreach ($unique as $a) {
                $sub = $a['subject'];
                $count = min($sub->periods_per_week ?? 5, 5);
                $availableDays = [];
                for ($d = 0; $d < 5; $d++) {
                    $daySubjectIds = array_map(fn($e) => $e['subject']->id, $dayBuckets[$d]);
                    if (count($dayBuckets[$d]) < 5 && !in_array($sub->id, $daySubjectIds)) {
                        $availableDays[] = $d;
                    }
                }
                shuffle($availableDays);
                for ($i = 0; $i < $count && $i < count($availableDays); $i++) {
                    $dayBuckets[$availableDays[$i]][] = ['subject' => $sub, 'teacher_user_id' => $a['teacher_user_id']];
                }
            }

            // Place into periods
            for ($d = 0; $d < 5; $d++) {
                $dayEntries = $dayBuckets[$d];
                shuffle($dayEntries);

                $period = 1;
                foreach ($dayEntries as $entry) {
                    if ($period > 5) break;

                    // Skip if teacher is already assigned elsewhere at this slot
                    $teacherBusy = Schedule::where('teacher_id', $entry['teacher_user_id'])
                        ->where('day_of_week', $days[$d])
                        ->where('period_number', $period)
                        ->exists();
                    if ($teacherBusy) continue;

                    Schedule::create([
                        'class_id' => $sectionId,
                        'section_id' => $sectionId,
                        'subject_id' => $entry['subject']->id,
                        'teacher_id' => $entry['teacher_user_id'],
                        'day_of_week' => $days[$d],
                        'period_number' => $period,
                        'room' => '',
                    ]);
                    $period++;
                    $created++;
                }
            }
        }

        return response()->json([
            'message' => "✅ تم إنشاء {$created} حصة دراسية تلقائياً."
        ]);
    }
}
