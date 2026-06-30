<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function children(Request $request)
    {
        $parent = $request->user()->parent;

        return response()->json(
            $parent->children()->with(['user', 'class'])->get()
        );
    }

    public function childGrades(Request $request, Student $student)
    {
        $parent = $request->user()->parent;
        if (!$parent || $student->parent_id !== $parent->id) {
            abort(403, 'ليس لديك صلاحية الوصول لبيانات هذا الطالب');
        }

        return response()->json(
            $student->grades()->with('subject')->latest()->get()
        );
    }

    public function childAttendance(Request $request, Student $student)
    {
        $parent = $request->user()->parent;
        if (!$parent || $student->parent_id !== $parent->id) {
            abort(403, 'ليس لديك صلاحية الوصول لبيانات هذا الطالب');
        }

        return response()->json(
            $student->attendance()->with('class')->latest()->get()
        );
    }
}
