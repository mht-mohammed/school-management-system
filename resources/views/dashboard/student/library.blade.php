@extends('layouts.dashboard')

@section('title', __('المكتبة'))
@section('page-title', __('المكتبة'))

@section('sidebar')
    <a href="/student">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/student/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/student/library" class="active">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
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
    .book-header { display: flex; align-items: center; gap: 14px; }
    .book-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--violet), var(--blue-main));
        display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--white);
        box-shadow: 0 6px 16px rgba(67,56,202,0.25);
    }
    .book-title { font-weight: 800; font-size: 15px; color: var(--text-dark); }
    .book-teacher { color: var(--gray-text); font-size: 12px; font-weight: 600; margin-top: 2px; }
    .book-desc { color: var(--gray-text); font-size: 13px; line-height: 1.6; margin: 10px 0 0; }
    .btn-open {
        padding: 10px 20px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 700;
        border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #157a35, #1fa548); color: var(--white);
        box-shadow: 0 4px 12px rgba(21,122,53,0.25); transition: all 0.2s;
    }
    .btn-open:hover { filter: brightness(1.1); transform: translateY(-1px); }
    .empty-state { text-align: center; padding: 52px 20px; color: var(--gray-text); }
    .empty-state .icon { font-size: 52px; margin-bottom: 14px; opacity: 0.6; }
    .empty-state p { font-size: 14px; font-weight: 600; }
</style>

<script>
    loadBooks();

    function loadBooks() {
        apiFetch('/student/library').then(books => {
            const l = document.getElementById('booksList');
            if (!books || !books.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📖</div><p>{{ __("لا توجد كتب متاحة لصفوفك") }}</p></div>'; return; }
            l.innerHTML = books.map(b => {
                const teacherName = (b.teacher && b.teacher.user) ? b.teacher.user.name : '—';
                return '<div class="book-card">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<div class="book-header" style="flex:1;">' +
                            '<div class="book-icon">📖</div>' +
                            '<div>' +
                                '<div class="book-title">' + (b.title || '') + '</div>' +
                                '<div class="book-teacher">👨‍🏫 ' + teacherName + '</div>' +
                            '</div>' +
                        '</div>' +
                        (b.link ? '<a href="' + (b.link.match(/^https?:\/\//) ? b.link : 'https://' + b.link) + '" target="_blank" class="btn-open">🔗 {{ __("فتح") }}</a>' : '') +
                    '</div>' +
                    (b.description ? '<div class="book-desc">' + b.description + '</div>' : '') +
                '</div>';
            }).join('');
        });
    }
</script>
@stop
