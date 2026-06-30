@extends('layouts.dashboard')

@section('title', __('تقرير الحضور'))
@section('page-title', __('تقرير الحضور'))

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
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report" class="active">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
    <div class="card">
        <h3>{{ __('📋 رفع جدول حضور شهري') }}</h3>
        <div style="display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px;">
            <div><label>{{ __('الصف') }}</label><select id="importClass" style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:200px;"></select></div>
            <div><label>{{ __('الشهر') }}</label><input id="importMonth" type="month" style="padding:10px;border:1px solid #ddd;border-radius:8px;"></div>
            <div style="align-self:flex-end;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <button class="btn btn-secondary" onclick="downloadTemplate()">{{ __('📥 تحميل القالب') }}</button>
                <label for="fileInput" class="btn btn-primary" style="cursor:pointer;">{{ __('📂 اختيار ملف') }}</label>
                <input id="fileInput" type="file" accept=".csv,.txt" style="display:none" onchange="document.getElementById('fileName').textContent=this.files[0]?.name||''">
                <span id="fileName" style="color:#888;font-size:13px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                <button class="btn btn-success" onclick="importAttendance()">{{ __('⬆ رفع') }}</button>
            </div>
        </div>
        <div class="loading" id="loadingImport" style="display:none;">{{ __('جاري الاستيراد...') }}</div>
        <div id="importResult" style="display:none;"></div>
    </div>

    <div class="card" style="margin-top:20px;">
        <h3>{{ __('✅ تقرير الحضور') }}</h3>
        <div style="display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px;">
            <div><label>{{ __('الصف') }}</label><select id="filterClass" onchange="loadReport()" style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:200px;"><option value="">{{ __('الكل') }}</option></select></div>
            <div><label>{{ __('الشهر') }}</label><input id="filterMonth" type="month" onchange="loadReport()" style="padding:10px;border:1px solid #ddd;border-radius:8px;"></div>
            <div style="align-self:flex-end;"><button class="btn btn-primary" onclick="exportCSV()">{{ __('📥 تصدير CSV') }}</button></div>
        </div>
        <div class="loading" id="loadingReport">{{ __('جاري التحميل...') }}</div>
        <div id="reportContent" style="display:none;overflow-x:auto;"></div>
    </div>

    <script>
        let allAttendance = [];

        const classSelects = [document.getElementById('importClass'), document.getElementById('filterClass')];
        apiFetch('/admin/classes').then(list => {
            (list || []).forEach(c => {
                const label = c.name + (c.section ? ' - ' + __('شعبة') + ' ' + sectionLabel(c.section) : '');
                classSelects.forEach(sel => {
                    sel.innerHTML += `<option value="${c.id}">${label}</option>`;
                });
            });
        });

        const now = new Date();
        const defaultMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
        document.getElementById('importMonth').value = defaultMonth;
        document.getElementById('filterMonth').value = defaultMonth;

        function downloadTemplate() {
            const classId = document.getElementById('importClass').value;
            const month = document.getElementById('importMonth').value;
            if (!classId) { showToast(__('اختر الصف أولاً'), 'error'); return; }
            if (!month) { showToast(__('اختر الشهر أولاً'), 'error'); return; }
            const token = localStorage.getItem('token');
            fetch(API_BASE + '/admin/attendance/template?class_id=' + classId + '&month=' + month, {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            }).then(r => {
                if (!r.ok) throw new Error(__('خطأ في تحميل القالب'));
                return r.blob();
            }).then(blob => {
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = __('حضور_') + month + '.csv';
                a.click();
                URL.revokeObjectURL(a.href);
            }).catch(err => showToast(err.message, 'error'));
        }

        function importAttendance() {
            const classId = document.getElementById('importClass').value;
            const month = document.getElementById('importMonth').value;
            const fileInput = document.getElementById('fileInput');

            if (!classId) { showToast(__('اختر الصف أولاً'), 'error'); return; }
            if (!month) { showToast(__('اختر الشهر أولاً'), 'error'); return; }
            if (!fileInput.files.length) { showToast(__('اختر ملف CSV أولاً'), 'error'); return; }

            const formData = new FormData();
            formData.append('class_id', classId);
            formData.append('month', month);
            formData.append('file', fileInput.files[0]);

            document.getElementById('loadingImport').style.display = '';
            document.getElementById('importResult').style.display = 'none';

            fetch(API_BASE + '/admin/attendance/import', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' },
                body: formData,
            }).then(r => {
                if (!r.ok) return r.text().then(body => { let msg; try { const j = JSON.parse(body); msg = j.message || __('خطأ ') + r.status; } catch(e) { msg = body.substring(0,100); } throw new Error(msg); });
                return r.json();
            }).then(result => {
                document.getElementById('loadingImport').style.display = 'none';
                document.getElementById('fileInput').value = '';
                document.getElementById('fileName').textContent = '';
                const div = document.getElementById('importResult');

                let html = `<div style="background:#e1f7e7;color:#157a35;padding:15px;border-radius:8px;margin-bottom:15px;font-weight:700;">✅ ${result.message}</div>`;

                if (result.not_found?.length) {
                    html += `<div style="background:#fdf1d9;color:#93680c;padding:15px;border-radius:8px;margin-bottom:15px;font-size:13px;">
                        <strong>⚠️ ${__('لم يتم العثور على طلاب بالبريد الإلكتروني:')}</strong><br>${result.not_found.join('، ')}</div>`;
                }

                if (result.other_class_students?.length) {
                    html += `<div style="background:#cce5ff;color:#004085;padding:15px;border-radius:8px;margin-bottom:15px;font-size:13px;">
                        <strong>ℹ️ ${__('الطلاب التاليين من صف آخر وتم استيراد حضورهم لصفهم الأصلي:')}</strong><br>${result.other_class_students.join('، ')}</div>`;
                }

                if (result.missing_students?.length) {
                    html += `<div style="background:#fdf1d9;color:#93680c;padding:15px;border-radius:8px;margin-bottom:15px;font-size:13px;">
                        <strong>⚠️ ${__('طلاب في الصف لم يتم تضمينهم في الملف:')}</strong><br>${result.missing_students.join('، ')}</div>`;
                }

                if (result.summary?.length) {
                    html += '<h4 style="margin-bottom:10px;">' + __('ملخص الحضور الشهري') + '</h4>';
                    html += '<table><thead><tr><th>' + __('الطالب') + '</th><th>' + __('إجمالي') + '</th><th>' + __('حاضر') + '</th><th>' + __('غائب') + '</th><th>' + __('النسبة') + '</th></tr></thead><tbody>';
                    result.summary.forEach(s => {
                        const avatar = s.avatar ? `<img src="/storage/${s.avatar}" style="width:24px;height:24px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : '';
                        html += `<tr><td>${avatar}${s.student_name}</td><td>${toArabicNum(s.total)}</td><td style="color:#28a745;">${toArabicNum(s.present)}</td><td style="color:#b8232e;">${toArabicNum(s.absent)}</td><td><strong>${toArabicNum(s.percentage)}%</strong></td></tr>`;
                    });
                    html += '</tbody></table>';
                }

                div.innerHTML = html;
                div.style.display = '';
                loadReport();
            }).catch(err => {
                document.getElementById('loadingImport').style.display = 'none';
                showToast(__('فشل الاستيراد: ') + err.message, 'error');
            });
        }

        function loadReport() {
            const classId = document.getElementById('filterClass').value;
            const month = document.getElementById('filterMonth').value;
            document.getElementById('loadingReport').textContent = __('جاري التحميل...');
            document.getElementById('loadingReport').style.display = '';

            let url = '/admin/attendance-report?';
            if (classId) url += 'class_id=' + classId + '&';
            if (month) {
                const [y, m] = month.split('-');
                const lastDay = new Date(parseInt(y), parseInt(m), 0).getDate();
                url += 'date_from=' + y + '-' + m + '-01&date_to=' + y + '-' + m + '-' + lastDay;
            }

            apiFetch(url).then(list => {
                allAttendance = list || [];
                document.getElementById('loadingReport').style.display = 'none';
                const div = document.getElementById('reportContent');

                if (!allAttendance.length) {
                    div.innerHTML = '<div class="empty">' + __('لا توجد سجلات حضور') + '</div>';
                    div.style.display = '';
                    return;
                }

                const byClass = {};
                allAttendance.forEach(a => {
                    const cid = a.class_id;
                    if (!byClass[cid]) {
                        byClass[cid] = { name: a.class?.name || __('صف ') + cid, students: {} };
                    }
                    const sid = a.student_id;
                    if (!byClass[cid].students[sid]) {
                        byClass[cid].students[sid] = { name: a.student?.user?.name || '-', avatar: a.student?.user?.avatar, present: 0, absent: 0, total: 0 };
                    }
                    byClass[cid].students[sid][a.status] = (byClass[cid].students[sid][a.status] || 0) + 1;
                    byClass[cid].students[sid].total++;
                });

                let html = `<p><strong>${__('إجمالي السجلات:')}</strong> ${toArabicNum(allAttendance.length)}</p>`;
                Object.entries(byClass).forEach(([cid, cls]) => {
                    html += `<h4 style="margin:20px 0 10px;color:var(--blue-main);">🏫 ${cls.name}</h4>`;
                    html += '<table><thead><tr><th>' + __('الطالب') + '</th><th>' + __('إجمالي') + '</th><th>' + __('حاضر') + '</th><th>' + __('غائب') + '</th><th>' + __('النسبة') + '</th></tr></thead><tbody>';
                    Object.values(cls.students).forEach(s => {
                        const pct = s.total ? Math.round((s.present || 0) / s.total * 100) : 0;
                        const avatar = s.avatar ? `<img src="/storage/${s.avatar}" style="width:24px;height:24px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : '';
                        html += `<tr><td>${avatar}${s.name}</td><td>${toArabicNum(s.total)}</td><td style="color:#28a745;">${toArabicNum(s.present||0)}</td><td style="color:#b8232e;">${toArabicNum(s.absent||0)}</td><td><strong>${toArabicNum(pct)}%</strong></td></tr>`;
                    });
                    html += '</tbody></table>';
                });

                const dayNames = {0:__('الأحد'),1:__('الإثنين'),2:__('الثلاثاء'),3:__('الأربعاء'),4:__('الخميس'),5:__('الجمعة'),6:__('السبت')};
                html += '<hr><button class="btn btn-secondary" onclick="toggleDetail()">' + __('📋 عرض التفاصيل اليومية') + '</button>';
                html += '<div id="detailView" style="display:none;margin-top:15px;">';
                html += '<table><thead><tr><th>' + __('الطالب') + '</th><th>' + __('الصف') + '</th><th>' + __('التاريخ') + '</th><th>' + __('اليوم') + '</th><th>' + __('الحالة') + '</th></tr></thead><tbody>';
                const statusAr = { present:__('حاضر'), absent:__('غائب') };
                allAttendance.forEach(a => {
                    const d = new Date(a.date + 'T00:00:00');
                    const dayName = dayNames[d.getDay()] || '';
                    const cls = a.status === 'present' ? 'badge-success' : 'badge-danger';
                    html += `<tr><td>${a.student?.user?.name || '-'}</td><td>${a.class?.name || ''}</td><td>${a.date}</td><td>${dayName}</td><td><span class="badge ${cls}">${statusAr[a.status] || a.status}</span></td></tr>`;
                });
                html += '</tbody></table></div>';

                div.innerHTML = html;
                div.style.display = '';
            });
        }

        function toggleDetail() {
            const div = document.getElementById('detailView');
            div.style.display = div.style.display === 'none' ? '' : 'none';
        }

        function exportCSV() {
            if (!allAttendance.length) { showToast(__('لا توجد بيانات للتصدير'), 'error'); return; }
            const statusAr = { present:__('حاضر'), absent:__('غائب') };
            let csv = __('الطالب') + ',' + __('التاريخ') + ',' + __('الحالة') + '\n';
            allAttendance.forEach(a => {
                const name = a.student?.user?.name || '-';
                csv += `"${name}",${a.date},${statusAr[a.status] || a.status}\n`;
            });
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = __('تقرير-الحضور') + '.csv';
            a.click();
        }

        loadReport();
    </script>
    <style>
        label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
    </style>
@stop
