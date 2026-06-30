@extends('layouts.dashboard')

@section('title', __('طلبات تعديل الملف الشخصي'))
@section('page-title', __('طلبات تعديل الملف الشخصي'))

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
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests" class="active">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
<div class="card">
    <div id="requestsLoading" class="loading">{{ __('جاري التحميل...') }}</div>
    <div id="requestsContainer" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('المستخدم') }}</th>
                    <th>{{ __('الدور') }}</th>
                    <th>{{ __('التغييرات') }}</th>
                    <th>{{ __('التاريخ') }}</th>
                    <th>{{ __('الحالة') }}</th>
                    <th>{{ __('إجراءات') }}</th>
                </tr>
            </thead>
            <tbody id="requestsTableBody"></tbody>
        </table>
        <div id="pagination" style="margin-top:15px;display:flex;gap:8px;justify-content:center;"></div>
    </div>
</div>

<div id="rejectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeReject()">
    <div style="background:#fff;padding:25px;border-radius:var(--radius-md);max-width:400px;width:90%;">
        <h3 style="margin-bottom:15px;">{{ __('رفض الطلب') }}</h3>
        <textarea id="rejectNote" placeholder="{{ __('سبب الرفض...') }}" style="width:100%;min-height:80px;padding:10px;border:1px solid #ddd;border-radius:8px;font-family:'Tajawal',sans-serif;"></textarea>
        <div style="display:flex;gap:10px;margin-top:15px;">
            <button class="btn btn-primary" onclick="confirmReject()">{{ __('تأكيد الرفض') }}</button>
            <button class="btn" style="background:#ddd;" onclick="closeReject()">{{ __('إلغاء') }}</button>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentRejectId = null;

    function loadRequests(page = 1) {
        document.getElementById('requestsLoading').style.display = '';
        document.getElementById('requestsContainer').style.display = 'none';
        apiFetch('/admin/profile-requests?page=' + page).then(d => {
            document.getElementById('requestsLoading').style.display = 'none';
            document.getElementById('requestsContainer').style.display = '';
            const tbody = document.getElementById('requestsTableBody');
            if (!d.data.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="empty">' + __('لا توجد طلبات') + '</td></tr>';
                return;
            }
            tbody.innerHTML = d.data.map(r => {
                const roleLabel = r.role === 'teacher' ? __('معلم') : r.role === 'parent' ? __('ولي أمر') : __('طالب');
                const changes = Object.entries(r.changes || {}).map(([k, v]) => {
                    const labels = {
                        name: __('الاسم'), email: __('البريد الإلكتروني'), phone: __('الجوال'),
                        qualification: __('المؤهل'), specialization: __('الاختصاص'),
                        dob: __('تاريخ الميلاد'), address: __('العنوان'), guardian_phone: __('هاتف ولي الأمر'),
                        occupation: __('المهنة'),
                    };
                    return `<div style="margin-bottom:4px;"><strong>${labels[k] || k}:</strong> <span style="color:var(--blue-main);">${v}</span></div>`;
                }).join('');
                const statusBadge = r.status === 'pending' ? '<span class="badge badge-warning">' + __('قيد الانتظار') + '</span>'
                    : r.status === 'approved' ? '<span class="badge badge-success">' + __('مقبول') + '</span>'
                    : '<span class="badge badge-danger">' + __('مرفوض') + '</span>';
                const actions = r.status === 'pending'
                    ? `<button class="btn btn-sm btn-primary" onclick="approve(${r.id})">✔ ${__('قبول')}</button>
                       <button class="btn btn-sm" style="background:#b8232e;color:#fff;" onclick="openReject(${r.id})">✖ ${__('رفض')}</button>`
                    : '<span style="color:#888;font-size:13px;">' + __('تمت المعالجة') + '</span>';
                return `<tr>
                    <td>${r.id}</td>
                    <td><strong>${r.user?.avatar ? `<img src="/storage/${r.user.avatar}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : ''}${r.user?.name || ''}</strong><br><small style="color:#888;">${r.user?.email || ''}</small></td>
                    <td>${roleLabel}</td>
                    <td style="max-width:250px;">${changes}</td>
                    <td style="font-size:12px;">${new Date(r.created_at).toLocaleDateString('ar')}</td>
                    <td>${statusBadge}</td>
                    <td style="white-space:nowrap;">${actions}</td>
                </tr>`;
            }).join('');
            // Pagination
            const pg = document.getElementById('pagination');
            if (d.last_page <= 1) { pg.innerHTML = ''; return; }
            let phtml = '';
            for (let i = 1; i <= d.last_page; i++) {
                phtml += `<button class="btn btn-sm ${i === d.current_page ? 'btn-primary' : ''}" style="background:${i === d.current_page ? '' : '#eee'};" onclick="loadRequests(${i})">${i}</button>`;
            }
            pg.innerHTML = phtml;
        }).catch(e => {
            document.getElementById('requestsLoading').textContent = __('❌ خطأ في تحميل الطلبات');
        });
    }

    function approve(id) {
        if (!confirm(__('تأكيد الموافقة على الطلب؟'))) return;
        apiFetch('/admin/profile-requests/' + id + '/approve', { method: 'POST' }).then(d => {
            showToast('✅ ' + d.message);
            loadRequests(currentPage);
        }).catch(e => showToast('❌ ' + (e.message || __('خطأ')), 'error'));
    }

    function openReject(id) {
        currentRejectId = id;
        document.getElementById('rejectModal').style.display = 'flex';
        document.getElementById('rejectNote').value = '';
    }

    function closeReject() {
        document.getElementById('rejectModal').style.display = 'none';
        currentRejectId = null;
    }

    function confirmReject() {
        if (!currentRejectId) return;
        const note = document.getElementById('rejectNote').value.trim() || __('لم يتم تقديم سبب');
        apiFetch('/admin/profile-requests/' + currentRejectId + '/reject', {
            method: 'POST',
            body: JSON.stringify({ admin_note: note }),
        }).then(d => {
            showToast('✅ ' + d.message);
            closeReject();
            loadRequests(currentPage);
        }).catch(e => showToast('❌ ' + (e.message || __('خطأ')), 'error'));
    }

    loadRequests();
</script>
@endsection
