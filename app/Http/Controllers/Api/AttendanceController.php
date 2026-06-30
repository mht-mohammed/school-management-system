<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:school_classes,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string',
        ]);

        $validated['teacher_id'] = $request->user()->id;

        $attendance = Attendance::create($validated);

        return response()->json($attendance, 201);
    }

    public function byClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'date' => 'required|date',
        ]);

        return response()->json(
            Attendance::where('class_id', $request->class_id)
                ->where('date', $request->date)
                ->with('student.user')
                ->get()
        );
    }

    public function report(Request $request)
    {
        $query = Attendance::with(['student.user', 'class']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return response()->json($query->latest('date')->paginate(50));
    }

    public function downloadMonthlyTemplate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'month' => 'required|regex:/^\d{4}-\d{2}$/',
        ]);

        $classId = $request->class_id;
        $month = $request->month;

        [$year, $monthNum] = explode('-', $month);
        $year = (int)$year;
        $monthNum = (int)$monthNum;
        $daysInMonth = (int)date('t', strtotime("$year-$monthNum-01"));

        $students = Student::where('class_id', $classId)->with('user')->get();
        $class = SchoolClass::find($classId);

        $rows = [];
        $header = ['البريد الإلكتروني للطالب', 'اسم الطالب'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $header[] = (string)$d;
        }
        $rows[] = $header;

        foreach ($students as $student) {
            $row = [$student->user->email, $student->user->name];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $row[] = '';
            }
            $rows[] = $row;
        }

        $bom = "\xEF\xBB\xBF";
        $content = $bom;
        foreach ($rows as $r) {
            $content .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $r)) . "\n";
        }

        $filename = 'حضور_' . str_replace(' ', '_', $class->name) . '_' . $month . '.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function importMonthly(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'month' => 'required|regex:/^\d{4}-\d{2}$/',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $classId = $request->class_id;
        $month = $request->month;

        [$year, $monthNum] = explode('-', $month);
        $year = (int)$year;
        $monthNum = (int)$monthNum;
        $daysInMonth = (int)date('t', strtotime("$year-$monthNum-01"));

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = $line;
        }
        fclose($handle);

        if (count($rows) < 2) {
            return response()->json(['message' => 'الملف فارغ أو لا يحتوي على بيانات'], 422);
        }

        array_shift($rows);

        $allStudents = Student::with('user')->get()->keyBy(fn($s) => $s->user->email);

        // Delete all attendance for classId + month
        Attendance::where('class_id', $classId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->delete();

        $imported = 0;
        $skippedStatus = 0;
        $summary = [];
        $notFound = [];
        $foundEmails = [];
        $otherClassWarnings = [];

        $statusMap = [
            'حاضر' => 'present',
            'غائب' => 'absent',
            'متأخر' => 'late',
            'present' => 'present',
            'absent' => 'absent',
            'late' => 'late',
            '1' => 'present',
            '0' => 'absent',
            'true' => 'present',
            'false' => 'absent',
        ];

        foreach ($rows as $row) {
            $email = trim($row[0] ?? '');
            if (empty($email)) continue;

            $student = $allStudents->get($email);
            if (!$student) {
                $notFound[] = $email;
                continue;
            }

            $foundEmails[] = $email;

            // If student belongs to a different class, delete their old attendance there too
            $actualClassId = $student->class_id;
            if ($actualClassId != $classId) {
                Attendance::where('student_id', $student->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->delete();
                $otherClassWarnings[] = $student->user->name;
            }

            $studentSummary = ['present' => 0, 'absent' => 0];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $status = trim($row[$day + 1] ?? '');
                if ($status === '') continue;

                $normalizedStatus = $statusMap[$status] ?? null;
                if (!$normalizedStatus) { $skippedStatus++; continue; }

                $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $day);

                Attendance::create([
                    'student_id' => $student->id,
                    'class_id' => $actualClassId,
                    'date' => $date,
                    'status' => $normalizedStatus,
                    'teacher_id' => $request->user()->id,
                ]);

                $studentSummary[$normalizedStatus]++;
                $imported++;
            }

            $total = array_sum($studentSummary);
            $totalDays = $studentSummary['present'] + $studentSummary['absent'];
            $summary[] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'avatar' => $student->user->avatar,
                'present' => $studentSummary['present'],
                'absent' => $studentSummary['absent'],
                'total' => $totalDays,
                'percentage' => $totalDays > 0 ? round($studentSummary['present'] / $totalDays * 100) : 0,
            ];
        }

        $foundInClassIds = Student::where('class_id', $classId)
            ->whereHas('user', fn($q) => $q->whereIn('email', $foundEmails))
            ->pluck('id');
        $missingStudents = Student::where('class_id', $classId)
            ->whereNotIn('id', $foundInClassIds)
            ->with('user')
            ->get()
            ->map(fn($s) => $s->user->name)
            ->values()
            ->toArray();

        $message = "تم استيراد $imported سجل حضور للشهر $month";
        if ($skippedStatus > 0) {
            $message .= ". تم تخطي $skippedStatus خلية لأن القيم غير معروفة (استخدم: حاضر/غائب أو 1/0)";
        }

        return response()->json([
            'message' => $message,
            'imported' => $imported,
            'skipped_status' => $skippedStatus,
            'summary' => $summary,
            'not_found' => $notFound,
            'missing_students' => $missingStudents,
            'other_class_students' => array_unique($otherClassWarnings),
        ]);
    }
}
