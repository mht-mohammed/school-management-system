@extends('layouts.dashboard')

@section('title', __('جدولي'))
@section('page-title', __('جدول الحصص'))

@section('sidebar')
    <a href="/teacher">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/teacher/grades">📝 <span>{{ __('الدرجات') }}</span></a>
    <a href="/teacher/schedule" class="active">📅 <span>{{ __('جدولي') }}</span></a>
@stop

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3>{{ __('📅 جدول الحصص الأسبوعي') }}</h3>
            <span style="color:#888;font-size:14px;" id="teacherName"></span>
        </div>
        <div class="loading" id="loadingSchedule">{{ __('جاري التحميل...') }}</div>
        <div id="scheduleGrid" style="display:none;overflow-x:auto;"></div>
    </div>

    <script>
        const daysAr = {sunday:__('الأحد'),monday:__('الإثنين'),tuesday:__('الثلاثاء'),wednesday:__('الأربعاء'),thursday:__('الخميس')};
        const daysOrder = ['sunday','monday','tuesday','wednesday','thursday'];
        const periodTimes = {1:'08:00-08:45',2:'08:50-09:35',3:'09:40-10:25',4:'10:55-11:40',5:'11:45-12:30'};

        // Populate teacher name
        try {
            const userData = JSON.parse(localStorage.getItem('user') || '{}');
            document.getElementById('teacherName').textContent = '👤 ' + (userData.name || '');
        } catch(e) {}

        apiFetch('/teacher/schedule').then(data => {
            document.getElementById('loadingSchedule').style.display = 'none';
            if (!data || !data.length) {
                document.querySelector('.card').innerHTML += '<div class="empty">' + __('لا يوجد جدول بعد') + '</div>';
                return;
            }

            // Build 5x5 grid
            const grid = {};
            daysOrder.forEach(d => { grid[d] = {}; });
            data.forEach(s => {
                if (grid[s.day_of_week]) {
                    grid[s.day_of_week][s.period_number] = s;
                }
            });

            let html = '<table style="min-width:700px;"><thead><tr><th style="min-width:80px;">' + __('اليوم / الحصة') + '</th>';
            for (let p = 1; p <= 5; p++) {
                html += `<th>${p}<br><small>${periodTimes[p]}</small></th>`;
            }
            html += '</tr></thead><tbody>';

            daysOrder.forEach(day => {
                html += `<tr><td><strong>${daysAr[day]}</strong></td>`;
                for (let p = 1; p <= 5; p++) {
                    const s = grid[day][p];
                    if (s) {
                        html += `<td style="background:#d4edda;color:#155724;font-size:13px;">
                            <strong>${subjectName(s.subject?.name) || '-'}</strong><br>
                            <small>${s.class?.name || ''} ${s.section?.section ? __('شعبة') + ' ' + sectionLabel(s.section.section) : ''}${s.room ? ' - ' + s.room : ''}</small>
                        </td>`;
                    } else {
                        html += '<td style="color:#ccc;font-size:12px;">—</td>';
                    }
                }
                html += '</tr>';
            });

            html += '</tbody></table>';
            const gridEl = document.getElementById('scheduleGrid');
            gridEl.innerHTML = html;
            gridEl.style.display = '';
        });
    </script>
    <style>
        #scheduleGrid td { padding: 10px 6px; text-align: center; vertical-align: middle; min-width: 100px; border:1px solid #e0e0e0; }
        #scheduleGrid th { text-align: center; font-size: 13px; background:#4a90d9; color:#fff; padding:10px; border:1px solid #3a7bc8; }
        #scheduleGrid tr td:first-child { background:#f0f4ff; font-weight:700; }
    </style>
@stop
