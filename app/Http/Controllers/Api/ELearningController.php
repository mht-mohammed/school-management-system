<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\OnlineSession;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ELearningController extends Controller
{
    // --- Teacher's sections ---
    public function teacherSections(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json([]);
        }

        $sectionIds = collect();

        $scheduleSections = \App\Models\Schedule::where('teacher_id', $teacher->user_id)
            ->pluck('section_id');
        $sectionIds = $sectionIds->merge($scheduleSections);

        $classTeacherSections = \DB::table('class_teacher')
            ->where('teacher_id', $teacher->id)
            ->pluck('class_id');
        $sectionIds = $sectionIds->merge($classTeacherSections);

        $subjectIds = \DB::table('subject_teacher')
            ->where('user_id', $user->id)
            ->pluck('subject_id');
        if ($subjectIds->isNotEmpty()) {
            $gradeLevelIds = \App\Models\Subject::whereIn('id', $subjectIds)
                ->pluck('grade_level_id');
            $subjectSections = SchoolClass::whereIn('grade_level_id', $gradeLevelIds)
                ->pluck('id');
            $sectionIds = $sectionIds->merge($subjectSections);
        }

        $sectionIds = $sectionIds->unique()->values();

        $sections = SchoolClass::whereIn('id', $sectionIds)->get();

        $sectionsWithInfo = $sections->map(function ($section) use ($teacher) {
            $gradeLevel = \App\Models\GradeLevel::find($section->grade_level_id);
            $materialsCount = Material::where('section_id', $section->id)->where('teacher_id', $teacher->id)->count();
            $quizzesCount = Quiz::where('section_id', $section->id)->where('teacher_id', $teacher->id)->count();
            $sessionsCount = OnlineSession::where('section_id', $section->id)->where('teacher_id', $teacher->id)->count();

            return [
                'id' => $section->id,
                'name' => $section->name,
                'section' => $section->section,
                'grade_level' => $gradeLevel?->name ?? '',
                'materials_count' => $materialsCount,
                'quizzes_count' => $quizzesCount,
                'sessions_count' => $sessionsCount,
            ];
        });

        return response()->json($sectionsWithInfo);
    }

    // --- Materials ---
    public function materials(Request $request, $sectionId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json([]);

        $materials = Material::where('section_id', $sectionId)
            ->where('teacher_id', $teacher->id)
            ->with('teacher.user')
            ->latest()
            ->get();

        return response()->json($materials);
    }

    public function storeMaterial(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
            'link' => 'nullable|string|max:500',
        ]);

        $teacher = $request->user()->teacher;
        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = $teacher->id;

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $material = Material::create($validated);

        return response()->json([
            'message' => '✅ تمت إضافة المادة',
            'material' => $material,
        ], 201);
    }

    public function destroyMaterial(Request $request, Material $material)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $material->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();
        return response()->json(['message' => '🗑️ تم حذف المادة']);
    }

    // --- Quizzes ---
    public function quizzes(Request $request, $sectionId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json([]);

        $quizzes = Quiz::where('section_id', $sectionId)
            ->where('teacher_id', $teacher->id)
            ->with(['questions', 'attempts.student.user', 'teacher.user'])
            ->latest()
            ->get();

        return response()->json($quizzes);
    }

    public function storeQuiz(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_at',
        ]);

        $teacher = $request->user()->teacher;
        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = $teacher->id;

        $quiz = Quiz::create($validated);

        return response()->json([
            'message' => '✅ تم إنشاء الاختبار',
            'quiz' => $quiz,
        ], 201);
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_at',
        ]);

        $quiz->update($validated);

        return response()->json([
            'message' => '✅ تم تحديث الاختبار',
            'quiz' => $quiz,
        ]);
    }

    public function destroyQuiz(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $quiz->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $quiz->delete();
        return response()->json(['message' => '🗑️ تم حذف الاختبار']);
    }

    // --- Questions ---
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $quiz->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:mc,tf,text',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'marks' => 'nullable|integer|min:1',
        ]);

        $validated['quiz_id'] = $quiz->id;
        $validated['sort_order'] = $quiz->questions()->max('sort_order') + 1;
        $validated['marks'] = $validated['marks'] ?? 1;

        $question = QuizQuestion::create($validated);

        return response()->json([
            'message' => '✅ تمت إضافة السؤال',
            'question' => $question,
        ], 201);
    }

    public function destroyQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $quiz->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $question->delete();
        return response()->json(['message' => '🗑️ تم حذف السؤال']);
    }

    // --- Quiz Attempt (Student) ---
    public function startQuiz(Request $request, Quiz $quiz)
    {
        $student = $request->user()->student;
        if (!$student) return response()->json(['error' => true, 'message' => 'غير مصرح']);

        if ($quiz->scheduled_at && now()->lessThan($quiz->scheduled_at)) {
            return response()->json([
                'error' => true,
                'not_started' => true,
                'message' => '🔒 ' . __('هذا الاختبار لم يبدأ بعد'),
                'scheduled_at' => $quiz->scheduled_at->toIso8601String(),
                'time_remaining' => intval(now()->diffInSeconds($quiz->scheduled_at, false)),
            ]);
        }

        if ($quiz->scheduled_end && now()->greaterThan($quiz->scheduled_end)) {
            return response()->json([
                'error' => true,
                'ended' => true,
                'message' => '🔒 ' . __('انتهى وقت هذا الاختبار'),
            ]);
        }

        $completedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        $existing = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('completed_at')
            ->first();

        if ($existing) {
            if ($quiz->time_limit && $existing->started_at) {
                $expiresAt = $existing->started_at->addMinutes($quiz->time_limit);
                if (now()->greaterThan($expiresAt)) {
                    $existing->update([
                        'completed_at' => now(),
                        'score' => 0,
                        'total_marks' => $quiz->questions()->sum('marks'),
                        'answers' => [],
                        'graded' => true,
                    ]);
                    return response()->json([
                        'expired' => true,
                        'message' => '⏱️ انتهت مدة المحاولة السابقة',
                        'completed_attempts' => $completedAttempts,
                        'max_attempts' => $quiz->max_attempts,
                    ]);
                }
            }
            return response()->json([
                'attempt' => $existing,
                'quiz' => $quiz->load('questions'),
                'resume' => true,
                'completed_attempts' => $completedAttempts,
                'max_attempts' => $quiz->max_attempts,
            ]);
        }

        if ($quiz->max_attempts && $completedAttempts >= $quiz->max_attempts) {
            return response()->json([
                'error' => true,
                'message' => "❌ لقد استنفدت جميع المحاولات ({$quiz->max_attempts})",
                'completed_attempts' => $completedAttempts,
                'max_attempts' => $quiz->max_attempts,
            ]);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);

        return response()->json([
            'attempt' => $attempt,
            'quiz' => $quiz->load('questions'),
            'resume' => false,
            'completed_attempts' => $completedAttempts,
            'max_attempts' => $quiz->max_attempts,
        ]);
    }

    public function submitQuiz(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $student = $request->user()->student;
        if (!$student || $attempt->student_id !== $student->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح']);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $questions = $quiz->questions()->get();
        $score = 0;
        $totalMarks = 0;
        $answers = [];
        $hasText = false;

        foreach ($questions as $q) {
            $totalMarks += $q->marks;
            $studentAnswer = $validated['answers'][$q->id] ?? null;
            $isCorrect = false;

            if ($q->type === 'mc' || $q->type === 'tf') {
                $isCorrect = ($studentAnswer === $q->correct_answer);
                if ($isCorrect) $score += $q->marks;
            } else {
                $hasText = true;
            }

            $answers[] = [
                'question_id' => $q->id,
                'answer' => $studentAnswer,
                'is_correct' => $isCorrect,
                'type' => $q->type,
                'marks' => $q->marks,
                'teacher_marks' => null,
            ];
        }

        $autoGraded = !$hasText;

        $attempt->update([
            'score' => $score,
            'total_marks' => $totalMarks,
            'answers' => $answers,
            'graded' => $autoGraded,
            'visible' => false,
            'has_text_questions' => $hasText,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => $hasText ? '📤 تم تسليم الاختبار — بانتظار مراجعة المعلم' : '✅ تم تسليم الاختبار',
            'score' => $score,
            'total_marks' => $totalMarks,
            'answers' => $answers,
            'has_text_questions' => $hasText,
        ]);
    }

    public function studentAttempt(Request $request, $attemptId)
    {
        $student = $request->user()->student;
        if (!$student) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempt = QuizAttempt::with('quiz')->where('id', $attemptId)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->firstOrFail();

        if (!$attempt->visible) {
            return response()->json([
                'error' => true,
                'hidden' => true,
                'message' => '⏳ {{ __("النتيجة لم تُعرض بعد") }}',
            ]);
        }

        $questions = $attempt->quiz->questions()->get();

        return response()->json([
            'quiz_id' => $attempt->quiz_id,
            'quiz_title' => $attempt->quiz->title,
            'score' => $attempt->score,
            'total_marks' => $attempt->total_marks,
            'answers' => $attempt->answers,
            'has_text_questions' => $attempt->has_text_questions,
            'visible' => $attempt->visible,
            'questions' => $questions,
        ]);
    }

    // --- Online Sessions ---
    public function sessions(Request $request, $sectionId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json([]);

        $sessions = OnlineSession::where('section_id', $sectionId)
            ->where('teacher_id', $teacher->id)
            ->with('teacher.user')
            ->latest('scheduled_at')
            ->get();

        return response()->json($sessions);
    }

    public function storeSession(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:meet,classroom,video',
            'url' => 'required|url|max:500',
            'scheduled_at' => 'nullable|date',
        ]);

        $teacher = $request->user()->teacher;
        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = $teacher->id;

        $session = OnlineSession::create($validated);

        return response()->json([
            'message' => '✅ تمت إضافة الحصة الإلكترونية',
            'session' => $session,
        ], 201);
    }

    public function destroySession(Request $request, OnlineSession $session)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher || $session->teacher_id != $teacher->id) {
            return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);
        }

        $session->delete();
        return response()->json(['message' => '🗑️ تم حذف الحصة الإلكترونية']);
    }

    // --- Teacher: Review Attempts ---
    public function quizAttempts(Request $request, $quizId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $quiz = Quiz::with('questions')->findOrFail($quizId);
        if ($quiz->teacher_id != $teacher->id) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempts = QuizAttempt::where('quiz_id', $quizId)
            ->whereNotNull('completed_at')
            ->with('student.user')
            ->get()
            ->groupBy('student_id')
            ->map(function ($group) {
                $sorted = $group->sortByDesc('completed_at');

                $markedBest = $sorted->firstWhere('is_best', true);
                if ($markedBest) {
                    $best = $markedBest;
                } else {
                    $best = $sorted->sortByDesc('score')->first();
                }

                return [
                    'student_id' => $best->student_id,
                    'student_name' => $best->student->user->name ?? '—',
                    'student_number' => $best->student->student_number ?? '—',
                    'best_score' => $best->score,
                    'best_total' => $best->total_marks,
                    'best_id' => $best->id,
                    'best_visible' => $best->visible,
                    'best_has_text' => $best->has_text_questions,
                    'attempts' => $sorted->map(fn($a) => [
                        'id' => $a->id,
                        'score' => $a->score,
                        'total_marks' => $a->total_marks,
                        'answers' => $a->answers,
                        'visible' => $a->visible,
                        'has_text_questions' => $a->has_text_questions,
                        'graded' => $a->graded,
                        'is_best' => $a->is_best,
                        'completed_at' => $a->completed_at?->toISOString(),
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'quiz' => ['id' => $quiz->id, 'title' => $quiz->title, 'questions' => $quiz->questions],
            'attempts' => $attempts,
        ]);
    }

    public function gradeAttempt(Request $request, $attemptId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $validated = $request->validate([
            'teacher_marks' => 'required|array',
        ]);

        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
        if ($attempt->quiz->teacher_id != $teacher->id) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $answers = $attempt->answers ?? [];
        foreach ($validated['teacher_marks'] as $grade) {
            foreach ($answers as &$ans) {
                if ($ans['question_id'] == $grade['question_id'] && $ans['type'] === 'text') {
                    $ans['teacher_marks'] = (float)($grade['marks'] ?? 0);
                    $ans['is_correct'] = ($ans['teacher_marks'] > 0);
                    break;
                }
            }
        }
        unset($ans);

        $totalScore = 0;
        foreach ($answers as $ans) {
            if ($ans['type'] !== 'text') {
                if (!empty($ans['is_correct'])) $totalScore += $ans['marks'] ?? 0;
            } else {
                $totalScore += $ans['teacher_marks'] ?? 0;
            }
        }

        $attempt->update([
            'answers' => $answers,
            'score' => $totalScore,
            'graded' => true,
        ]);

        return response()->json(['message' => '✅ تم التصحيح', 'score' => $totalScore]);
    }

    public function toggleAttemptVisibility(Request $request, $attemptId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
        if ($attempt->quiz->teacher_id != $teacher->id) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempt->update(['visible' => !$attempt->visible]);

        return response()->json([
            'message' => $attempt->visible ? '👁️ تم عرض النتيجة للطالب' : '🚫 تم إخفاء النتيجة',
            'visible' => $attempt->visible,
        ]);
    }

    public function setBestAttempt(Request $request, $attemptId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
        if ($attempt->quiz->teacher_id != $teacher->id) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('student_id', $attempt->student_id)
            ->update(['is_best' => false]);

        $attempt->update(['is_best' => true]);

        return response()->json([
            'message' => '🏆 تم تحديد هذه المحاولة كأفضل محاولة',
            'is_best' => true,
            'has_text_questions' => $attempt->has_text_questions,
        ]);
    }

    public function deleteAttempt(Request $request, $attemptId)
    {
        $teacher = $request->user()->teacher;
        if (!$teacher) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
        if ($attempt->quiz->teacher_id != $teacher->id) return response()->json(['error' => true, 'message' => 'غير مصرح'], 403);

        QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('student_id', $attempt->student_id)
            ->where('is_best', true)
            ->where('id', '!=', $attempt->id)
            ->update(['is_best' => false]);

        $attempt->delete();

        return response()->json(['message' => '🗑️ تم حذف المحاولة بنجاح']);
    }

    // --- Student view ---
    public function studentSections(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) return response()->json(['sections' => []]);

        $sections = SchoolClass::whereHas('students', fn($q) => $q->where('students.id', $student->id))->get();

        return response()->json($sections);
    }

    public function studentSectionContent(Request $request, $sectionId)
    {
        $materials = Material::where('section_id', $sectionId)->latest()->get();
        $quizzes = Quiz::where('section_id', $sectionId)->with('questions')->get();
        $sessions = OnlineSession::where('section_id', $sectionId)->latest('scheduled_at')->get();

        $student = $request->user()->student;
        if ($student) {
            foreach ($quizzes as $quiz) {
                $completedAttempts = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->whereNotNull('completed_at')
                    ->count();

                $latestAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->latest()
                    ->first();

                $bestVisible = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->whereNotNull('completed_at')
                    ->where('visible', true)
                    ->where('is_best', true)
                    ->first();

                if (!$bestVisible) {
                    $bestVisible = QuizAttempt::where('quiz_id', $quiz->id)
                        ->where('student_id', $student->id)
                        ->whereNotNull('completed_at')
                        ->where('visible', true)
                        ->orderByDesc('score')
                        ->first();
                }

                $displayAttempt = $bestVisible ?? $latestAttempt;

                $quiz->my_attempt = $displayAttempt ? [
                    'id' => $displayAttempt->id,
                    'score' => $displayAttempt->score,
                    'total_marks' => $displayAttempt->total_marks,
                    'completed_at' => $displayAttempt->completed_at?->toISOString(),
                    'visible' => $displayAttempt->visible,
                    'has_text_questions' => $displayAttempt->has_text_questions,
                ] : null;

                $quiz->completed_attempts = $completedAttempts;
                $quiz->max_attempts = $quiz->max_attempts;
                $quiz->can_attempt = !$quiz->max_attempts || $completedAttempts < $quiz->max_attempts;
            }
        }

        return response()->json([
            'materials' => $materials,
            'quizzes' => $quizzes,
            'sessions' => $sessions,
        ]);
    }
}
