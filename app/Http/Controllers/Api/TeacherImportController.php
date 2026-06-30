<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\ODS\Reader as ODSReader;

class TeacherImportController extends Controller
{
    private function openReader($path, $ext)
    {
        $reader = match ($ext) {
            'csv' => new CSVReader(),
            'ods' => new ODSReader(),
            default => new XLSXReader(),
        };
        $reader->open($path);
        return $reader;
    }

    public function importGrades(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv,ods']);

        $teacherId = $request->user()->id;
        $teacherSections = \App\Models\Schedule::where('teacher_id', $teacherId)
            ->distinct()->pluck('section_id')->toArray();
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $reader = $this->openReader($file->getRealPath(), $ext);

        $created = 0;
        $errors = [];
        $rowNum = 0;
        $importedSections = [];
        $importedSubjects = [];
        $foundStudentIds = [];
        $processedSectionIds = [];
        $studentsWithNewGrades = [];

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

        $currentLabels = array_map(fn($d) => $d['label'], $dist);

        // Check if existing grades use different exam_type labels
        $existingLabels = Grade::where('teacher_id', $teacherId)
            ->where('exam_type', '!=', 'الدرجة النهائية')
            ->distinct()->pluck('exam_type')->toArray();

        $labelsDiffer = false;
        if (count($existingLabels) > 0) {
            sort($existingLabels); sort($currentLabels);
            if ($existingLabels !== $currentLabels) {
                $labelsDiffer = true;
            }
        }

        // If labels differ and user hasn't confirmed, ask for confirmation
        if ($labelsDiffer && !$request->boolean('force')) {
            $reader->close();
            return response()->json([
                'confirmation_required' => true,
                'message' => '⚠️ توزيع العلامات الحالي مختلف عن التوزيعة المستخدمة في الدرجات القديمة.',
                'old_labels' => $existingLabels,
                'new_labels' => $currentLabels,
            ]);
        }

        // Get all students in teacher's sections
        $allSectionStudents = \App\Models\Student::whereIn('class_id', $teacherSections)
            ->with('user:id,email,name')
            ->get();

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;
                if ($rowNum === 1) continue;

                $vals = array_map(fn($c) => (string) $c->getValue(), $row->getCells());
                $colCount = count($vals);
                if ($colCount >= 10) {
                    // 10-col: email | name | subject | m1 | mid | m2 | final | overall | term | year
                    $studentEmail = $vals[0] ?? '';
                    $subjectName = $vals[2] ?? '';
                    $examScores = [
                        ['key' => 'monthly1', 'label' => $dist[0]['label'], 'score' => $vals[3] ?? '', 'max' => $dist[0]['max']],
                        ['key' => 'midterm', 'label' => $dist[1]['label'], 'score' => $vals[4] ?? '', 'max' => $dist[1]['max']],
                        ['key' => 'monthly2', 'label' => $dist[2]['label'], 'score' => $vals[5] ?? '', 'max' => $dist[2]['max']],
                        ['key' => 'final', 'label' => $dist[3]['label'], 'score' => $vals[6] ?? '', 'max' => $dist[3]['max']],
                    ];
                    $overallScore = $vals[7] ?? '';
                    $term = $vals[8] ?? '';
                    $academicYear = $vals[9] ?? date('Y');
                } elseif ($colCount >= 9) {
                    // 7-column format: email | name | subject | exam_type | score | term | year
                    $studentEmail = $vals[0] ?? '';
                    $subjectName = $vals[2] ?? '';
                    $scores = [$vals[3] => $vals[4] ?? ''];
                    $term = $vals[5] ?? '';
                    $academicYear = $vals[6] ?? date('Y');
                } else {
                    // legacy 6-column format: email | subject | exam_type | score | term | year
                    $studentEmail = $vals[0] ?? '';
                    $subjectName = $vals[1] ?? '';
                    $scores = [$vals[2] => $vals[3] ?? ''];
                    $term = $vals[4] ?? '';
                    $academicYear = $vals[5] ?? date('Y');
                }

                if (!trim($studentEmail)) continue;

                $studentEmail = trim(strtolower($studentEmail));
                $studentUser = User::where('email', $studentEmail)->where('role', 'student')->first();
                if (!$studentUser) { $errors[] = "صف $rowNum: الطالب '$studentEmail' غير موجود"; continue; }

                $studentModel = Student::where('user_id', $studentUser->id)->first();
                if (!$studentModel) { $errors[] = "صف $rowNum: الطالب '$studentEmail' ليس لديه سجل طالب"; continue; }

                if (!in_array($studentModel->class_id, $teacherSections)) {
                    $errors[] = "صف $rowNum: الطالب '$studentEmail' ليس من صفوفك"; continue;
                }

                // Track this student as found (early tracking)
                if (!in_array($studentModel->id, $foundStudentIds)) {
                    $foundStudentIds[] = $studentModel->id;
                }

                // Track this section as processed
                if (!in_array($studentModel->class_id, $processedSectionIds)) {
                    $processedSectionIds[] = $studentModel->class_id;
                }

                $sectionGradeLevelId = \App\Models\SchoolClass::where('id', $studentModel->class_id)->value('grade_level_id');
                $subject = \App\Models\Subject::where('name', $subjectName)
                    ->when($sectionGradeLevelId, fn($q) => $q->where('grade_level_id', $sectionGradeLevelId))
                    ->first();
                if (!$subject) { $errors[] = "صف $rowNum: المادة '$subjectName' غير موجودة لهذا الصف"; continue; }

                $teachesSubject = \App\Models\Schedule::where('teacher_id', $teacherId)
                    ->where('section_id', $studentModel->class_id)
                    ->where('subject_id', $subject->id)->exists();
                if (!$teachesSubject) {
                    $errors[] = "صف $rowNum: لست مدرّس مادة '$subjectName' لهذا الصف"; continue;
                }

                if ($colCount >= 10) {
                    // Collect exam types that have scores
                    $filledExamTypes = [];
                    foreach ($examScores as $es) {
                        $score = trim($es['score']);
                        if ($score !== '' && $score !== null) {
                            $filledExamTypes[] = $es['label'];
                        }
                    }

                    // Delete old grades
                    $deleteQuery = Grade::where('student_id', $studentModel->id)
                        ->where('subject_id', $subject->id)
                        ->where('teacher_id', $teacherId);
                    if ($labelsDiffer) {
                        // Distribution changed — delete ALL old grades
                        $deleteQuery->delete();
                    } else {
                        // Only delete grades for filled exam types
                        $deleteQuery->whereIn('exam_type', $filledExamTypes)->delete();
                    }

                    $total = 0;
                    $allFilled = true;
                    foreach ($examScores as $i => $es) {
                        $score = trim($es['score']);
                        if ($score === '' || $score === null) {
                            $allFilled = false;
                            continue;
                        }
                        if (!is_numeric($score) || $score < 0) {
                            $errors[] = "صف $rowNum: الدرجة '$score' في '{$es['label']}' غير صالحة"; continue 2;
                        }
                        if ($score > $es['max']) {
                            $errors[] = "صف $rowNum: الدرجة '$score' في '{$es['label']}' تجاوزت الحد {$es['max']}"; continue 2;
                        }
                        Grade::create([
                            'student_id' => $studentModel->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'exam_type' => $es['label'],
                            'score' => $score,
                            'term' => $term,
                            'academic_year' => $academicYear,
                        ]);
                        $created++;
                        $total += $score;
                        if (!in_array($studentModel->id, $studentsWithNewGrades)) {
                            $studentsWithNewGrades[] = $studentModel->id;
                        }
                    }
                    // Only create final grade if ALL exam types are filled
                    if ($allFilled && $total > 0) {
                        Grade::where('student_id', $studentModel->id)
                            ->where('subject_id', $subject->id)
                            ->where('teacher_id', $teacherId)
                            ->where('exam_type', 'الدرجة النهائية')
                            ->delete();

                        Grade::create([
                            'student_id' => $studentModel->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'exam_type' => 'الدرجة النهائية',
                            'score' => $total,
                            'term' => $term,
                            'academic_year' => $academicYear,
                        ]);
                        $created++;
                    }
                    $sectionName = \App\Models\SchoolClass::where('id', $studentModel->class_id)->value('name');
                    $sectionLetter = \App\Models\SchoolClass::where('id', $studentModel->class_id)->value('section');
                    $importedSections[$studentModel->class_id] = $sectionName . ($sectionLetter ? ' - شعبة ' . $sectionLetter : '');
                    $importedSubjects[$subject->id] = $subject->name;
                } else {
                    // Collect exam types that have scores
                    $filledExamTypes = [];
                    foreach ($scores as $examType => $score) {
                        $score = trim($score);
                        if ($score !== '' && $score !== null) {
                            $filledExamTypes[] = $examType;
                        }
                    }

                    // Delete old grades
                    $deleteQuery = Grade::where('student_id', $studentModel->id)
                        ->where('subject_id', $subject->id)
                        ->where('teacher_id', $teacherId);
                    if ($labelsDiffer) {
                        $deleteQuery->delete();
                    } elseif (count($filledExamTypes) > 0) {
                        $deleteQuery->whereIn('exam_type', $filledExamTypes)->delete();
                    }

                    foreach ($scores as $examType => $score) {
                        $score = trim($score);
                        if ($score === '' || $score === null) continue;
                        if (!is_numeric($score) || $score < 0) {
                            $errors[] = "صف $rowNum: الدرجة '$score' في '$examType' غير صالحة"; continue;
                        }
                        if (($examType === 'نهائي' || $examType === 'الدرجة النهائية') && $score > 100) {
                            $errors[] = "صف $rowNum: الدرجة '$score' في '$examType' يجب أن تكون من 0 إلى 100"; continue;
                        }
                        Grade::create([
                            'student_id' => $studentModel->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'exam_type' => $examType,
                            'score' => $score,
                            'term' => $term,
                            'academic_year' => $academicYear,
                        ]);
                        $created++;
                        if (!in_array($studentModel->id, $studentsWithNewGrades)) {
                            $studentsWithNewGrades[] = $studentModel->id;
                        }
                    }
                    $sectionName = \App\Models\SchoolClass::where('id', $studentModel->class_id)->value('name');
                    $sectionLetter = \App\Models\SchoolClass::where('id', $studentModel->class_id)->value('section');
                    $importedSections[$studentModel->class_id] = $sectionName . ($sectionLetter ? ' - شعبة ' . $sectionLetter : '');
                    $importedSubjects[$subject->id] = $subject->name;
                }
            }
        }

        $reader->close();

        // Check for students in imported sections with no grades at all
        $missingStudents = [];
        foreach ($allSectionStudents as $student) {
            if (!in_array($student->class_id, $processedSectionIds)) continue;
            if (!in_array($student->id, $studentsWithNewGrades)) {
                $missingStudents[] = $student->user->name ?? 'طالب #' . $student->id;
            }
        }

        if (count($missingStudents) > 0) {
            $errors[] = '⚠️ طلاب لم تُدرج درجاتهم: ' . implode('، ', $missingStudents);
        }

        return response()->json([
            'message' => 'تم استيراد ' . $created . ' درجة' . (count($errors) ? " مع ملاحظات" : ''),
            'count' => $created,
            'sections' => array_values($importedSections),
            'subjects' => array_values($importedSubjects),
            'errors' => $errors,
        ]);
    }
}
