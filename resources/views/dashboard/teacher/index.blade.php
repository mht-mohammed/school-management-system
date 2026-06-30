@extends('layouts.dashboard')

@section('title', __('المعلم'))
@section('page-title', __('لوحة المعلم'))

@section('sidebar')
    <a href="/teacher" class="active">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/teacher/grades">📝 <span>{{ __('الدرجات') }}</span></a>
    <a href="/teacher/schedule">📅 <span>{{ __('جدولي') }}</span></a>
    <a href="/teacher/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/teacher/library">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card"><div class="number" id="statClasses">-</div><div class="label">{{ __('الصفوف') }}</div></div>
        <div class="stat-card"><div class="number" id="statSubjects">-</div><div class="label">{{ __('المواد') }}</div></div>
        <div class="stat-card"><div class="number" id="statStudents">-</div><div class="label">{{ __('الطلاب') }}</div></div>
        <div class="stat-card"><div class="number" id="statGrades">-</div><div class="label">{{ __('الدرجات المسجلة') }}</div></div>
    </div>

    <div class="card">
        <h3>{{ __('🏫 صفوفي') }}</h3>
        <div class="loading" id="loadingClasses">{{ __('⏳ جاري التحميل...') }}</div>
        <div id="classesContent" style="display:none;"></div>
    </div>

    <script>
        apiFetch('/teacher/stats').then(d => {
            setNum('statClasses', d.classes_count);
            setNum('statSubjects', d.subjects_count);
            setNum('statStudents', d.students_count);
            setNum('statGrades', d.grades_count);
        }).catch(() => {
            document.querySelectorAll('.stat-card .number').forEach(el => el.textContent = '!');
        });

        apiFetch('/teacher/classes').then(async data => {
            document.getElementById('loadingClasses').style.display = 'none';
            const div = document.getElementById('classesContent');
            div.style.display = '';

            if (!data || !data.length) {
                div.innerHTML = '<div class="empty">' + __('📭 لا توجد صفوف مخصصة لك بعد') + '</div>';
                return;
            }

            for (const c of data) {
                const studentCount = c.students?.length || 0;
                const secLabel = c.section ? ' - ' + __('شعبة') + ' ' + sectionLabel(c.section) : '';

                let grades = [];
                try {
                    grades = await apiFetch('/teacher/grades?section_id=' + c.id);
                } catch(e) {}

                const gradeMap = {};
                (grades || []).forEach(g => {
                    if (g.exam_type === 'الدرجة النهائية') {
                        gradeMap[g.student_id] = parseFloat(g.score) || 0;
                    }
                });

                const studentRows = (c.students || []).map((s, i) => {
                    const total = gradeMap[s.id];
                    const gradeStr = total !== undefined ? toArabicNum(total.toFixed(0)) : '—';
                    const gradeColor = total !== undefined ? (total >= 50 ? '#157a35' : '#b8232e') : '#ccc';
                    return `<tr>
                        <td style="padding:8px;text-align:right;">${s.user?.name || '-'}</td>
                        <td style="text-align:center;padding:8px;font-weight:700;color:${gradeColor};">${gradeStr}</td>
                    </tr>`;
                }).join('');

                div.innerHTML += `
                    <div style="border:1px solid var(--border-soft);border-radius:var(--radius-md);padding:16px;margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                            <div><strong style="font-size:16px;">📚 ${c.name}${secLabel}</strong></div>
                            <div style="font-size:13px;color:#666;">👥 ${toArabicNum(studentCount)} ${__('طالب')}</div>
                        </div>
                        ${studentCount > 0 ? `
                        <details>
                            <summary style="cursor:pointer;font-size:13px;color:var(--blue-main);font-weight:600;">👤 ${__('عرض الطلاب')} (${toArabicNum(studentCount)})</summary>
                            <table style="margin-top:8px;"><thead><tr><th style="text-align:right;padding:8px;">${__('الطالب')}</th><th style="text-align:center;padding:8px;">${__('الدرجة من 100')}</th></tr></thead><tbody>${studentRows}</tbody></table>
                        </details>` : '<div style="color:#aaa;font-size:13px;">' + __('لا يوجد طلاب') + '</div>'}
                    </div>`;
            }
        }).catch(() => {
            document.getElementById('loadingClasses').textContent = __('❌ تعذر التحميل');
        });
    </script>
@stop
