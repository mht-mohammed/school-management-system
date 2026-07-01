@extends('layouts.dashboard')

@section('title', __('ولي الأمر'))
@section('page-title', __('لوحة ولي الأمر'))

@section('sidebar')
    <a href="/parent" class="active">📊 <span>{{ __('أبنائي') }}</span></a>
@stop

@section('content')
    <div class="card">
        <h3>{{ __('أبنائي') }}</h3>
        <div class="loading" id="loadingChildren">{{ __('جاري التحميل...') }}</div>
        <div id="childrenContent" style="display:none;"></div>
    </div>

    <script>
        apiFetch('/parent/children').then(list => {
            document.getElementById('loadingChildren').style.display = 'none';
            if (!list || !list.length) { document.querySelector('.card').innerHTML += '<div class="empty">' + __('لا يوجد أبناء مسجلين') + '</div>'; return; }
            const div = document.getElementById('childrenContent');

            list.forEach(c => {
                let html = `<div style="background:#fff;border:1px solid var(--border-soft);box-shadow:var(--shadow-sm);padding:20px;border-radius:var(--radius-md);margin-bottom:15px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <strong style="font-size:17px;color:var(--text-dark);">${c.user?.name || '-'}</strong>
                        <span class="badge badge-success" style="font-size:12px;padding:5px 16px;">${c.class?.name || __('بدون صف')}</span>
                    </div>`;

                apiFetch('/parent/child/' + c.id + '/grades').then(grades => {
                    let gradesHtml = '<div style="margin-top:12px;"><h4 style="font-size:15px;color:var(--blue-main);font-weight:800;">📊 ' + __('الدرجات') + '</h4>';
                    if (grades?.length) {
                        const bySubject = {};
                        grades.forEach(g => {
                            const sn = subjectName(g.subject?.name);
                            if (!bySubject[sn]) bySubject[sn] = [];
                            bySubject[sn].push(g);
                        });
                        Object.entries(bySubject).forEach(([sn, list]) => {
                            gradesHtml += `<div style="margin-top:10px;"><strong style="color:var(--text-dark);font-size:14px;">📚 ${sn}</strong></div>`;
                            gradesHtml += '<table style="margin-top:6px;width:100%;"><tr><th>' + __('الامتحان') + '</th><th>' + __('الدرجة') + '</th></tr>';
                            list.forEach(g => {
                                gradesHtml += `<tr><td style="color:#333;">${g.exam_type}</td><td style="font-weight:800;font-size:15px;color:var(--text-dark);">${g.score}</td></tr>`;
                            });
                            gradesHtml += '</table>';
                        });
                    } else {
                        gradesHtml += '<div class="empty" style="padding:10px;">' + __('لا توجد درجات') + '</div>';
                    }
                    gradesHtml += '</div>';

                    const childDiv = document.querySelector(`#child-${c.id}`);
                    if (childDiv) childDiv.innerHTML += gradesHtml;
                });

                apiFetch('/parent/child/' + c.id + '/attendance').then(att => {
                    let attHtml = '<div style="margin-top:12px;"><h4 style="font-size:15px;color:var(--blue-main);font-weight:800;">📋 ' + __('الحضور') + '</h4>';
                    const statusAr = { present:__('حاضر'), absent:__('غائب') };
                    if (att?.length) {
                        const present = att.filter(a => a.status === 'present').length;
                        const absent = att.filter(a => a.status === 'absent').length;
                        const total = present + absent;
                        const pct = total ? Math.round(present / total * 100) : 0;
                        attHtml += `<p style="font-size:15px;color:var(--text-dark);font-weight:600;">${__('نسبة الحضور:')} <strong style="color:var(--blue-main);font-size:18px;">${toArabicNum(pct)}%</strong> (${__('حاضر')} <strong style="color:#157a35;">${toArabicNum(present)}</strong> / ${__('غائب')} <strong style="color:#b8232e;">${toArabicNum(absent)}</strong>)</p>`;
                        const dayNames = {0:__('الأحد'),1:__('الإثنين'),2:__('الثلاثاء'),3:__('الأربعاء'),4:__('الخميس'),5:__('الجمعة'),6:__('السبت')};
                        const absentDays = att.filter(a => a.status === 'absent');
                        const hasMore = absentDays.length < att.length;
                        let tableId = 'attTable-' + c.id;

                        if (absentDays.length) {
                            attHtml += '<table style="margin-top:6px;width:100%;"><tr><th>' + __('التاريخ') + '</th><th>' + __('اليوم') + '</th><th>' + __('الحالة') + '</th></tr>';
                            absentDays.forEach(a => {
                                const d = new Date(a.date + 'T00:00:00');
                                const dayName = dayNames[d.getDay()] || '';
                                attHtml += `<tr><td style="color:#333;">${a.date}</td><td style="color:#333;">${dayName}</td><td><span class="badge badge-danger" style="font-size:12px;padding:5px 16px;">${statusAr[a.status] || a.status}</span></td></tr>`;
                            });
                            attHtml += '</table>';
                        } else {
                            attHtml += '<p style="color:#28a745;font-weight:600;">✅ ' + __('لا توجد أيام غياب') + '</p>';
                        }

                        if (hasMore) {
                            attHtml += '<div id="attAll-' + c.id + '" style="display:none;">';
                            attHtml += '<hr style="margin:10px 0;"><p style="font-weight:600;color:var(--text-dark);">' + __('سجل الحضور الكامل') + '</p>';
                            attHtml += '<table style="margin-top:6px;width:100%;"><tr><th>' + __('التاريخ') + '</th><th>' + __('اليوم') + '</th><th>' + __('الحالة') + '</th></tr>';
                            att.forEach(a => {
                                const d = new Date(a.date + 'T00:00:00');
                                const dayName = dayNames[d.getDay()] || '';
                                const cls = a.status === 'present' ? 'badge-success' : 'badge-danger';
                                attHtml += `<tr><td style="color:#333;">${a.date}</td><td style="color:#333;">${dayName}</td><td><span class="badge ${cls}" style="font-size:12px;padding:5px 16px;">${statusAr[a.status] || a.status}</span></td></tr>`;
                            });
                            attHtml += '</table>';
                            attHtml += '<button class="btn btn-secondary" style="margin-top:8px;font-size:12px;padding:6px 14px;" onclick="document.getElementById(\'attAbsent-' + c.id + '\').style.display=\'\';document.getElementById(\'attAll-' + c.id + '\').style.display=\'none\'">' + __('🔙 إخفاء') + '</button>';
                            attHtml += '</div>';
                            attHtml += '<div id="attAbsent-' + c.id + '"><button class="btn btn-secondary" style="margin-top:8px;font-size:12px;padding:6px 14px;" onclick="document.getElementById(\'attAbsent-' + c.id + '\').style.display=\'none\';document.getElementById(\'attAll-' + c.id + '\').style.display=\'\'">' + __('📋 عرض سجل الحضور الكامل') + '</button></div>';
                        }
                        attHtml += '</table>';
                    } else {
                        attHtml += '<div class="empty" style="padding:10px;">' + __('لا توجد سجلات حضور') + '</div>';
                    }
                    attHtml += '</div>';

                    const childDiv = document.querySelector(`#child-${c.id}`);
                    if (childDiv) childDiv.innerHTML += attHtml;
                });

                html += `<div id="child-${c.id}"></div>`;
                html += '</div>';
                div.innerHTML += html;
            });
            div.style.display = '';
        });
    </script>
@stop
