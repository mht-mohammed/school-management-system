@extends('layouts.dashboard')

@section('title', __('تقرير الدرجات'))
@section('page-title', __('تقرير الدرجات - كشف علامات الطلاب'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/admin/library">📖 <span>{{ __('المكتبة') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report" class="active">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
    <div class="card">
        <h3>{{ __('كشف علامات الطلاب') }}</h3>
        <p style="color:#666;font-size:14px;margin-bottom:15px;">{{ __('اختر الصف لعرض علامات الطلاب في جميع المواد مع المعدل') }}</p>
        <div style="display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px;align-items:flex-end;">
            <div><label style="font-weight:700;font-size:15px;color:var(--blue-main);">{{ __('🏫 الصف') }}</label><select id="filterClass" style="padding:10px;border:2px solid var(--blue-main);border-radius:8px;min-width:200px;font-weight:600;"><option value="">{{ __('-- اختر الصف --') }}</option></select></div>
            <div><label>{{ __('📚 المادة') }}</label><select id="filterSubject" onchange="loadReport()" style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:150px;"><option value="">{{ __('جميع المواد') }}</option></select></div>
            <div><label>{{ __('👤 الطالب') }}</label><select id="filterStudent" onchange="loadReport()" style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:180px;"><option value="">{{ __('جميع الطلاب') }}</option></select></div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-primary" onclick="exportAllCSV()">{{ __('📥 تصدير الكل') }}</button>
                <button class="btn btn-secondary" onclick="exportStudentCSV()" id="exportStudentBtn" style="display:none;">{{ __('📥 تصدير هذا الطالب') }}</button>
            </div>
        </div>
        <div id="classPrompt" style="text-align:center;padding:40px;color:#888;font-size:16px;">{{ __('👈 اختر الصف من القائمة لعرض العلامات') }}</div>
        <div class="loading" id="loadingReport" style="display:none;">{{ __('جاري التحميل...') }}</div>
        <div id="reportContent" style="display:none;"></div>
    </div>

    <script>
        let allGrades = [];
        let allSubjects = [];
        let allClasses = [];
        let allStudents = [];
        let currentClassStudents = [];

        apiFetch('/admin/classes').then(list => {
            allClasses = list || [];
            const sel = document.getElementById('filterClass');
            sel.innerHTML = '<option value="">' + __('-- اختر الصف --') + '</option>';
            allClasses.forEach(c => { sel.innerHTML += `<option value="${c.id}">${c.name} ${c.section ? __('شعبة ') + sectionLabel(c.section) : ''}</option>`; });
        });

        apiFetch('/admin/subjects').then(list => {
            allSubjects = list || [];
        });

        apiFetch('/admin/students').then(list => {
            allStudents = list || [];
        });

        document.getElementById('filterClass').addEventListener('change', function() {
            const classId = this.value;
            document.getElementById('classPrompt').style.display = classId ? 'none' : '';
            document.getElementById('loadingReport').style.display = classId ? '' : 'none';
            document.getElementById('reportContent').style.display = 'none';

            // Reset dependent filters
            document.getElementById('filterStudent').innerHTML = '<option value="">' + __('جميع الطلاب') + '</option>';
            const subjSel = document.getElementById('filterSubject');
            subjSel.innerHTML = '<option value="">' + __('جميع المواد') + '</option>';

            if (!classId) return;

            // Populate subjects
            const cls = allClasses.find(c => c.id == classId);
            if (cls && cls.grade_level_id) {
                const seen = new Set();
                allSubjects.filter(s => s.grade_level_id == cls.grade_level_id).forEach(s => {
                    if (!seen.has(s.id)) { seen.add(s.id); subjSel.innerHTML += `<option value="${s.id}">${s.name}</option>`; }
                });
            }

            // Populate students
            currentClassStudents = allStudents.filter(s => s.class_id == classId);
            currentClassStudents.forEach(s => {
                document.getElementById('filterStudent').innerHTML += `<option value="${s.id}">${s.user?.name || '—'}</option>`;
            });

            document.getElementById('exportStudentBtn').style.display = '';
            loadReport();
        });

        document.getElementById('filterSubject').addEventListener('change', loadReport);
        document.getElementById('filterStudent').addEventListener('change', loadReport);

        function loadReport() {
            const classId = document.getElementById('filterClass').value;
            if (!classId) return;
            const studentId = document.getElementById('filterStudent').value;
            const subjectId = document.getElementById('filterSubject').value;

            document.getElementById('loadingReport').textContent = __('جاري التحميل...');
            document.getElementById('loadingReport').style.display = '';
            document.getElementById('reportContent').style.display = 'none';

            let params = { class_id: classId };
            if (studentId) params.student_id = studentId;
            if (subjectId) params.subject_id = subjectId;

            apiFetch('/admin/grades-report?' + new URLSearchParams(params)).then(grades => {
                allGrades = grades || [];
                document.getElementById('loadingReport').style.display = 'none';
                const div = document.getElementById('reportContent');

                if (!allGrades.length) {
                    div.innerHTML = '<div class="empty">' + __('لا توجد درجات لهذا الصف') + '</div>';
                    div.style.display = '';
                    return;
                }

                // Group by student_id -> subject_id
                const studentGroups = {};
                allGrades.forEach(g => {
                    const stuId = g.student_id;
                    const subId = g.subject_id;
                    if (!studentGroups[stuId]) studentGroups[stuId] = {};
                    if (!studentGroups[stuId][subId]) studentGroups[stuId][subId] = [];
                    studentGroups[stuId][subId].push(g);
                });

                let html = '<div style="display:flex;flex-direction:column;gap:24px;">';

                Object.entries(studentGroups).forEach(([stuId, subjects]) => {
                    const firstGrade = subjects[Object.keys(subjects)[0]][0];
                    const stuName = firstGrade.student?.user?.name || '—';
                    const stuAvatar = firstGrade.student?.user?.avatar;

                    const allExamTypes = [];
                    const subjectRows = [];

                    Object.entries(subjects).forEach(([subId, gradeList]) => {
                        const subjName = subjectName(gradeList[0].subject?.name);
                        const examMap = {};
                        let finalScore = null;
                        gradeList.forEach(g => {
                            examMap[g.exam_type] = g.score;
                            if (g.exam_type === 'الدرجة النهائية') finalScore = g.score;
                            if (!allExamTypes.includes(g.exam_type)) allExamTypes.push(g.exam_type);
                        });
                        subjectRows.push({ name: subjName, exams: examMap, final: finalScore });
                    });

                    const finals = subjectRows.map(r => r.final).filter(f => f !== null);
                    const avg = finals.length ? (finals.reduce((a, b) => a + b, 0) / finals.length).toFixed(1) : '—';

                    html += `<div style="background:#f8faff;border-radius:var(--radius-md);padding:18px;border:1px solid #e0e8f0;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                            ${stuAvatar ? `<img src="/storage/${stuAvatar}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">` : '<div style="width:36px;height:36px;border-radius:50%;background:var(--blue-main);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;">🎓</div>'}
                            <div><strong style="font-size:16px;">${stuName}</strong></div>
                            <span style="margin-right:auto;font-size:14px;color:${avg !== '—' ? '#28a745' : '#888'};font-weight:700;">${avg !== '—' ? __('المعدل: ') + toArabicNum(avg) : __('لا يوجد معدل')}</span>
                        </div>
                        <table style="margin-top:8px;width:100%;">
                            <thead><tr><th style="text-align:right;min-width:120px;">${__('المادة')}</th>${allExamTypes.map(e => `<th style="text-align:center;min-width:70px;">${__(e)}</th>`).join('')}</tr></thead>
                            <tbody>`;
                    subjectRows.forEach(row => {
                        html += `<tr><td style="text-align:right;font-weight:600;">${row.name}</td>`;
                        allExamTypes.forEach(e => {
                            const val = row.exams[e];
                            html += `<td style="text-align:center;">${val !== undefined ? toArabicNum(val) : '<span style="color:#ccc;">—</span>'}</td>`;
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                });

                html += '</div>';
                const totalStudents = Object.keys(studentGroups).length;
                html = `<p style="margin-bottom:15px;font-size:14px;color:#666;">${__('عدد الطلاب: ')}<strong>${toArabicNum(totalStudents)}</strong> | ${__('إجمالي الدرجات: ')}<strong>${toArabicNum(allGrades.length)}</strong></p>` + html;
                div.innerHTML = html;
                div.style.display = '';
            });
        }

        function exportAllCSV() {
            if (!allGrades.length) { showToast(__('⚠️ لا توجد بيانات للتصدير'), 'error'); return; }

            const groups = {};
            allGrades.forEach(g => {
                const stuId = g.student_id;
                if (!groups[stuId]) groups[stuId] = { name: g.student?.user?.name || '—', subjects: {} };
                const subId = g.subject_id;
                if (!groups[stuId].subjects[subId]) groups[stuId].subjects[subId] = { name: subjectName(g.subject?.name), exams: {} };
                groups[stuId].subjects[subId].exams[g.exam_type] = g.score;
            });

            let csv = __('الطالب,المادة');
            const allTypes = new Set();
            Object.values(groups).forEach(s => Object.values(s.subjects).forEach(sub => Object.keys(sub.exams).forEach(e => allTypes.add(e))));
            const types = ['الدرجة النهائية', ...Array.from(allTypes).filter(t => t !== 'الدرجة النهائية')];
            csv += types.map(t => ',' + t).join('') + ',' + __('المعدل') + '\n';

            Object.entries(groups).forEach(([stuId, stu]) => {
                let studentFinals = [];
                Object.entries(stu.subjects).forEach(([subId, sub]) => {
                    csv += `"${stu.name}","${sub.name}"`;
                    types.forEach(t => csv += ',' + (sub.exams[t] !== undefined ? sub.exams[t] : ''));
                    const final = sub.exams['الدرجة النهائية'];
                    if (final !== undefined) studentFinals.push(parseFloat(final));
                    csv += ',' + (studentFinals.length ? (studentFinals.reduce((a,b) => a+b, 0) / studentFinals.length).toFixed(1) : '') + '\n';
                });
            });

            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
            a.download = __('كشف-علامات-الكل.csv'); a.click();
        }

        function exportStudentCSV() {
            const studentId = document.getElementById('filterStudent').value;
            if (!studentId) { showToast(__('⚠️ اختر الطالب أولاً'), 'error'); return; }

            const studentGrades = allGrades.filter(g => g.student_id == studentId);
            if (!studentGrades.length) { showToast(__('⚠️ لا توجد درجات لهذا الطالب'), 'error'); return; }

            const stuName = studentGrades[0]?.student?.user?.name || '—';
            const groups = {};
            studentGrades.forEach(g => {
                const subId = g.subject_id;
                if (!groups[subId]) groups[subId] = { name: subjectName(g.subject?.name), exams: {} };
                groups[subId].exams[g.exam_type] = g.score;
            });

            const allTypes = new Set();
            Object.values(groups).forEach(sub => Object.keys(sub.exams).forEach(e => allTypes.add(e)));
            const types = ['الدرجة النهائية', ...Array.from(allTypes).filter(t => t !== 'الدرجة النهائية')];

            let finals = [];
            let csv = `${__('الطالب: ')}${stuName}\n${__('المادة')}`;
            csv += types.map(t => ',' + t).join('') + ',' + __('المعدل') + '\n';
            Object.entries(groups).forEach(([subId, sub]) => {
                csv += `"${sub.name}"`;
                types.forEach(t => csv += ',' + (sub.exams[t] !== undefined ? sub.exams[t] : ''));
                const f = sub.exams['الدرجة النهائية'];
                if (f !== undefined) finals.push(parseFloat(f));
                csv += ',' + (finals.length ? (finals.reduce((a,b) => a+b, 0) / finals.length).toFixed(1) : '') + '\n';
            });
            if (finals.length) csv += `\n${__('المعدل العام')},${(finals.reduce((a,b) => a+b, 0) / finals.length).toFixed(1)}\n`;

            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
            a.download = __('كشف-علامات-') + stuName + '.csv'; a.click();
        }
    </script>
    <style>
        label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
        #reportContent td { padding: 8px 10px; text-align: center; vertical-align: middle; }
        #reportContent th { text-align: center; font-size: 13px; background:#e8eff9; padding:8px; }
        #reportContent table { border-collapse: collapse; }
        #reportContent table td, #reportContent table th { border: 1px solid #e0e8f0; }
        .btn-secondary { background:#6c757d; color:#fff; }
        .btn-secondary:hover { background:#5a6268; }
        #filterStudent option { padding:4px; }
    </style>
@stop