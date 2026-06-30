@extends('layouts.dashboard')

@section('title', __('الطالب'))
@section('page-title', __('لوحة الطالب'))

@section('sidebar')
    <a href="/student" class="active">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/student/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/student/library">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
    <div style="display:flex;align-items:center;gap:15px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,var(--blue-main),var(--blue-light));display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff;box-shadow:0 6px 16px rgba(45,91,227,0.25);">🎓</div>
        <div>
            <h2 id="studentName" style="margin:0;">—</h2>
            <p id="studentClass" style="color:#888;margin:4px 0 0;">—</p>
        </div>
    </div>

    <div class="stats-grid" id="statsGrid">
        <div class="stat-card"><div class="number" id="statAvg">-</div><div class="label">{{ __('المعدل العام') }}</div></div>
        <div class="stat-card"><div class="number" id="statGrades">-</div><div class="label">{{ __('عدد الدرجات') }}</div></div>
        <div class="stat-card"><div class="number" id="statPresent">-</div><div class="label">{{ __('نسبة الحضور') }}</div></div>
        <div class="stat-card"><div class="number" id="statSubjectsCount">-</div><div class="label">{{ __('المواد') }}</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <h3>{{ __('📊 درجاتي') }}</h3>
            <div class="loading" id="loadingGrades">{{ __('جاري التحميل...') }}</div>
            <div id="gradesContent" style="display:none;"></div>
        </div>
        <div class="card">
            <h3>{{ __('✅ حضوري') }}</h3>
            <div class="loading" id="loadingAttendance">{{ __('جاري التحميل...') }}</div>
            <div id="attendanceContent" style="display:none;"></div>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <h3>{{ __('📅 جدولي الدراسي') }}</h3>
        <div class="loading" id="loadingSchedule">{{ __('جاري التحميل...') }}</div>
        <div id="scheduleGrid" style="display:none;overflow-x:auto;"></div>
    </div>

    <script>
        const daysAr = {sunday:__('الأحد'),monday:__('الإثنين'),tuesday:__('الثلاثاء'),wednesday:__('الأربعاء'),thursday:__('الخميس')};
        const daysOrder = ['sunday','monday','tuesday','wednesday','thursday'];
        const periodTimes = {1:'08:00-08:45',2:'08:50-09:35',3:'09:40-10:25',4:'10:55-11:40',5:'11:45-12:30'};

        apiFetch('/student/average').then(d => {
            document.getElementById('studentName').textContent = d.name || '—';
            document.getElementById('studentClass').textContent = d.class_name || '—';
            setNum('statAvg', d.average ?? '-');
            setNum('statGrades', d.total_grades ?? 0);
            setNum('statSubjectsCount', d.subjects?.length ?? 0);

            if (d.subjects?.length) {
                const gc = document.getElementById('gradesContent');
                let html = '<table><thead><tr><th>' + __('المادة') + '</th><th>' + __('الدرجة النهائية') + '</th><th>' + __('عدد الدرجات') + '</th></tr></thead><tbody>';
                d.subjects.forEach(s => {
                    html += `<tr><td>${subjectName(s.name)}</td><td><strong>${toArabicNum(s.average)}</strong></td><td>${toArabicNum(s.count || 0)}</td></tr>`;
                });
                html += '</tbody></table>';
                gc.innerHTML = html;
                gc.style.display = '';
                document.getElementById('loadingGrades').style.display = 'none';
            }
        });

        const statusAr = { present:__('حاضر'), absent:__('غائب') };
        apiFetch('/student/attendance').then(list => {
            document.getElementById('loadingAttendance').style.display = 'none';
            const ac = document.getElementById('attendanceContent');
            ac.style.display = '';

            if (!list || !list.length) {
                ac.innerHTML = '<div class="empty">' + __('لا توجد سجلات حضور') + '</div>';
                return;
            }

            const present = list.filter(a => a.status === 'present').length;
            const absent = list.filter(a => a.status === 'absent').length;
            const total = present + absent;
            const pct = total ? Math.round(present / total * 100) : 0;
            document.getElementById('statPresent').textContent = toArabicNum(pct) + '%';

            let html = `<p style="font-size:14px;color:#666;">
                <span style="color:#157a35;">✔ ${__('حاضر:')} ${toArabicNum(present)}</span> |
                <span style="color:#b8232e;">✖ ${__('غائب:')} ${toArabicNum(absent)}</span>
            </p>`;
            const dayNames = {0:__('الأحد'),1:__('الإثنين'),2:__('الثلاثاء'),3:__('الأربعاء'),4:__('الخميس'),5:__('الجمعة'),6:__('السبت')};
            html += '<table><thead><tr><th>' + __('التاريخ') + '</th><th>' + __('اليوم') + '</th><th>' + __('الحالة') + '</th></tr></thead><tbody>';
            list.slice(0, 10).forEach(a => {
                const d = new Date(a.date + 'T00:00:00');
                const dayName = dayNames[d.getDay()] || '';
                const cls = a.status === 'present' ? 'badge-success' : 'badge-danger';
                html += `<tr><td>${a.date}</td><td>${dayName}</td><td><span class="badge ${cls}">${statusAr[a.status] || a.status}</span></td></tr>`;
            });
            html += '</tbody></table>';
            if (list.length > 10) html += `<p style="font-size:13px;color:#888;">${__('... وآخر')} ${list.length - 10} ${__('سجل')}</p>`;
            ac.innerHTML = html;
        });

        apiFetch('/student/schedule').then(list => {
            document.getElementById('loadingSchedule').style.display = 'none';
            if (!list || !list.length) { document.querySelector('#scheduleGrid').parentNode.innerHTML += '<div class="empty">' + __('لا يوجد جدول بعد') + '</div>'; return; }

            const grid = document.getElementById('scheduleGrid');
            let html = '<table style="min-width:600px;"><thead><tr><th style="min-width:80px;">' + __('اليوم / الحصة') + '</th>';
            for (let p = 1; p <= 5; p++) {
                html += `<th>${p}<br><small>${periodTimes[p]}</small></th>`;
            }
            html += '</tr></thead><tbody>';
            daysOrder.forEach(day => {
                html += `<tr><td><strong>${daysAr[day]}</strong></td>`;
                for (let p = 1; p <= 5; p++) {
                    const item = list.find(s => s.day_of_week === day && s.period_number === p);
                    if (item) {
                        html += `<td style="background:var(--blue-50);color:var(--blue-main);font-size:13px;border-radius:8px;">
                            <strong>${subjectName(item.subject?.name)}</strong><br><small>${item.teacher?.name || ''}${item.room ? ' - ' + item.room : ''}</small>
                        </td>`;
                    } else {
                        html += '<td style="color:#ccc;">—</td>';
                    }
                }
                html += '</tr>';
            });
            html += '</tbody></table>';
            grid.innerHTML = html;
            grid.style.display = '';
        });
    </script>
    <style>
        #scheduleGrid td { padding: 12px 8px; text-align: center; vertical-align: middle; min-width: 90px; }
        #scheduleGrid th { text-align: center; font-size: 13px; }
    </style>
@stop
