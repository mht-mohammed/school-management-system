@extends('layouts.dashboard')

@section('title', __('أولياء الأمور'))
@section('page-title', __('إدارة أولياء الأمور'))

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
    <a href="/admin/parents" class="active">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('أولياء الأمور') }}</h3>
            <div style="display:flex;gap:10px;align-items:center;">
                <input id="searchParents" placeholder="{{ __('🔍 بحث...') }}" oninput="debounceParentSearch()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:200px;">
            </div>
        </div>
        <div class="loading" id="loadingParents">{{ __('جاري التحميل...') }}</div>
        <div style="overflow-x:auto;">
            <table id="parentsTable" style="display:none;">
                <thead><tr><th>{{ __('الاسم') }}</th><th>{{ __('البريد') }}</th><th>{{ __('الهاتف') }}</th><th>{{ __('الأبناء') }}</th><th style="width:120px;">{{ __('إجراءات') }}</th></tr></thead>
                <tbody id="parentsBody"></tbody>
            </table>
        </div>
        <div id="emptyParents" class="empty" style="display:none;">{{ __('لا يوجد أولياء أمور') }}</div>
    </div>

    {{-- Add / Edit Modal --}}
    <div id="parentModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:var(--radius-md);width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <h3 id="modalTitle" style="margin-bottom:15px;">{{ __('إضافة ولي أمر') }}</h3>
            <input id="editParentUserId" type="hidden">
            <div class="form-group"><label>{{ __('الاسم') }}</label><input id="fName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            <div class="form-group"><label>{{ __('البريد الإلكتروني') }}</label><input id="fEmail" type="email" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            <div class="form-group"><label>{{ __('كلمة المرور') }} <span id="passwordHint" style="color:#888;font-weight:400;font-size:12px;">{{ __('(أدخل كلمة جديدة فقط للتغيير)') }}</span></label><input id="fPassword" type="password" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            <div class="form-group"><label>{{ __('الهاتف') }}</label><input id="fPhone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            <div class="form-group">
                <label>{{ __('الصورة الشخصية') }}</label>
                <div style="display:flex;align-items:center;gap:12px;margin:5px 0 10px;">
                    <div id="pAvatarPreview" style="width:50px;height:50px;border-radius:50%;background:#e0e0e0;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:20px;color:#999;border:2px solid var(--blue-main);flex-shrink:0;">
                        <img id="pAvatarImg" style="width:100%;height:100%;object-fit:cover;display:none;">
                        <span id="pAvatarPlaceholder">👤</span>
                    </div>
                    <span id="pRemoveAvatarBtn" style="display:none;cursor:pointer;color:#b8232e;font-weight:600;font-size:13px;" onclick="removeParentAvatar()">{{ __('🗑️ حذف') }}</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button class="btn btn-primary" onclick="saveParent()" style="flex:1;">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#ddd;flex:1;" onclick="closeModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    {{-- Children Modal --}}
    <div id="childrenModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:var(--radius-md);width:90%;max-width:500px;">
            <h3 id="childrenModalTitle" style="margin-bottom:15px;">{{ __('أبناء ولي الأمر') }}</h3>
            <div id="childrenList"></div>
            <button class="btn" style="background:#ddd;width:100%;margin-top:12px;" onclick="document.getElementById('childrenModal').style.display='none'">{{ __('إغلاق') }}</button>
        </div>
    </div>

    <script>
        let allParents = [];

        function loadParents() {
            apiFetch('/admin/parents').then(list => {
                allParents = list || [];
                document.getElementById('loadingParents').style.display = 'none';
                renderParents(allParents);
            });
        }

        function renderParents(list) {
            const tbody = document.getElementById('parentsBody');
            tbody.innerHTML = '';
            if (!list.length) {
                document.getElementById('parentsTable').style.display = 'none';
                document.getElementById('emptyParents').style.display = 'block';
                return;
            }
            document.getElementById('parentsTable').style.display = '';
            document.getElementById('emptyParents').style.display = 'none';
            list.forEach(p => {
                const children = p.parent?.children || [];
                const count = children.length;
                const childrenHtml = count > 0
                    ? `<span style="color:#1976d2;font-weight:500;">${count} ${__('أبناء')}</span>`
                    : '<span style="color:#888;">—</span>';
                tbody.innerHTML += `<tr>
                    <td>${p.avatar ? `<img src="/storage/${p.avatar}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : ''}${p.name}</td>
                    <td>${p.email}</td>
                    <td>${p.phone || '-'}</td>
                    <td>${childrenHtml}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-primary btn-sm" onclick="editParent(${p.id})">✏️</button>
                        <button class="btn btn-sm" style="background:#17a2b8;color:#fff;" onclick="showChildren(${p.id})">👪</button>
                        <button class="btn btn-sm" style="background:#b8232e;color:#fff;" onclick="deleteParent(${p.id},'${p.name}')">🗑️</button>
                    </td>
                </tr>`;
            });
        }

        function filterParents() {
            const q = document.getElementById('searchParents').value.trim().toLowerCase();
            if (!q) { renderParents(allParents); return; }
            renderParents(allParents.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.email.toLowerCase().includes(q) ||
                (p.phone||'').includes(q)
            ));
        }

        let parentSearchTimeout;
        function debounceParentSearch() {
            clearTimeout(parentSearchTimeout);
            parentSearchTimeout = setTimeout(filterParents, 300);
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = __('➕ إضافة ولي أمر');
            document.getElementById('editParentUserId').value = '';
            document.getElementById('passwordHint').style.display = 'none';
            ['fName','fEmail','fPassword','fPhone'].forEach(id => document.getElementById(id).value = '');
            pRemoveAvatarFlag = false;
            document.getElementById('pAvatarImg').style.display = 'none';
            document.getElementById('pAvatarPlaceholder').style.display = '';
            document.getElementById('pRemoveAvatarBtn').style.display = 'none';
            document.getElementById('parentModal').style.display = 'flex';
        }

        function editParent(userId) {
            const p = allParents.find(x => x.id == userId);
            if (!p) return;
            document.getElementById('modalTitle').textContent = __('✏️ تعديل ولي أمر');
            document.getElementById('editParentUserId').value = p.id;
            document.getElementById('passwordHint').style.display = 'inline';
            document.getElementById('fName').value = p.name || '';
            document.getElementById('fEmail').value = p.email || '';
            document.getElementById('fPassword').value = '';
            document.getElementById('fPhone').value = p.phone || '';
            // Avatar
            pRemoveAvatarFlag = false;
            if (p.avatar) {
                document.getElementById('pAvatarImg').src = '/storage/' + p.avatar;
                document.getElementById('pAvatarImg').style.display = '';
                document.getElementById('pAvatarPlaceholder').style.display = 'none';
                document.getElementById('pRemoveAvatarBtn').style.display = '';
            } else {
                document.getElementById('pAvatarImg').style.display = 'none';
                document.getElementById('pAvatarPlaceholder').style.display = '';
                document.getElementById('pRemoveAvatarBtn').style.display = 'none';
            }
            document.getElementById('parentModal').style.display = 'flex';
        }

        function closeModal() { document.getElementById('parentModal').style.display = 'none'; }

        let pRemoveAvatarFlag = false;
        function removeParentAvatar() {
            if (!confirm(__('حذف الصورة الشخصية؟'))) return;
            pRemoveAvatarFlag = true;
            document.getElementById('pAvatarImg').style.display = 'none';
            document.getElementById('pAvatarPlaceholder').style.display = '';
            document.getElementById('pRemoveAvatarBtn').style.display = 'none';
        }

        function saveParent() {
            const id = document.getElementById('editParentUserId').value;
            const isEdit = !!id;
            const data = {
                name: document.getElementById('fName').value.trim(),
                email: document.getElementById('fEmail').value.trim(),
                phone: document.getElementById('fPhone').value.trim() || null,
            };
            const pw = document.getElementById('fPassword').value;
            if (pw) data.password = pw;

            if (!isEdit && !data.password) { showToast(__('⚠️ كلمة المرور مطلوبة للإضافة'), 'error'); return; }
            if (!data.name || !data.email) { showToast(__('⚠️ الاسم والبريد مطلوبان'), 'error'); return; }

            const url = isEdit ? '/admin/parents/' + id : '/admin/parents';
            const method = isEdit ? 'PUT' : 'POST';

            if (pRemoveAvatarFlag) data.remove_avatar = true;

            apiFetch(url, { method, body: JSON.stringify(data) }).then(r => {
                if (r.errors) {
                    const msgs = Object.values(r.errors).flat().join(' | ');
                    showToast('⚠️ ' + msgs, 'error');
                } else {
                    showToast(isEdit ? __('✅ تم تعديل ولي الأمر') : __('✅ تم إضافة ولي الأمر'));
                    closeModal();
                    setTimeout(() => location.reload(), 800);
                }
            });
        }

        function showChildren(userId) {
            const p = allParents.find(x => x.id == userId);
            const children = p?.parent?.children || [];
            document.getElementById('childrenModalTitle').textContent = __('👪 أبناء') + ' ' + p?.name;
            const container = document.getElementById('childrenList');
            if (!children.length) {
                container.innerHTML = '<div style="color:#888;padding:20px;text-align:center;">' + __('لا يوجد أبناء مسجلون') + '</div>';
            } else {
                const sorted = [...children].sort((a, b) => a.class_name.localeCompare(b.class_name) || a.name.localeCompare(b.name));
                container.innerHTML = '<div style="display:flex;flex-direction:column;gap:8px;">';
                sorted.forEach((c, i) => {
                    const sectionInfo = c.section ? `- ${__('شعبة')} ${sectionLabel(c.section)}` : '';
                    container.innerHTML += `<div style="padding:10px 14px;background:#f5f9ff;border-radius:10px;border-right:4px solid #1976d2;font-size:14px;line-height:1.7;">
                        <strong>${c.name}</strong>
                        <span style="color:#555;margin-right:8px;">${c.class_name} ${sectionInfo}</span>
                    </div>`;
                });
                container.innerHTML += '</div>';
            }
            document.getElementById('childrenModal').style.display = 'flex';
        }

        function deleteParent(userId, name) {
            if (!confirm(__('⚠️ تأكيد حذف ولي الأمر') + ' "' + name + '"؟')) return;
            apiFetch('/admin/parents/' + userId, { method: 'DELETE' }).then(r => {
                if (r.errors) { showToast('⚠️ ' + Object.values(r.errors).flat().join(' | '), 'error'); return; }
                showToast(__('✅ تم حذف ولي الأمر'));
                setTimeout(() => location.reload(), 800);
            });
        }

        loadParents();
    </script>
    <style>
        .badge-children {
            display: inline-block;
            background: #e3f2fd;
            color: #1565c0;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin: 2px 3px;
            white-space: nowrap;
        }
        .children-table { width:100%; border-collapse:collapse; }
        .children-table th, .children-table td { padding:8px 12px; text-align:right; border-bottom:1px solid #eee; }
        .children-table th { background:#f5f5f5; }
    </style>
@stop
