<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class TemplateController extends Controller
{
    private function csvResponse($filename, $rows)
    {
        $bom = "\xEF\xBB\xBF";
        $content = $bom;
        foreach ($rows as $r) {
            $content .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $r)) . "\n";
        }
        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function students()
    {
        return $this->csvResponse('students_template.csv', [
            ['✏️ الاسم الكامل للطالب', '📧 البريد الإلكتروني للطالب', '🔑 كلمة المرور', '📞 رقم جوال الطالب', '👪 رقم جوال ولي الأمر (لإنشاء حسابه)', '🏫 اسم الصف (كما في النظام)'],
            ['أحمد محمد أحمد', 'ahmed@test.com', '12345678', '0501234567', '0555000011', 'الصف الأول الابتدائي'],
            ['سارة علي حسن', 'sara@test.com', '12345678', '0507654321', '0555000022', 'الصف الثاني الابتدائي'],
            ['محمد خالد عمر', 'mohamed@test.com', '', '0512345678', '0555000033', 'الصف الأول الابتدائي'],
        ]);
    }

    public function grades()
    {
        return $this->csvResponse('grades_template.csv', [
            ['📧 البريد الإلكتروني للطالب', '📚 اسم المادة (كما في النظام)', '📝 نوع الامتحان', '💯 الدرجة (0-100)', '📖 الفصل', '📅 العام الدراسي'],
            ['ahmed@test.com', 'الرياضيات', 'نهائي', '95', 'الأول', '2025-2026'],
            ['ahmed@test.com', 'الرياضيات', 'شهري', '88', 'الأول', '2025-2026'],
            ['sara@test.com', 'العلوم', 'نهائي', '92', 'الأول', '2025-2026'],
            ['sara@test.com', 'العلوم', 'شهري', '85', 'الأول', '2025-2026'],
        ]);
    }

    public function attendance()
    {
        return $this->csvResponse('attendance_template.csv', [
            ['📧 البريد الإلكتروني للطالب', '🏫 اسم الصف (كما في النظام)', '📅 التاريخ (YYYY-MM-DD)', '✅ الحالة (حاضر/غائب)', '📝 ملاحظات (اختياري)'],
            ['ahmed@test.com', 'الصف الأول الابتدائي', '2026-06-07', 'حاضر', ''],
            ['sara@test.com', 'الصف الثاني الابتدائي', '2026-06-07', 'غائب', 'غير مدع'],
            ['mohamed@test.com', 'الصف الأول الابتدائي', '2026-06-07', 'غائب', 'تأخر 10 دقائق'],
        ]);
    }
}
