@extends('layouts.dashboard')

@section('title', __('المكتبة'))
@section('page-title', __('المكتبة'))

@section('sidebar')
    <a href="/teacher">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/teacher/grades">📝 <span>{{ __('الدرجات') }}</span></a>
    <a href="/teacher/schedule">📅 <span>{{ __('جدولي') }}</span></a>
    <a href="/teacher/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/teacher/library" class="active">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
<div class="header">
    <h1>{{ __('المكتبة') }}</h1>
    <button class="btn btn-primary" onclick="toggleAddForm()" id="addBtn">➕ {{ __('إضافة كتاب') }}</button>
</div>

<div id="addBookForm" style="display:none;" class="card">
    <h3 id="formTitle">{{ __('إضافة كتاب جديد') }}</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div>
            <label style="font-weight:700;font-size:12px;display:block;margin-bottom:6px;color:var(--gray-text);text-transform:uppercase;letter-spacing:0.3px;">{{ __('عنوان الكتاب') }} *</label>
            <input id="bookTitle" required style="width:100%;padding:10px 14px;border:1px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;box-sizing:border-box;transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'">
        </div>
        <div>
            <label style="font-weight:700;font-size:12px;display:block;margin-bottom:6px;color:var(--gray-text);text-transform:uppercase;letter-spacing:0.3px;">{{ __('الرابط') }} *</label>
            <input id="bookLink" required placeholder="https://..." dir="ltr" onblur="fixLink(this)" style="width:100%;padding:10px 14px;border:1px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;box-sizing:border-box;transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--blue-main)'" onblur="if(!this.value||this.value.match(/^https?:\/\//))this.style.borderColor='var(--border-soft)'">
        </div>
        <div style="grid-column:1/-1;">
            <label style="font-weight:700;font-size:12px;display:block;margin-bottom:6px;color:var(--gray-text);text-transform:uppercase;letter-spacing:0.3px;">{{ __('الوصف') }}</label>
            <textarea id="bookDesc" rows="2" style="width:100%;padding:10px 14px;border:1px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;box-sizing:border-box;resize:vertical;transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></textarea>
        </div>
        <div style="grid-column:1/-1;">
            <label style="font-weight:700;font-size:12px;display:block;margin-bottom:8px;color:var(--gray-text);text-transform:uppercase;letter-spacing:0.3px;">{{ __('الصفوف') }} *</label>
            <div id="bookClassesList" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
        </div>
    </div>
    <div style="display:flex;gap:10px;margin-top:18px;">
        <button class="btn btn-primary" onclick="saveBook()" id="saveBtn">💾 {{ __('حفظ') }}</button>
        <button class="btn" style="background:var(--gray-light);color:var(--text-dark);" onclick="cancelForm()">{{ __('إلغاء') }}</button>
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
        font-size: 11.5px; font-weight: 700;
    }
    .tag-class { background: #fdf1d9; color: #93680c; }
    .book-actions { display: flex; gap: 8px; }
    .book-actions a, .book-actions button {
        padding: 8px 14px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 700;
        border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.2s;
    }
    .btn-link { background: #e1f7e7; color: #157a35; }
    .btn-link:hover { background: #c3efd0; }
    .btn-edit { background: #eef1fd; color: var(--blue-main); }
    .btn-edit:hover { background: #dde5fb; }
    .btn-delete { background: #fde3e3; color: #b8232e; }
    .btn-delete:hover { background: #fbd0d0; }
    .class-chip {
        display: flex; align-items: center; gap: 6px; background: var(--white); border: 1px solid var(--border-soft);
        border-radius: var(--radius-sm); padding: 8px 14px; cursor: pointer; font-size: 13px; font-weight: 600;
        transition: all 0.2s; user-select: none;
    }
    .class-chip:hover { border-color: var(--blue-main); background: var(--blue-50); }
    .class-chip.selected { border-color: var(--blue-main); background: #eef1fd; color: var(--blue-main); }
    .class-chip input { display: none; }
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
    let mySections = [];
    let editingId = null;

    loadLibrary();

    function toggleAddForm() {
        const f = document.getElementById('addBookForm');
        f.style.display = f.style.display === 'none' ? '' : 'none';
        if (f.style.display === 'none') resetForm();
    }

    function cancelForm() {
        document.getElementById('addBookForm').style.display = 'none';
        resetForm();
    }

    function resetForm() {
        editingId = null;
        document.getElementById('bookTitle').value = '';
        document.getElementById('bookLink').value = '';
        document.getElementById('bookDesc').value = '';
        document.querySelectorAll('.class-chip').forEach(c => c.classList.remove('selected'));
        document.getElementById('formTitle').textContent = '{{ __("إضافة كتاب جديد") }}';
        document.getElementById('saveBtn').textContent = '💾 {{ __("حفظ") }}';
    }

    function fixLink(input) {
        const v = input.value.trim();
        if (v && !v.match(/^https?:\/\//)) input.value = 'https://' + v;
    }

    function loadLibrary() {
        apiFetch('/teacher/elearning/sections').then(sections => {
            mySections = sections;
            renderClassesCheckboxes();
        });
        apiFetch('/teacher/library').then(books => {
            const l = document.getElementById('booksList');
            if (!books || !books.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📖</div><p>{{ __("لا توجد كتب بعد") }}</p></div>'; return; }
            l.innerHTML = books.map(b => {
                const bookClasses = b.classes || [];
                const tags = bookClasses.map(c =>
                    '<span class="book-tag tag-class">🏫 ' + tGrade(c.name) + ' — ' + tSection(c.section) + '</span>'
                ).join('');
                const classIds = bookClasses.map(c => c.id);
                const escapedTitle = (b.title || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const escapedLink = (b.link || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const escapedDesc = (b.description || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

                return '<div class="book-card">' +
                    '<div class="book-header">' +
                        '<div class="book-icon">📖</div>' +
                        '<div style="flex:1;">' +
                            '<div class="book-title">' + (b.title || '') + '</div>' +
                        '</div>' +
                        '<div class="book-actions">' +
                            (b.link ? '<a href="' + (b.link.match(/^https?:\/\//) ? b.link : 'https://' + b.link) + '" target="_blank" class="btn-link">🔗</a>' : '') +
                            '<button onclick="openEdit(' + b.id + ',\'' + escapedTitle + '\',\'' + escapedLink + '\',\'' + escapedDesc + '\',[' + classIds.join(',') + '])" class="btn-edit">✏️</button>' +
                            '<button onclick="deleteBook(' + b.id + ')" class="btn-delete">🗑️</button>' +
                        '</div>' +
                    '</div>' +
                    (b.description ? '<div class="book-desc">' + b.description + '</div>' : '') +
                    '<div class="book-tags">' + tags + '</div>' +
                '</div>';
            }).join('');
        });
    }

    function renderClassesCheckboxes() {
        document.getElementById('bookClassesList').innerHTML = mySections.map(c =>
            '<label class="class-chip" onclick="this.classList.toggle(\'selected\')">' +
                '<input type="checkbox" value="' + c.id + '" class="bookClassCb" style="display:none;">' +
                '🏫 ' + tGrade(c.name) + ' — ' + tSection(c.section) +
            '</label>'
        ).join('');
    }

    function saveBook() {
        const classIds = [...document.querySelectorAll('.bookClassCb:checked')].map(cb => parseInt(cb.value));
        if (!document.getElementById('bookTitle').value) { showToast('{{ __("العنوان مطلوب") }}', 'error'); return; }
        if (!document.getElementById('bookLink').value) { showToast('{{ __("الرابط مطلوب") }}', 'error'); return; }
        if (!classIds.length) { showToast('{{ __("اختر صف واحد على الأقل") }}', 'error'); return; }

        const payload = {
            title: document.getElementById('bookTitle').value,
            link: document.getElementById('bookLink').value || null,
            description: document.getElementById('bookDesc').value || null,
            class_ids: classIds,
        };

        const url = editingId ? '/teacher/library/' + editingId : '/teacher/library';
        const method = editingId ? 'PUT' : 'POST';

        apiFetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
            .then(d => { showToast(d.message); document.getElementById('addBookForm').style.display = 'none'; resetForm(); loadLibrary(); })
            .catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function openEdit(id, title, link, desc, classIds) {
        editingId = id;
        document.getElementById('bookTitle').value = title.replace(/&quot;/g, '"');
        document.getElementById('bookLink').value = link.replace(/&quot;/g, '"');
        document.getElementById('bookDesc').value = desc.replace(/&quot;/g, '"');
        document.querySelectorAll('.class-chip').forEach(c => {
            const cb = c.querySelector('.bookClassCb');
            if (classIds.includes(parseInt(cb.value))) { c.classList.add('selected'); cb.checked = true; } else { c.classList.remove('selected'); cb.checked = false; }
        });
        document.getElementById('formTitle').textContent = '{{ __("تعديل الكتاب") }}';
        document.getElementById('saveBtn').textContent = '💾 {{ __("تحديث") }}';
        document.getElementById('addBookForm').style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function deleteBook(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف الكتاب؟") }}')) return;
        apiFetch('/teacher/library/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadLibrary(); });
    }
</script>
@stop
