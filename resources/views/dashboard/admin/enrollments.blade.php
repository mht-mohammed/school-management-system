@extends('layouts.dashboard')

@section('title', __('طلبات الالتحاق'))
@section('page-title', __('إدارة طلبات الالتحاق'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments" class="active">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
@stop

@section('content')
    <style>
        .filter-tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
        .filter-tab { padding:8px 20px; border-radius:20px; border:1px solid #ddd; background:#fff; cursor:pointer; font-size:14px; font-weight:600; transition:all 0.2s; }
        .filter-tab:hover { border-color:var(--blue-main); }
        .filter-tab.active { background:var(--blue-main); color:#fff; border-color:var(--blue-main); }
        .filter-tab .count { display:inline-block; background:rgba(0,0,0,0.1); border-radius:10px; padding:0 8px; margin-right:4px; font-size:12px; }
        .filter-tab.active .count { background:rgba(255,255,255,0.25); }

        .req-card { border:1px solid #e8e8e8; border-radius:14px; padding:16px 20px; margin-bottom:12px; background:#fff; transition:all 0.2s; }
        .req-card:hover { border-color:#c0c0c0; box-shadow:0 2px 12px rgba(0,0,0,0.04); }
        .req-card.pending { border-right:4px solid #ffc107; }
        .req-card.approved { border-right:4px solid #28a745; }
        .req-card.rejected { border-right:4px solid #dc3545; }

        .guardian-info, .student-info { display:flex; flex-wrap:wrap; gap:6px 20px; font-size:14px; }
        .guardian-info .label, .student-info .label { color:#888; font-size:13px; }
        .guardian-info .value, .student-info .value { font-weight:600; }
        .section-title { font-size:13px; font-weight:700; color:#666; margin-bottom:6px; display:flex; align-items:center; gap:6px; }

        .credentials-modal { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; display:none; align-items:center; justify-content:center; }
        .credentials-modal.open { display:flex; }
        .credentials-modal .box { background:#fff; border-radius:16px; padding:30px; width:90%; max-width:450px; text-align:center; }
        .credentials-modal .box h2 { color:var(--blue-main); margin-bottom:8px; }
        .cred-row { background:#f8f9fb; border-radius:10px; padding:12px 16px; margin:8px 0; text-align:right; }
        .cred-row .lbl { font-size:13px; color:#888; }
        .cred-row .val { font-weight:700; font-size:15px; direction:ltr; display:inline-block; }

        .action-btn { padding:8px 18px; border:none; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; transition:all 0.2s; }
        .action-btn.approve { background:#d4edda; color:#155724; }
        .action-btn.approve:hover { background:#28a745; color:#fff; }
        .action-btn.reject { background:#f8d7da; color:#721c24; }
        .action-btn.reject:hover { background:#dc3545; color:#fff; }
        .action-btn:disabled { opacity:0.5; cursor:not-allowed; }

        .empty-state { text-align:center; padding:60px 20px; color:#aaa; }
        .empty-state .icon { font-size:48px; margin-bottom:10px; }
    </style>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('📋 طلبات الالتحاق') }}</h3>
            <input id="searchEnrollments" placeholder="{{ __('🔍 بحث باسم ولي الأمر أو الطالب...') }}" oninput="debounceEnrollmentSearch()" style="padding:8px 14px;border:1px solid #ddd;border-radius:20px;min-width:240px;font-size:14px;">
        </div>

        <div class="filter-tabs" id="filterTabs">
            <div class="filter-tab active" data-filter="all" onclick="setFilter('all')">{{ __('الكل') }} <span class="count" id="countAll">0</span></div>
            <div class="filter-tab" data-filter="pending" onclick="setFilter('pending')">{{ __('⏳ قيد الانتظار') }} <span class="count" id="countPending">0</span></div>
            <div class="filter-tab" data-filter="approved" onclick="setFilter('approved')">{{ __('✅ مقبول') }} <span class="count" id="countApproved">0</span></div>
            <div class="filter-tab" data-filter="rejected" onclick="setFilter('rejected')">{{ __('❌ مرفوض') }} <span class="count" id="countRejected">0</span></div>
        </div>

        <div class="loading" id="loadingEnrollments">{{ __('جاري تحميل الطلبات...') }}</div>
        <div id="enrollmentsContainer"></div>
    </div>

    <div class="credentials-modal" id="credentialsModal">
        <div class="box">
            <div style="font-size:48px;margin-bottom:6px;">🎉</div>
            <h2>{{ __('تم قبول الطلب بنجاح!') }}</h2>
            <p style="color:#666;margin-bottom:16px;">{{ __('تم إنشاء الحسابات التالية:') }}</p>
            <div id="credentialsContent"></div>
            <button class="btn btn-primary" onclick="closeCredentials()" style="margin-top:16px;">{{ __('✔ حسناً') }}</button>
        </div>
    </div>

    <script>
        let allEnrollments = [];
        let currentFilter = 'all';

        apiFetch('/admin/enrollments').then(list => {
            allEnrollments = list || [];
            document.getElementById('loadingEnrollments').style.display = 'none';
            updateCounts();
            renderEnrollments(allEnrollments);
        }).catch(() => {
            const el = document.getElementById('loadingEnrollments');
            if (el) el.textContent = __('❌ تعذر التحميل — تحقق من الاتصال');
        });

        function updateCounts() {
            setNum('countAll', allEnrollments.length);
            setNum('countPending', allEnrollments.filter(e => e.status === 'pending').length);
            setNum('countApproved', allEnrollments.filter(e => e.status === 'approved').length);
            setNum('countRejected', allEnrollments.filter(e => e.status === 'rejected').length);
        }

        function setFilter(filter) {
            currentFilter = filter;
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.toggle('active', t.dataset.filter === filter));
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('searchEnrollments').value.trim().toLowerCase();
            let filtered = allEnrollments;
            if (currentFilter !== 'all') filtered = filtered.filter(e => e.status === currentFilter);
            if (q) filtered = filtered.filter(e =>
                (e.guardian_name||'').toLowerCase().includes(q) ||
                (e.guardian_email||'').toLowerCase().includes(q) ||
                (e.student_name||'').toLowerCase().includes(q)
            );
            renderEnrollments(filtered);
        }

        function filterEnrollments() { applyFilters(); }

        let enrollmentSearchTimeout;
        function debounceEnrollmentSearch() {
            clearTimeout(enrollmentSearchTimeout);
            enrollmentSearchTimeout = setTimeout(filterEnrollments, 300);
        }

        function renderEnrollments(list) {
            const container = document.getElementById('enrollmentsContainer');
            container.innerHTML = '';
            if (!list.length) {
                container.innerHTML = '<div class="empty-state"><div class="icon">📭</div>' + __('لا توجد طلبات') + (currentFilter !== 'all' ? __(' في هذا التصنيف') : '') + '</div>';
                return;
            }
            list.forEach(e => {
                const statusLabels = { pending: __('⏳ قيد الانتظار'), approved: __('✅ مقبول'), rejected: __('❌ مرفوض') };
                const createdAt = e.created_at ? new Date(e.created_at).toLocaleDateString('ar-SA', { year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';
                container.innerHTML += `
                    <div class="req-card ${e.status}">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                            <div style="flex:1;min-width:200px;">
                                <div class="section-title">${__('👤 ولي الأمر')}</div>
                                <div class="guardian-info">
                                    <div><span class="label">${__('الاسم:')}</span> <span class="value">${e.guardian_name || '—'}</span></div>
                                    <div><span class="label">${__('الإيميل:')}</span> <span class="value" dir="ltr">${e.guardian_email || '—'}</span></div>
                                    <div><span class="label">${__('الجوال:')}</span> <span class="value" dir="ltr">${e.guardian_phone || '—'}</span></div>
                                </div>
                            </div>
                            <div style="flex:1;min-width:200px;">
                                <div class="section-title">${__('🎓 الطالب')}</div>
                                <div class="student-info">
                                    <div><span class="label">${__('الاسم:')}</span> <span class="value">${e.student_name}</span></div>
                                    <div><span class="label">${__('تاريخ الميلاد:')}</span> <span class="value">${e.dob || '—'}</span></div>
                                    <div><span class="label">${__('المرحلة:')}</span> <span class="value">${e.stage || '—'}</span></div>
                                </div>
                            </div>
                            <div style="text-align:left;min-width:120px;">
                                <div style="margin-bottom:8px;"><span class="badge ${e.status === 'approved' ? 'badge-success' : e.status === 'rejected' ? 'badge-danger' : 'badge-warning'}">${statusLabels[e.status] || e.status}</span></div>
                                <div style="font-size:12px;color:#999;">${createdAt}</div>
                            </div>
                        </div>
                        ${e.status === 'pending' ? `
                        <div style="display:flex;gap:10px;margin-top:14px;padding-top:12px;border-top:1px solid #eee;">
                            <button class="action-btn approve" onclick="approveEnrollment(${e.id}, event)">${__('✔️ قبول الطلب')}</button>
                            <button class="action-btn reject" onclick="rejectEnrollment(${e.id})">${__('✖️ رفض الطلب')}</button>
                        </div>` : ''}
                        ${e.status === 'approved' && e.notes ? `<div style="margin-top:8px;font-size:13px;color:#666;">${__('📝 ')}${e.notes}</div>` : ''}
                        ${e.status === 'approved' && e.user ? `
                        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #eee;font-size:13px;color:#28a745;">
                            ${__('🎓 حساب الطالب: ')}<strong>${e.user.avatar ? `<img src="/storage/${e.user.avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:4px;">` : ''}${e.user.name}</strong> — ${e.user.email}
                        </div>` : ''}
                    </div>
                `;
            });
        }

        async function approveEnrollment(id, event) {
            if (!confirm(__('سيتم إنشاء حساب لولي الأمر والطالب. هل أنت متأكد؟'))) return;
            const btn = event.target;
            btn.disabled = true; btn.textContent = __('⏳ جاري...');
            try {
                const data = await apiFetch('/admin/enrollments/' + id, { method: 'PUT', body: JSON.stringify({ status: 'approved' }) });
                if (data.guardian) {
                    let html = '';
                    html += '<div class="cred-row"><div class="lbl">' + __('👤 ولي الأمر') + '</div><div class="val">' + data.guardian.email + '</div>';
                    if (data.guardian.is_new) html += '<div style="font-size:13px;color:#888;">' + __('كلمة المرور: ') + '<strong>' + data.guardian.password + '</strong></div>';
                    else html += '<div style="font-size:13px;color:#28a745;">' + __('✔ حساب موجود مسبقاً') + '</div>';
                    html += '</div>';
                    if (data.student_user) {
                        html += '<div class="cred-row"><div class="lbl">' + __('🎓 الطالب') + '</div><div class="val">' + data.student_user.email + '</div>';
                        html += '<div style="font-size:13px;color:#888;">' + __('كلمة المرور: ') + '<strong>' + data.student_user.password + '</strong></div></div>';
                    }
                    document.getElementById('credentialsContent').innerHTML = html;
                    document.getElementById('credentialsModal').classList.add('open');
                }
                setTimeout(() => location.reload(), 3000);
            } catch(e) {
                showToast(__('حدث خطأ: ') + e.message, 'error');
                btn.disabled = false; btn.textContent = __('✔️ قبول الطلب');
            }
        }

        async function rejectEnrollment(id) {
            if (!confirm(__('هل أنت متأكد من رفض الطلب؟'))) return;
            try {
                await apiFetch('/admin/enrollments/' + id, { method: 'PUT', body: JSON.stringify({ status: 'rejected' }) });
                showToast(__('❌ تم رفض الطلب'));
                setTimeout(() => location.reload(), 1000);
            } catch(e) {
                showToast(__('حدث خطأ: ') + e.message, 'error');
            }
        }

        function closeCredentials() {
            document.getElementById('credentialsModal').classList.remove('open');
        }
    </script>
@stop
