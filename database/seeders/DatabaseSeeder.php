<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@alebdaa.edu')->exists()) {
            User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@alebdaa.edu',
                'password' => Hash::make('admin123'),
                'phone' => '0592388493',
                'role' => 'admin',
            ]);
        }

        $classNames = ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس'];
        $sectionLetters = ['أ', 'ب', 'ج', 'د'];
        $allClasses = [];
        $allTeachers = [];

        $subjectsData = [
            ['name' => 'الرياضيات', 'periods' => 4],
            ['name' => 'اللغة العربية', 'periods' => 4],
            ['name' => 'اللغة الإنجليزية', 'periods' => 4],
            ['name' => 'العلوم الحياتية', 'periods' => 4],
            ['name' => 'التربية الإسلامية', 'periods' => 3],
            ['name' => 'الدراسات الاجتماعية', 'periods' => 3],
            ['name' => 'التكنولوجيا والحاسوب', 'periods' => 2],
            ['name' => 'التربية الرياضية', 'periods' => 1],
        ];

        $teacherIdx = 0;
        foreach ($subjectsData as $subj) {
            for ($t = 0; $t < 5; $t++) {
                $teacherIdx++;
                $teacherUser = User::firstOrCreate(
                    ['email' => 'teacher' . $teacherIdx . '@alebdaa.edu'],
                    [
                        'name' => 'teacher' . $teacherIdx,
                        'password' => Hash::make('teacher123'),
                        'phone' => '059' . str_pad((2000000 + $teacherIdx), 7, '0', STR_PAD_LEFT),
                        'role' => 'teacher',
                    ]
                );

                $teacher = Teacher::firstOrCreate(
                    ['user_id' => $teacherUser->id],
                    [
                        'qualification' => 'بكالوريوس ' . $subj['name'],
                        'specialization' => $subj['name'],
                        'hire_date' => '2024-09-01',
                        'salary' => 5000,
                    ]
                );

                $allTeachers[] = $teacher;
            }
        }

        $allTeachers = collect($allTeachers);

        foreach ($classNames as $name) {
            $gradeLevel = GradeLevel::firstOrCreate(
                ['name' => $name],
                ['stage' => 'ابتدائي', 'academic_year' => '2025-2026']
            );

            $firstSectionId = null;
            foreach ($sectionLetters as $letter) {
                $class = SchoolClass::firstOrCreate(
                    ['name' => $name, 'grade_level_id' => $gradeLevel->id, 'section' => $letter],
                    [
                        'stage' => 'ابتدائي',
                        'academic_year' => '2025-2026',
                    ]
                );
                $allClasses[] = $class;
                if (!$firstSectionId) $firstSectionId = $class->id;
            }

            foreach ($subjectsData as $subj) {
                Subject::updateOrCreate(
                    ['name' => $subj['name'], 'grade_level_id' => $gradeLevel->id],
                    [
                        'class_id' => $firstSectionId,
                        'periods_per_week' => $subj['periods'],
                        'coefficient' => 1,
                    ]
                );
            }
        }

        $allSubjects = Subject::whereNotNull('grade_level_id')->get();

        $this->command->info(' successful seeding ');
    }
}
