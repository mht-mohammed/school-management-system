@extends('layouts.dashboard')

@section('title', __('رسائل التواصل'))
@section('page-title', __('رسائل التواصل'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages" class="active">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
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
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('قائمة الرسائل') }}</h3>
            <div><input id="searchMessages" placeholder="{{ __('🔍 بحث...') }}" oninput="filterMessages()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:200px;"></div>
        </div>
        <div class="loading" id="loadingMessages">{{ __('جاري التحميل...') }}</div>
        <div style="overflow-x:auto;">
            <table id="messagesTable" style="display:none;">
                <thead><tr><th>{{ __('الاسم') }}</th><th>{{ __('البريد') }}</th><th>{{ __('الرسالة') }}</th><th>{{ __('الحالة') }}</th><th>{{ __('إجراءات') }}</th></tr></thead>
                <tbody id="messagesBody"></tbody>
            </table>
        </div>
    </div>
    <script>
        let allMessages = [];
        apiFetch('/admin/contact-messages').then(list => {
            allMessages = list || [];
            document.getElementById('loadingMessages').style.display = 'none';
            renderMessages(allMessages);
        }).catch(() => {
            if (document.getElementById('loadingMessages')) document.getElementById('loadingMessages').textContent = __('تعذر التحميل');
        });
        function renderMessages(list) {
            const tbody = document.getElementById('messagesBody');
            const table = document.getElementById('messagesTable');
            tbody.innerHTML = '';
            if (!list.length) { table.style.display = 'none'; document.querySelector('.card').innerHTML += '<div class="empty">' + __('لا توجد رسائل') + '</div>'; return; }
            table.style.display = '';
            list.forEach(m => {
                const statusClass = m.is_read ? 'badge-success' : 'badge-warning';
                tbody.innerHTML += `<tr>
                    <td>${m.name}</td><td>${m.email}</td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${m.message}</td>
                    <td><span class="badge ${statusClass}">${m.is_read ? __('مقروءة') : __('جديدة')}</span></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewMessage(${m.id})">👁 ${__('عرض')}</button>
                        <button class="btn btn-sm" style="background:#b8232e;color:#fff" onclick="deleteMessage(${m.id})">🗑 ${__('حذف')}</button>
                    </td>
                </tr>`;
            });
        }
        function filterMessages() {
            const q = document.getElementById('searchMessages').value.trim().toLowerCase();
            if (!q) { renderMessages(allMessages); return; }
            renderMessages(allMessages.filter(m => (m.name||'').toLowerCase().includes(q) || (m.email||'').toLowerCase().includes(q) || (m.message||'').toLowerCase().includes(q)));
        }
        async function viewMessage(id) {
            const m = await apiFetch('/admin/contact-messages/' + id);
            showToast(m.message, 'success');
        }
        async function deleteMessage(id) {
            if (!confirm(__('تأكيد الحذف؟'))) return;
            await apiFetch('/admin/contact-messages/' + id, { method: 'DELETE' });
            location.reload();
        }
    </script>
@stop
