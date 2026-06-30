@extends('layouts.dashboard')

@section('title', __('الإدارة'))
@section('page-title', __('لوحة التحكم'))

@section('sidebar')
    <a href="/admin" class="active">📊 <span>{{ __('الإحصائيات') }}</span></a>
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
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card"><div class="number" id="statStudents">-</div><div class="label">{{ __('الطلاب') }}</div></div>
        <div class="stat-card"><div class="number" id="statTeachers">-</div><div class="label">{{ __('المعلمون') }}</div></div>
        <div class="stat-card"><div class="number" id="statClasses">-</div><div class="label">{{ __('الصفوف') }}</div></div>
        <div class="stat-card"><div class="number" id="statSubjects">-</div><div class="label">{{ __('المواد') }}</div></div>
        <div class="stat-card"><div class="number" id="statEnrollments">-</div><div class="label">{{ __('طلبات التحاق') }}</div></div>
        <div class="stat-card"><div class="number" id="statMessages">-</div><div class="label">{{ __('رسائل غير مقروءة') }}</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <h3>{{ __('آخر طلبات الالتحاق') }}</h3>
            <div class="loading" id="loadingRecentEnrollments">{{ __('جاري التحميل...') }}</div>
            <table id="recentEnrollmentsTable" style="display:none;">
                <thead><tr><th>{{ __('الاسم') }}</th><th>{{ __('الحالة') }}</th></tr></thead>
                <tbody id="recentEnrollmentsBody"></tbody>
            </table>
        </div>
        <div class="card">
            <h3>{{ __('آخر الرسائل') }}</h3>
            <div class="loading" id="loadingRecentMessages">{{ __('جاري التحميل...') }}</div>
            <table id="recentMessagesTable" style="display:none;">
                <thead><tr><th>{{ __('الاسم') }}</th><th>{{ __('الحالة') }}</th></tr></thead>
                <tbody id="recentMessagesBody"></tbody>
            </table>
        </div>
    </div>

    <script>
        apiFetch('/admin/dashboard').then(d => {
            setNum('statStudents', d.students_count);
            setNum('statTeachers', d.teachers_count);
            setNum('statEnrollments', d.pending_enrollments);
            setNum('statMessages', d.unread_messages);
        });

        apiFetch('/admin/classes').then(list => {
            setNum('statClasses', list?.length || 0);
        });

        apiFetch('/admin/subjects').then(list => {
            setNum('statSubjects', list?.length || 0);
        });

        apiFetch('/admin/enrollments').then(list => {
            document.getElementById('loadingRecentEnrollments').style.display = 'none';
            if (list?.length) {
                const tbody = document.getElementById('recentEnrollmentsBody');
                document.getElementById('recentEnrollmentsTable').style.display = '';
                list.slice(0, 5).forEach(e => {
                    const statusMap = { approved:__('موافق'), rejected:__('مرفوض'), pending:__('قيد الانتظار') };
                    const cls = e.status === 'approved' ? 'badge-success' : e.status === 'rejected' ? 'badge-danger' : 'badge-warning';
                    tbody.innerHTML += `<tr><td>${e.student_name}</td><td><span class="badge ${cls}">${statusMap[e.status] || e.status}</span></td></tr>`;
                });
            }
        });

        apiFetch('/admin/contact-messages').then(list => {
            document.getElementById('loadingRecentMessages').style.display = 'none';
            if (list?.length) {
                const tbody = document.getElementById('recentMessagesBody');
                document.getElementById('recentMessagesTable').style.display = '';
                list.slice(0, 5).forEach(m => {
                    const cls = m.is_read ? 'badge-success' : 'badge-warning';
                    tbody.innerHTML += `<tr><td>${m.name}</td><td><span class="badge ${cls}">${m.is_read ? __('مقروءة') : __('جديدة')}</span></td></tr>`;
                });
            }
        });
    </script>
@stop
