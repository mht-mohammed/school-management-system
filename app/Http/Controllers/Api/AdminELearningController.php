<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\OnlineSession;
use App\Models\SchoolClass;
use App\Models\GradeLevel;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AdminELearningController extends Controller
{
    public function dashboard()
    {
        $sectionsCount = SchoolClass::count();
        $materialsCount = Material::count();
        $quizzesCount = Quiz::count();
        $attemptsCount = QuizAttempt::whereNotNull('completed_at')->count();
        $teachersCount = Teacher::count();

        $teachers = Teacher::with('user', 'subjects')->get()->map(function ($teacher) {
            $materials = Material::where('teacher_id', $teacher->id)->count();
            $quizzes = Quiz::where('teacher_id', $teacher->id)->count();
            $sessions = OnlineSession::where('teacher_id', $teacher->id)->count();
            $attempts = QuizAttempt::whereHas('quiz', fn($q) => $q->where('teacher_id', $teacher->id))
                ->whereNotNull('completed_at')->count();

            $sectionIds = Material::where('teacher_id', $teacher->id)->pluck('section_id')
                ->merge(Quiz::where('teacher_id', $teacher->id)->pluck('section_id'))
                ->merge(OnlineSession::where('teacher_id', $teacher->id)->pluck('section_id'))
                ->unique();

            $sections = SchoolClass::whereIn('id', $sectionIds)->with('gradeLevel')->get()->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'section' => $s->section,
                'grade_level' => $s->gradeLevel?->name ?? '',
                'materials' => Material::where('teacher_id', $teacher->id)->where('section_id', $s->id)->count(),
                'quizzes' => Quiz::where('teacher_id', $teacher->id)->where('section_id', $s->id)->count(),
                'sessions' => OnlineSession::where('teacher_id', $teacher->id)->where('section_id', $s->id)->count(),
            ]);

            return [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? '—',
                'subjects' => $teacher->subjects->pluck('name'),
                'materials' => $materials,
                'quizzes' => $quizzes,
                'sessions' => $sessions,
                'attempts' => $attempts,
                'total' => $materials + $quizzes + $sessions,
                'sections' => $sections,
            ];
        })->sortByDesc('total')->values();

        return response()->json([
            'stats' => compact('sectionsCount', 'materialsCount', 'quizzesCount', 'attemptsCount', 'teachersCount'),
            'teachers' => $teachers,
        ]);
    }

    public function sections()
    {
        $classes = SchoolClass::with('gradeLevel')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'section' => $c->section,
                'grade_level' => $c->gradeLevel?->name ?? '',
                'materials_count' => Material::where('section_id', $c->id)->count(),
                'quizzes_count' => Quiz::where('section_id', $c->id)->count(),
                'sessions_count' => OnlineSession::where('section_id', $c->id)->count(),
            ];
        });

        return response()->json($classes);
    }

    // --- Materials CRUD ---
    public function materials($sectionId)
    {
        $materials = Material::where('section_id', $sectionId)
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
            'file_path' => 'nullable|string',
            'link' => 'nullable|url',
        ]);

        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = null;

        $material = Material::create($validated);

        return response()->json(['message' => '✅ تمت إضافة المادة', 'material' => $material], 201);
    }

    public function updateMaterial(Request $request, Material $material)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|string',
            'link' => 'nullable|url',
        ]);

        $material->update($validated);

        return response()->json(['message' => '✅ تم تحديث المادة', 'material' => $material]);
    }

    public function destroyMaterial(Material $material)
    {
        $material->delete();
        return response()->json(['message' => '🗑️ تم حذف المادة']);
    }

    // --- Quizzes CRUD ---
    public function quizzes($sectionId)
    {
        $quizzes = Quiz::where('section_id', $sectionId)
            ->with('questions')
            ->withCount('attempts')
            ->with('teacher')
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
        ]);

        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = null;

        $quiz = Quiz::create($validated);

        return response()->json(['message' => '✅ تمت إضافة الاختبار', 'quiz' => $quiz], 201);
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_at',
        ]);

        $quiz->update($validated);

        return response()->json(['message' => '✅ تم تحديث الاختبار', 'quiz' => $quiz]);
    }

    public function destroyQuiz(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json(['message' => '🗑️ تم حذف الاختبار']);
    }

    // --- Quiz Questions ---
    public function questions(Quiz $quiz)
    {
        return response()->json($quiz->questions()->orderBy('sort_order')->get());
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:mc,tf,text',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable',
            'marks' => 'nullable|integer|min:1',
        ]);

        $validated['quiz_id'] = $quiz->id;
        $validated['marks'] = $validated['marks'] ?? 1;

        $question = QuizQuestion::create($validated);

        return response()->json(['message' => '✅ تمت إضافة السؤال', 'question' => $question], 201);
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return response()->json(['message' => '🗑️ تم حذف السؤال']);
    }

    // --- Quiz Attempts ---
    public function quizAttempts(Quiz $quiz)
    {
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->whereNotNull('completed_at')
            ->with('student.user')
            ->get()
            ->groupBy('student_id')
            ->map(function ($group) {
                $sorted = $group->sortByDesc('completed_at');
                $markedBest = $sorted->firstWhere('is_best', true);
                $best = $markedBest ?? $sorted->sortByDesc('score')->first();

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

        return response()->json(['attempts' => $attempts]);
    }

    public function gradeAttempt(Request $request, QuizAttempt $attempt)
    {
        $validated = $request->validate([
            'teacher_marks' => 'required|array',
            'teacher_marks.*.question_id' => 'required',
            'teacher_marks.*.marks' => 'required|numeric|min:0',
        ]);

        $answers = $attempt->answers ?? [];
        $totalScore = 0;

        foreach ($answers as &$ans) {
            if ($ans['type'] !== 'text') {
                $totalScore += isset($ans['is_correct']) && $ans['is_correct'] ? $ans['marks'] : 0;
                continue;
            }

            foreach ($validated['teacher_marks'] as $grade) {
                if ($ans['question_id'] == $grade['question_id']) {
                    $question = QuizQuestion::find($ans['question_id']);
                    $maxMarks = $question ? $question->marks : $ans['marks'];
                    $ans['teacher_marks'] = min(floatval($grade['marks']), $maxMarks);
                    $totalScore += $ans['teacher_marks'];
                    break;
                }
            }
        }
        unset($ans);

        $attempt->update([
            'answers' => $answers,
            'score' => $totalScore,
            'graded' => true,
        ]);

        return response()->json(['message' => '✅ تم حفظ التصحيح', 'score' => $totalScore]);
    }

    public function setBestAttempt(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);

        QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('student_id', $attempt->student_id)
            ->update(['is_best' => false]);

        $attempt->update(['is_best' => true]);

        return response()->json(['message' => '🏆 تم تحديد هذه المحاولة كأفضل محاولة']);
    }

    public function toggleVisibility(QuizAttempt $attempt)
    {
        $attempt->update(['visible' => !$attempt->visible]);

        return response()->json([
            'message' => $attempt->visible ? '👁️ تم عرض النتيجة' : '🚫 تم إخفاء النتيجة',
            'visible' => $attempt->visible,
        ]);
    }

    public function deleteAttempt(QuizAttempt $attempt)
    {
        $quiz = $attempt->quiz;

        QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('student_id', $attempt->student_id)
            ->where('is_best', true)
            ->where('id', '!=', $attempt->id)
            ->update(['is_best' => false]);

        $attempt->delete();

        return response()->json(['message' => '🗑️ تم حذف المحاولة بنجاح']);
    }

    // --- Sessions CRUD ---
    public function sessions($sectionId)
    {
        $sessions = OnlineSession::where('section_id', $sectionId)
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
            'url' => 'required|url',
            'scheduled_at' => 'nullable|date',
        ]);

        $validated['section_id'] = $sectionId;
        $validated['teacher_id'] = null;

        $session = OnlineSession::create($validated);

        return response()->json(['message' => '✅ تمت إضافة الحصة', 'session' => $session], 201);
    }

    public function updateSession(Request $request, OnlineSession $session)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:meet,classroom,video',
            'url' => 'required|url',
            'scheduled_at' => 'nullable|date',
        ]);

        $session->update($validated);

        return response()->json(['message' => '✅ تم تحديث الحصة', 'session' => $session]);
    }

    public function destroySession(OnlineSession $session)
    {
        $session->delete();
        return response()->json(['message' => '🗑️ تم حذف الحصة']);
    }
}
