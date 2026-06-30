<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'teacher')->whereHas('teacher')
            ->with('teacher.assignedClasses')
            ->get();

        return response()->json($teachers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'required|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'teacher',
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'qualification' => $validated['qualification'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
            ]);

            return response()->json($teacher->load('user'), 201);
        });
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        $user = $teacher->user;

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'avatar' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        return DB::transaction(function () use ($validated, $teacher, $user, $request) {
            $userData = [];
            if (isset($validated['name'])) $userData['name'] = $validated['name'];
            if (isset($validated['email'])) $userData['email'] = $validated['email'];
            if (array_key_exists('phone', $validated)) $userData['phone'] = $validated['phone'];
            if (!empty($validated['password'])) $userData['password'] = Hash::make($validated['password']);
            if (!empty($userData)) $user->update($userData);

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

            $teacherData = [];
            if (array_key_exists('qualification', $validated)) $teacherData['qualification'] = $validated['qualification'];
            if (array_key_exists('specialization', $validated)) $teacherData['specialization'] = $validated['specialization'];
            if (array_key_exists('salary', $validated)) $teacherData['salary'] = $validated['salary'];
            if (array_key_exists('hire_date', $validated)) $teacherData['hire_date'] = $validated['hire_date'];
            if (!empty($teacherData)) $teacher->update($teacherData);

            return response()->json($teacher->fresh()->load('user'));
        });
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $userId = $teacher->user_id;

        return DB::transaction(function () use ($teacher, $userId) {
            // Nullify teacher_id in schedules before deleting
            \App\Models\Schedule::where('teacher_id', $userId)->update(['teacher_id' => null]);
            \DB::table('class_teacher')->where('teacher_id', $userId)->delete();
            \DB::table('subject_teacher')->where('user_id', $userId)->delete();
            $teacher->delete();
            User::where('id', $userId)->delete();
            return response()->json(['message' => 'تم حذف المعلم']);
        });
    }

    public function byClass($classId)
    {
        return response()->json(
            Teacher::whereHas('assignedClasses', fn($q) => $q->where('class_id', $classId))
                ->with('user')->get()
        );
    }

    public function getAssignedClasses($teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        return response()->json($teacher->assignedClasses);
    }

    public function assignClasses(Request $request, $teacherId)
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);
        $validated = $request->validate([
            'class_ids' => 'present|array',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        $specialization = $teacher->specialization;
        if ($specialization) {
            $normalize = fn($s) => str_replace(['أ','إ','آ'],'ا', str_replace('ة','ه', preg_replace('/[\s_]+/','', str_replace('ال','',$s))));
            $specNorm = $normalize($specialization);

            $existing = \DB::table('class_teacher')
                ->join('teachers', 'class_teacher.teacher_id', '=', 'teachers.id')
                ->whereIn('class_teacher.class_id', $validated['class_ids'])
                ->where('teachers.id', '!=', $teacherId)
                ->whereNotNull('teachers.specialization')
                ->select('teachers.specialization', 'class_teacher.class_id')
                ->get();

            foreach ($existing as $e) {
                $existingSpecNorm = $normalize($e->specialization);
                if ($specNorm === $existingSpecNorm ||
                    str_contains($specNorm, $existingSpecNorm) ||
                    str_contains($existingSpecNorm, $specNorm)) {
                    $section = \App\Models\Section::find($e->class_id);
                    return response()->json([
                        'message' => 'يوجد معلم آخر بنفس التخصص ("' . $e->specialization . '") معيّن على "' . ($section->name ?? '') . ' شعبة ' . ($section->section ?? '') . '" بالفعل'
                    ], 422);
                }
            }
        }

        $teacher->assignedClasses()->sync($validated['class_ids']);
        return response()->json(['message' => 'تم تحديث الصفوف', 'classes' => $teacher->assignedClasses]);
    }

    public function classes(Request $request)
    {
        $teacherId = $request->user()->id;

        $schedules = \App\Models\Schedule::where('teacher_id', $teacherId)
            ->select('section_id', 'subject_id')
            ->distinct()->get();

        $sectionIds = $schedules->pluck('section_id')->unique();
        if ($sectionIds->isEmpty()) return response()->json([]);

        $classes = \App\Models\SchoolClass::whereIn('id', $sectionIds)
            ->with('students.user')
            ->get();

        $subjectMap = [];
        foreach ($schedules as $s) {
            $subjectMap[$s->section_id][] = $s->subject_id;
        }

        foreach ($classes as $class) {
            $ids = $subjectMap[$class->id] ?? [];
            $class->subjects = \App\Models\Subject::whereIn('id', $ids)->get();
        }

        return response()->json($classes);
    }

    public function sectionsWithGrades(Request $request)
    {
        $request->validate(['class_id' => 'required']);
        $teacherId = $request->user()->id;

        $schedules = \App\Models\Schedule::where('teacher_id', $teacherId)
            ->where('class_id', $request->class_id)
            ->select('section_id', 'subject_id')
            ->distinct()->get();

        $sectionIds = $schedules->pluck('section_id')->unique();
        if ($sectionIds->isEmpty()) return response()->json([]);

        $sections = \App\Models\SchoolClass::whereIn('id', $sectionIds)->get();

        foreach ($sections as $section) {
            $sectionSubjects = $schedules->where('section_id', $section->id)->pluck('subject_id')->unique();
            $subjectInfo = [];
            foreach ($sectionSubjects as $subjectId) {
                $subject = \App\Models\Subject::find($subjectId);
                $gradeCount = Grade::where('subject_id', $subjectId)
                    ->where('teacher_id', $teacherId)
                    ->whereHas('student', fn($q) => $q->where('class_id', $section->id))
                    ->count();
                $subjectInfo[] = [
                    'id' => $subjectId,
                    'name' => $subject?->name ?? '',
                    'has_grades' => $gradeCount > 0,
                    'grade_count' => $gradeCount,
                ];
            }
            $section->subjects = $subjectInfo;
        }

        return response()->json($sections);
    }

    public function schedule(Request $request)
    {
        $teacherId = $request->user()->id;
        $schedules = \App\Models\Schedule::with(['subject', 'class', 'section'])
            ->where('teacher_id', $teacherId)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        return response()->json($schedules);
    }

    public function stats(Request $request)
    {
        $teacherId = $request->user()->id;
        $gradeCount = Grade::where('teacher_id', $teacherId)->count();
        $sectionIds = \App\Models\Schedule::where('teacher_id', $teacherId)
            ->distinct()->pluck('section_id');
        $classes = $sectionIds->isNotEmpty()
            ? \App\Models\SchoolClass::whereIn('id', $sectionIds)->get()
            : collect();
        $teacher = $request->user()->teacher;
        $normalize = fn($s) => str_replace(['أ','إ','آ'],'ا', str_replace('ة','ه', preg_replace('/[\s_]+/','', str_replace('ال','',$s))));
        $subjectNames = collect(['الرياضيات','العلوم الحياتية','اللغة العربية','اللغة الإنجليزية','التربية الإسلامية','الدراسات الاجتماعية','التكنولوجيا والحاسوب']);
        $specNorm = $normalize($teacher->specialization ?? '');
        $subjectCount = $specNorm ? $subjectNames->filter(fn($n) => str_contains($normalize($n), $specNorm) || str_contains($specNorm, $normalize($n)))->count() : 0;
        $studentCount = 0;
        foreach ($classes as $c) $studentCount += $c->students()->count();
        return response()->json([
            'classes_count' => $classes->count(), 'subjects_count' => $subjectCount,
            'students_count' => $studentCount, 'grades_count' => $gradeCount,
        ]);
    }

    public function classSections(Request $request, $classId)
    {
        $class = \App\Models\SchoolClass::with('sections.teacher.user')->findOrFail($classId);
        return response()->json($class->sections);
    }

    public function studentsByClass(Request $request, $classId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json([], 403);
        $class = $teacher->assignedClasses()->with('students.user')->findOrFail($classId);
        $students = $class->students()->with('user');
        if ($request->has('section_id')) $students->where('section_id', $request->section_id);
        return response()->json($students->get());
    }

    public function getGrades(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:school_classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'term' => 'nullable|string',
            'academic_year' => 'nullable|string',
        ]);

        $teacherId = $request->user()->id;

        $query = Grade::where('teacher_id', $teacherId)
            ->whereHas('student', fn($q) => $q->where('class_id', $request->section_id))
            ->with('student.user:id,name', 'subject:id,name');

        if ($request->subject_id) {
            $teaches = \App\Models\Schedule::where('teacher_id', $teacherId)
                ->where('section_id', $request->section_id)
                ->where('subject_id', $request->subject_id)->exists();
            if (!$teaches) {
                return response()->json(['message' => 'ليس لديك صلاحية'], 403);
            }
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->term) $query->where('term', $request->term);
        if ($request->academic_year) $query->where('academic_year', $request->academic_year);

        $grades = $query->get();

        return response()->json($grades);
    }

    public function storeGradesBulk(Request $request)
    {
        $validated = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.subject_id' => 'required|exists:subjects,id',
            'grades.*.exam_type' => 'required|string|max:255',
            'grades.*.score' => 'required|numeric|min:0|max:100',
            'grades.*.term' => 'required|string',
            'grades.*.academic_year' => 'required|string',
        ]);
        $teacherId = $request->user()->id;
        $created = [];
        foreach ($validated['grades'] as $g) {
            $g['teacher_id'] = $teacherId;
            $grade = Grade::create($g);
            $created[] = $grade;
            $subjectName = \App\Models\Subject::find($g['subject_id'])?->name ?? 'غير معروفة';
            NotificationController::notifyParentOnGrade($g['student_id'], $g['score'], $subjectName);
        }
        return response()->json(['message' => 'تم حفظ الدرجات', 'count' => count($created)], 201);
    }

    public function downloadGradeTemplate(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:school_classes,id',
        ]);

        $teacherId = $request->user()->id;
        $sectionId = $request->section_id;

        $schedule = \App\Models\Schedule::where('teacher_id', $teacherId)
            ->where('section_id', $sectionId)->first();
        if (!$schedule) {
            return response()->json(['message' => 'ليس لديك صلاحية لهذا الصف'], 403);
        }

        $subject = Subject::findOrFail($schedule->subject_id);
        $section = SchoolClass::with('gradeLevel')->findOrFail($sectionId);

        $students = Student::where('class_id', $sectionId)
            ->whereHas('user')
            ->with('user')
            ->get();

        $teacher = $request->user()->teacher;
        $dist = $teacher?->grade_distribution;
        if (!$dist || count($dist) !== 4) {
            $dist = [
                ['key' => 'monthly1', 'label' => 'امتحان شهري أول', 'max' => 20],
                ['key' => 'midterm', 'label' => 'امتحان نصفي', 'max' => 30],
                ['key' => 'monthly2', 'label' => 'امتحان شهري ثاني', 'max' => 20],
                ['key' => 'final', 'label' => 'امتحان نهائي', 'max' => 30],
            ];
        }

        $columns = ['البريد الإلكتروني', 'اسم الطالب', 'المادة',
            $dist[0]['label'] . ' (من ' . $dist[0]['max'] . ')',
            $dist[1]['label'] . ' (من ' . $dist[1]['max'] . ')',
            $dist[2]['label'] . ' (من ' . $dist[2]['max'] . ')',
            $dist[3]['label'] . ' (من ' . $dist[3]['max'] . ')',
            'العلامة النهائية (من 100)', 'الفصل', 'العام الدراسي'];
        $academicYear = date('Y') . '-' . (date('Y') + 1);

        $filePath = tempnam(sys_get_temp_dir(), 'grades_') . '.csv';
        $handle = fopen($filePath, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns);

        foreach ($students as $student) {
            fputcsv($handle, [
                $student->user->email,
                $student->user->name,
                $subject->name,
                '', '', '', '', '',
                'الأول',
                $academicYear,
            ]);
        }

        fclose($handle);

        return response()->download($filePath, "درجات_{$section->name}.csv")->deleteFileAfterSend(true);
    }

    public function getGradeDistribution(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['message' => 'ليس لديك صلاحية معلم'], 403);

        $default = [
            ['key' => 'monthly1', 'label' => 'امتحان شهري أول', 'max' => 20],
            ['key' => 'midterm', 'label' => 'امتحان نصفي', 'max' => 30],
            ['key' => 'monthly2', 'label' => 'امتحان شهري ثاني', 'max' => 20],
            ['key' => 'final', 'label' => 'امتحان نهائي', 'max' => 30],
        ];

        $saved = $teacher->grade_distribution;

        return response()->json($saved ?: $default);
    }

    public function saveGradeDistribution(Request $request)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['message' => 'ليس لديك صلاحية معلم'], 403);

        $validated = $request->validate([
            'distribution' => 'required|array|size:4',
            'distribution.*.key' => 'required|string|in:monthly1,midterm,monthly2,final',
            'distribution.*.label' => 'required|string',
            'distribution.*.max' => 'required|numeric|min:1',
        ]);

        $total = collect($validated['distribution'])->sum('max');
        if ($total != 100) {
            return response()->json(['message' => 'مجموع العلامات يجب أن يساوي 100 (الموجود: ' . $total . ')'], 422);
        }

        $teacher->update(['grade_distribution' => $validated['distribution']]);

        return response()->json(['message' => '✅ تم حفظ توزيع العلامات', 'distribution' => $validated['distribution']]);
    }
}
