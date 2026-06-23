<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
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

        $teacherUser = User::where('email', 'teacher@alebdaa.edu')->first();
        if (!$teacherUser) {
            $teacherUser = User::create([
                'name' => 'أحمد محمد',
                'email' => 'teacher@alebdaa.edu',
                'password' => Hash::make('teacher123'),
                'phone' => '0592388494',
                'role' => 'teacher',
            ]);
        }

        if (!Teacher::where('user_id', $teacherUser->id)->exists()) {
            Teacher::create([
                'user_id' => $teacherUser->id,
                'qualification' => 'بكالوريوس رياضيات',
                'specialization' => 'رياضيات',
                'hire_date' => '2024-09-01',
                'salary' => 5000,
            ]);
        }

        $classes = [];
        $classNames = ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس'];
        foreach ($classNames as $i => $name) {
            $class = SchoolClass::firstOrCreate(
                ['name' => $name],
                ['stage' => 'ابتدائي', 'teacher_id' => $teacherUser->id, 'academic_year' => '2025-2026']
            );
            $classes[] = $class;
        }

        $fixedSubjects = [
            'القرآن الكريم',
            'التربية الإسلامية',
            'اللغة العربية',
            'اللغة الإنجليزية',
            'الرياضيات',
            'العلوم',
            'الدراسات الاجتماعية',
            'التاريخ',
            'الجغرافيا',
            'التربية الوطنية',
            'التربية الفنية',
            'التربية الرياضية',
            'التربية المهنية',
            'الحاسوب',
            'العلوم الحياتية',
            'الكيمياء',
            'الفيزياء',
            'الأحياء',
            'اللغة الفرنسية',
            'المهارات الرقمية'
        ];
        foreach ($fixedSubjects as $name) {
            Subject::firstOrCreate(['name' => $name], ['teacher_id' => $teacherUser->id]);
        }

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $times = [['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'], ['11:00', '12:00']];

        foreach ($classes as $ci => $class) {
            foreach ($days as $di => $day) {
                if ($di < count($times)) {
                    $subj = Subject::inRandomOrder()->first();
                    if ($subj) {
                        Schedule::firstOrCreate(
                            ['class_id' => $class->id, 'day_of_week' => $day, 'start_time' => $times[$di][0]],
                            [
                                'subject_id' => $subj->id,
                                'teacher_id' => $teacherUser->id,
                                'end_time' => $times[$di][1],
                                'room' => 'قاعة ' . chr(65 + $ci),
                            ]
                        );
                    }
                }
            }
        }

        $studentData = [
            ['name' => 'علي حسن', 'email' => 'ali@test.com'],
            ['name' => 'سارة أحمد', 'email' => 'sara@test.com'],
            ['name' => 'محمد خالد', 'email' => 'mohamed@test.com'],
            ['name' => 'نور محمود', 'email' => 'noor@test.com'],
        ];

        foreach ($studentData as $i => $sd) {
            $classIdx = $i % count($classes);
            $user = User::firstOrCreate(
                ['email' => $sd['email']],
                [
                    'name' => $sd['name'],
                    'password' => Hash::make('12345678'),
                    'phone' => '059' . str_pad((1000000 + $i), 7, '0', STR_PAD_LEFT),
                    'role' => 'student',
                ]
            );

            $student = Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'class_id' => $classes[$classIdx]->id,
                    'dob' => '2010-0' . ($i + 1) . '-15',
                    'address' => 'عنوان الطالب ' . ($i + 1),
                    'guardian_phone' => '0599999' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'enrollment_date' => '2025-09-01',
                    'status' => 'active',
                ]
            );

            $studentSubjects = Subject::where('class_id', $classes[$classIdx]->id)->get();
            foreach ($studentSubjects as $subj) {
                Grade::firstOrCreate(
                    ['student_id' => $student->id, 'subject_id' => $subj->id, 'exam_type' => 'امتحان شهري', 'term' => 'الأول'],
                    ['teacher_id' => $teacherUser->id, 'score' => rand(60, 100), 'academic_year' => '2025-2026']
                );
                Grade::firstOrCreate(
                    ['student_id' => $student->id, 'subject_id' => $subj->id, 'exam_type' => 'امتحان نهائي', 'term' => 'الأول'],
                    ['teacher_id' => $teacherUser->id, 'score' => rand(50, 100), 'academic_year' => '2025-2026']
                );
            }

            $dates = ['2026-03-01', '2026-03-02', '2026-03-03', '2026-03-04', '2026-03-05'];
            foreach ($dates as $date) {
                Attendance::firstOrCreate(
                    ['student_id' => $student->id, 'date' => $date],
                    [
                        'class_id' => $classes[$classIdx]->id,
                        'teacher_id' => $teacherUser->id,
                        'status' => rand(0, 10) > 1 ? 'present' : 'absent',
                    ]
                );
            }
        }

        $this->command->info('✅ تم seeding البيانات التجريبية بنجاح');
    }
}
