@extends('layouts.dashboard')

@section('title', __('ولي الأمر'))
@section('page-title', __('لوحة ولي الأمر'))

@section('sidebar')
    <a href="/parent" class="active">📊 <span>{{ __('أبنائي') }}</span></a>
    <a href="/admin/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
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
                let html = `<div style="background:var(--blue-50);border:1px solid var(--border-soft);padding:18px;border-radius:var(--radius-md);margin-bottom:15px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <strong style="font-size:16px;">${c.user?.name || '-'}</strong>
                        <span class="badge badge-success">${c.class?.name || __('بدون صف')}</span>
                    </div>`;

                apiFetch('/parent/child/' + c.id + '/grades').then(grades => {
                    let gradesHtml = '<div style="margin-top:10px;"><h4 style="font-size:14px;color:var(--blue-main);">' + __('الدرجات') + '</h4>';
                    if (grades?.length) {
                        const bySubject = {};
                        grades.forEach(g => {
                            const sn = subjectName(g.subject?.name);
                            if (!bySubject[sn]) bySubject[sn] = [];
                            bySubject[sn].push(g);
                        });
                        Object.entries(bySubject).forEach(([sn, list]) => {
                            gradesHtml += `<div style="margin-top:8px;"><strong>📚 ${sn}</strong></div>`;
                            gradesHtml += '<table style="margin-top:4px;"><tr><th>' + __('الامتحان') + '</th><th>' + __('الدرجة') + '</th></tr>';
                            list.forEach(g => {
                                gradesHtml += `<tr><td>${g.exam_type}</td><td style="font-weight:700;">${g.score}</td></tr>`;
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
                    let attHtml = '<div style="margin-top:10px;"><h4 style="font-size:14px;color:var(--blue-main);">' + __('الحضور') + '</h4>';
                    const statusAr = { present:__('حاضر'), absent:__('غائب') };
                    if (att?.length) {
                        const present = att.filter(a => a.status === 'present').length;
                        const absent = att.filter(a => a.status === 'absent').length;
                        const total = present + absent;
                        const pct = total ? Math.round(present / total * 100) : 0;
                        attHtml += `<p style="font-size:14px;">${__('نسبة الحضور:')} <strong>${toArabicNum(pct)}%</strong> (${__('حاضر')} ${toArabicNum(present)} / ${__('غائب')} ${toArabicNum(absent)})</p>`;
                        const dayNames = {0:__('الأحد'),1:__('الإثنين'),2:__('الثلاثاء'),3:__('الأربعاء'),4:__('الخميس'),5:__('الجمعة'),6:__('السبت')};
                        attHtml += '<table style="margin-top:5px;"><tr><th>' + __('التاريخ') + '</th><th>' + __('اليوم') + '</th><th>' + __('الحالة') + '</th></tr>';
                        att.slice(0, 5).forEach(a => {
                            const d = new Date(a.date + 'T00:00:00');
                            const dayName = dayNames[d.getDay()] || '';
                            const cls = a.status === 'present' ? 'badge-success' : 'badge-danger';
                            attHtml += `<tr><td>${a.date}</td><td>${dayName}</td><td><span class="badge ${cls}">${statusAr[a.status] || a.status}</span></td></tr>`;
                        });
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
