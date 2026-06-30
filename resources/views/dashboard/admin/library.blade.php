@extends('layouts.dashboard')

@section('title', __('المكتبة'))
@section('page-title', __('المكتبة — الإدارة'))

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
    <a href="/admin/library" class="active">📖 <span>{{ __('المكتبة') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
<div class="header">
    <h1>{{ __('المكتبة') }}</h1>
</div>

<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="number" id="statBooks">-</div>
        <div class="label">{{ __('كتاب') }}</div>
    </div>
    <div class="stat-card">
        <div class="number" id="statTeachers">-</div>
        <div class="label">{{ __('معلم') }}</div>
    </div>
    <div class="stat-card">
        <div class="number" id="statClasses">-</div>
        <div class="label">{{ __('صف') }}</div>
    </div>
</div>

<div class="card">
    <div id="booksList">
        <div class="loading">{{ __('جاري التحميل...') }}</div>
    </div>
</div>

<style>
    .book-card {
        background: var(--white); border: 1px solid var(--border-soft); border-radius: var(--radius-md);
        padding: 20px; margin-bottom: 14px; transition: all 0.25s ease;
    }
    .book-card:hover { border-color: var(--blue-main); box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .book-header { display: flex; align-items: center; gap: 14px; margin-bottom: 10px; }
    .book-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--violet), var(--blue-main));
        display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--white);
        box-shadow: 0 6px 16px rgba(67,56,202,0.25);
    }
    .book-title { font-weight: 800; font-size: 15px; color: var(--text-dark); }
    .book-desc { color: var(--gray-text); font-size: 13px; line-height: 1.6; margin-bottom: 12px; }
    .book-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
    .book-tag {
        display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 50px;
        font-size: 11.5px; font-weight: 700; letter-spacing: 0.1px;
    }
    .tag-teacher { background: #eef1fd; color: var(--blue-main); }
    .tag-subject { background: #f3eeff; color: var(--violet); }
    .tag-class { background: #fdf1d9; color: #93680c; }
    .book-actions { display: flex; gap: 8px; }
    .book-actions a, .book-actions button {
        padding: 8px 14px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 700;
        border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.2s;
    }
    .btn-link { background: #e1f7e7; color: #157a35; }
    .btn-link:hover { background: #c3efd0; }
    .btn-delete { background: #fde3e3; color: #b8232e; }
    .btn-delete:hover { background: #fbd0d0; }
    .empty-state { text-align: center; padding: 52px 20px; color: var(--gray-text); }
    .empty-state .icon { font-size: 52px; margin-bottom: 14px; opacity: 0.6; }
    .empty-state p { font-size: 14px; font-weight: 600; }
</style>

<script>
    const isEn = () => document.documentElement.lang === 'en';
    const gradeMap = {'الصف الأول':'1st','الصف الثاني':'2nd','الصف الثالث':'3rd','الصف الرابع':'4th','الصف الخامس':'5th'};
    const sectionMap = {'أ':'A','ب':'B','ج':'C','د':'D'};
    const tGrade = (name) => isEn() ? (gradeMap[name] || name) : name;
    const tSection = (letter) => isEn() ? (sectionMap[letter] || letter) : letter;

    loadBooks();

    function loadBooks() {
        apiFetch('/admin/library').then(books => {
            if (!books || !books.length) {
                document.getElementById('statBooks').textContent = '0';
                document.getElementById('statTeachers').textContent = '0';
                document.getElementById('statClasses').textContent = '0';
                document.getElementById('booksList').innerHTML = '<div class="empty-state"><div class="icon">📖</div><p>{{ __("لا توجد كتب بعد") }}</p></div>';
                return;
            }
            const teacherNames = [...new Set(books.map(b => b.teacher && b.teacher.user ? b.teacher.user.name : ''))];
            const allClasses = [];
            books.forEach(b => (b.classes || []).forEach(c => {
                if (!allClasses.find(x => x.id === c.id)) allClasses.push(c);
            }));
            document.getElementById('statBooks').textContent = books.length;
            document.getElementById('statTeachers').textContent = teacherNames.length;
            document.getElementById('statClasses').textContent = allClasses.length;

            document.getElementById('booksList').innerHTML = books.map(b => {
                const teacherName = (b.teacher && b.teacher.user) ? b.teacher.user.name : '—';
                const taughtSubjects = (b.teacher && b.teacher.taught_subjects) ? b.teacher.taught_subjects : [];
                const subjects = taughtSubjects.map(s => s.name).join(', ') || '—';
                const bookClasses = b.classes || [];
                const tags = bookClasses.map(c => {
                    const gradeName = (c.grade_level && c.grade_level.name) ? c.grade_level.name : c.name;
                    return '<span class="book-tag tag-class">🏫 ' + tGrade(gradeName) + ' — ' + tSection(c.section) + '</span>';
                }).join('');

                return '<div class="book-card">' +
                    '<div class="book-header">' +
                        '<div class="book-icon">📖</div>' +
                        '<div style="flex:1;">' +
                            '<div class="book-title">' + (b.title || '') + '</div>' +
                        '</div>' +
                        '<div class="book-actions">' +
                            (b.link ? '<a href="' + (b.link.match(/^https?:\/\//) ? b.link : 'https://' + b.link) + '" target="_blank" class="btn-link">🔗 {{ __("فتح") }}</a>' : '') +
                            '<button onclick="deleteBook(' + b.id + ')" class="btn-delete">🗑️</button>' +
                        '</div>' +
                    '</div>' +
                    (b.description ? '<div class="book-desc">' + b.description + '</div>' : '') +
                    '<div class="book-tags">' +
                        '<span class="book-tag tag-teacher">👨‍🏫 ' + teacherName + '</span>' +
                        '<span class="book-tag tag-subject">📚 ' + subjects + '</span>' +
                        tags +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(e => {
            console.error('Library error:', e);
            document.getElementById('booksList').innerHTML = '<div class="empty-state"><div class="icon">⚠️</div><p>{{ __("حدث خطأ في تحميل البيانات") }}</p></div>';
        });
    }

    function deleteBook(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف الكتاب؟") }}')) return;
        apiFetch('/admin/library/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadBooks(); });
    }
</script>
@stop
