@extends('layouts.dashboard')

@section('title', __('المعلمون'))
@section('page-title', __('إدارة المعلمين'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers" class="active">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
@stop

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('قائمة المعلمين') }}</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <input id="searchTeachers" placeholder="{{ __('🔍 بحث...') }}" oninput="filterTeachers()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:200px;">
                <button class="btn btn-sm" style="background:#6c757d;color:#fff;" onclick="toggleGrouped()" id="groupToggleBtn">{{ __('📂 عرض حسب التخصص') }}</button>
            </div>
        </div>
        <div class="loading" id="loadingTeachers">{{ __('جاري التحميل...') }}</div>
        <div style="overflow-x:auto;">
            <table id="teachersTable" style="display:none;">
                <thead><tr><th>{{ __('الاسم') }}</th><th>{{ __('البريد') }}</th><th>{{ __('رقم الهاتف') }}</th><th>{{ __('التخصص') }}</th><th>{{ __('المؤهل') }}</th><th>{{ __('الراتب') }}</th><th>{{ __('عدد الحصص') }}</th><th>{{ __('الصفوف') }}</th><th style="width:200px;">{{ __('إجراءات') }}</th></tr></thead>
                <tbody id="teachersBody"></tbody>
            </table>
        </div>
        <div id="emptyTeachers" class="empty" style="display:none;">{{ __('لا يوجد معلمون') }}</div>
    </div>

    {{-- Add / Edit Modal --}}
    <div id="teacherFormModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:28px;border-radius:14px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <h3 id="formModalTitle" style="margin-bottom:18px;">{{ __('إضافة معلم جديد') }}</h3>
            <input id="editTeacherId" type="hidden">
            <div class="form-group"><label>{{ __('الاسم') }}</label><input id="fName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('البريد الإلكتروني') }}</label><input id="fEmail" type="email" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('رقم الهاتف') }}</label><input id="fPhone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('كلمة المرور') }} <span id="passwordHint" style="color:#888;font-weight:400;font-size:12px;">{{ __('(أدخل كلمة جديدة فقط للتغيير)') }}</span></label><input id="fPassword" type="password" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('التخصص') }}</label><input id="fSpecialization" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('المؤهل') }}</label><input id="fQualification" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('الراتب') }}</label><input id="fSalary" type="number" step="0.01" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 12px;"></div>
            <div class="form-group"><label>{{ __('تاريخ التعيين') }}</label><input id="fHireDate" type="date" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:4px 0 16px;"></div>
                    <div class="form-group">
                <label>{{ __('الصورة الشخصية') }}</label>
                <div style="display:flex;align-items:center;gap:12px;margin:4px 0 12px;">
                    <div id="avatarPreview" style="width:50px;height:50px;border-radius:50%;background:#e0e0e0;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:20px;color:#999;border:2px solid var(--blue-main);flex-shrink:0;">
                        <img id="fAvatarImg" style="width:100%;height:100%;object-fit:cover;display:none;">
                        <span id="fAvatarPlaceholder">👤</span>
                    </div>
                    <span id="fRemoveAvatarBtn" style="display:none;cursor:pointer;color:#dc3545;font-weight:600;font-size:13px;" onclick="removeTeacherAvatar()">{{ __('🗑️ حذف') }}</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveTeacherForm()" style="flex:1;">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#ddd;flex:1;" onclick="closeFormModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:28px;border-radius:14px;width:90%;max-width:400px;text-align:center;">
            <div style="font-size:48px;margin-bottom:10px;">⚠️</div>
            <h3 style="margin-bottom:8px;">{{ __('حذف معلم') }}</h3>
            <p id="deleteTeacherName" style="color:#888;margin-bottom:20px;"></p>
            <input id="deleteTeacherId" type="hidden">
            <div style="display:flex;gap:10px;">
                <button class="btn" style="background:#dc3545;color:#fff;flex:1;" onclick="confirmDelete()">{{ __('🗑️ تأكيد الحذف') }}</button>
                <button class="btn" style="background:#ddd;flex:1;" onclick="closeDeleteModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    <script>
        let allTeachers = [];
        let editingId = null;
        let groupedView = false;

        let workloadMap = {};
        let teacherSectionsMap = {}; // teacher_id -> [{name, section}]

        Promise.all([
            apiFetch('/admin/teachers'),
            apiFetch('/admin/schedules')
        ]).then(([list, schedules]) => {
            allTeachers = (list || []).filter(u => u.role === 'teacher');
            (schedules || []).forEach(s => {
                if (s.teacher_id) {
                    workloadMap[s.teacher_id] = (workloadMap[s.teacher_id] || 0) + 1;
                    if (!teacherSectionsMap[s.teacher_id]) teacherSectionsMap[s.teacher_id] = [];
                    const sec = s.section;
                    if (sec && !teacherSectionsMap[s.teacher_id].some(x => x.id === sec.id)) {
                        teacherSectionsMap[s.teacher_id].push({ id: sec.id, name: sec.name, section: sec.section || '' });
                    }
                }
            });
            document.getElementById('loadingTeachers').style.display = 'none';
            renderTeachers(allTeachers);
        });

        function toggleGrouped() {
            groupedView = !groupedView;
            document.getElementById('groupToggleBtn').textContent = groupedView ? __('📋 عرض عادي') : __('📂 عرض حسب التخصص');
            filterTeachers();
        }

        function renderTeachers(list) {
            const tbody = document.getElementById('teachersBody');
            tbody.innerHTML = '';
            if (!list.length) {
                document.getElementById('teachersTable').style.display = 'none';
                document.getElementById('emptyTeachers').style.display = 'block';
                return;
            }
            document.getElementById('teachersTable').style.display = '';
            document.getElementById('emptyTeachers').style.display = 'none';

            if (groupedView) {
                const groups = {};
                list.forEach(u => {
                    const spec = u.teacher?.specialization || __('بدون تخصص');
                    if (!groups[spec]) groups[spec] = [];
                    groups[spec].push(u);
                });
                const sortedSpecs = Object.keys(groups).sort();
                sortedSpecs.forEach(spec => {
                    tbody.innerHTML += `<tr style="background:#f0f4f8;font-weight:700;"><td colspan="9" style="padding:10px 14px;font-size:15px;">📂 ${spec} (${groups[spec].length})</td></tr>`;
                    groups[spec].forEach(u => renderRow(u, tbody));
                });
            } else {
                list.forEach(u => renderRow(u, tbody));
            }
        }

        function renderRow(u, tbody) {
            const t = u.teacher || {};
            const wl = workloadMap[u.id] || 0;
            const wlColor = wl === 0 ? 'color:#dc3545;font-weight:700;' : '';
            const secs = teacherSectionsMap[u.id] || [];
            const classHtml = secs.length
                ? secs.map(c => `<span class="badge-class success">${c.name} ${c.section ? __('شعبة') + ' ' + sectionLabel(c.section) : ''}</span>`).join('')
                : '<span class="badge-class primary">' + __('بدون') + '</span>';
            tbody.innerHTML += `<tr>
                <td>${u.avatar ? `<img src="/storage/${u.avatar}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : ''}${u.name}</td>
                <td>${u.email}</td>
                <td>${u.phone || '-'}</td>
                <td>${t.specialization || '-'}</td>
                <td>${t.qualification || '-'}</td>
                <td>${t.salary ? t.salary + ' ' + __('ريال') : '-'}</td>
                <td style="${wlColor}">${wl}</td>
                <td>${classHtml}</td>
                <td style="white-space:nowrap;">
                    <button class="btn btn-primary btn-sm" onclick="editTeacher(${u.id})">${__('✏️ تعديل')}</button>
                    <button class="btn btn-sm" style="background:#dc3545;color:#fff;" onclick="openDeleteModal(${u.id}, '${u.name}')">🗑️</button>
                </td>
            </tr>`;
        }

        function filterTeachers() {
            const q = document.getElementById('searchTeachers').value.trim().toLowerCase();
            if (!q) { renderTeachers(allTeachers); return; }
            renderTeachers(allTeachers.filter(u =>
                u.name.toLowerCase().includes(q) ||
                u.email.toLowerCase().includes(q) ||
                (u.teacher?.specialization || '').toLowerCase().includes(q)
            ));
        }

        // ---- Add / Edit ----
        function openAddModal() {
            editingId = null;
            document.getElementById('formModalTitle').textContent = __('➕ إضافة معلم جديد');
            document.getElementById('passwordHint').style.display = 'none';
            ['fName','fEmail','fPhone','fPassword','fSpecialization','fQualification','fSalary','fHireDate','editTeacherId'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('teacherFormModal').style.display = 'flex';
        }

        function editTeacher(userId) {
            const u = allTeachers.find(x => x.id == userId);
            if (!u || !u.teacher) { showToast(__('⚠️ سجل المعلم غير مكتمل'), 'error'); return; }
            editingId = u.teacher.id;
            const t = u.teacher;
            document.getElementById('formModalTitle').textContent = __('✏️ تعديل معلم');
            document.getElementById('passwordHint').style.display = 'inline';
            document.getElementById('editTeacherId').value = t.id;
            document.getElementById('fName').value = u.name || '';
            document.getElementById('fEmail').value = u.email || '';
            document.getElementById('fPhone').value = u.phone || '';
            document.getElementById('fPassword').value = '';
            document.getElementById('fSpecialization').value = t.specialization || '';
            document.getElementById('fQualification').value = t.qualification || '';
            document.getElementById('fSalary').value = t.salary || '';
            document.getElementById('fHireDate').value = t.hire_date ? t.hire_date.substring(0,10) : '';
            // Avatar
            fRemoveAvatar = false;
            if (u.avatar) {
                document.getElementById('fAvatarImg').src = '/storage/' + u.avatar;
                document.getElementById('fAvatarImg').style.display = '';
                document.getElementById('fAvatarPlaceholder').style.display = 'none';
                document.getElementById('fRemoveAvatarBtn').style.display = '';
            } else {
                document.getElementById('fAvatarImg').style.display = 'none';
                document.getElementById('fAvatarPlaceholder').style.display = '';
                document.getElementById('fRemoveAvatarBtn').style.display = 'none';
            }
            document.getElementById('teacherFormModal').style.display = 'flex';
        }

        function closeFormModal() { document.getElementById('teacherFormModal').style.display = 'none'; }

        let fRemoveAvatar = false;
        function removeTeacherAvatar() {
            if (!confirm(__('حذف الصورة الشخصية؟'))) return;
            fRemoveAvatar = true;
            document.getElementById('fAvatarImg').style.display = 'none';
            document.getElementById('fAvatarPlaceholder').style.display = '';
            document.getElementById('fRemoveAvatarBtn').style.display = 'none';
        }

        function saveTeacherForm() {
            const data = {
                name: document.getElementById('fName').value.trim(),
                email: document.getElementById('fEmail').value.trim(),
                phone: document.getElementById('fPhone').value.trim() || null,
                specialization: document.getElementById('fSpecialization').value.trim() || null,
                qualification: document.getElementById('fQualification').value.trim() || null,
                salary: document.getElementById('fSalary').value || null,
                hire_date: document.getElementById('fHireDate').value || null,
            };
            const pw = document.getElementById('fPassword').value;
            if (pw) data.password = pw;

            const id = document.getElementById('editTeacherId').value;
            const isEdit = !!id;
            const url = isEdit ? '/admin/teachers/' + id : '/admin/teachers';
            const method = isEdit ? 'PUT' : 'POST';

            if (!isEdit && !data.password) { showToast(__('⚠️ كلمة المرور مطلوبة للإضافة')); return; }
            if (!data.name || !data.email) { showToast(__('⚠️ الاسم والبريد الإلكتروني مطلوبان')); return; }

            if (fRemoveAvatar) data.remove_avatar = true;

            apiFetch(url, { method, body: JSON.stringify(data) }).then(r => {
                if (r.errors) {
                    const msgs = Object.values(r.errors).flat().join(' | ');
                    showToast('⚠️ ' + msgs, 'error');
                } else {
                    showToast(isEdit ? __('✅ تم تعديل المعلم') : __('✅ تم إضافة المعلم'));
                    closeFormModal();
                    setTimeout(() => location.reload(), 800);
                }
            });
        }

        // ---- Delete ----
        function openDeleteModal(userId, userName) {
            const t = allTeachers.find(x => x.id == userId);
            if (!t || !t.teacher) return;
            document.getElementById('deleteTeacherId').value = t.teacher.id;
            document.getElementById('deleteTeacherName').textContent = __('هل أنت متأكد من حذف المعلم') + ' "' + userName + '"؟';
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }

        function confirmDelete() {
            const id = document.getElementById('deleteTeacherId').value;
            apiFetch('/admin/teachers/' + id, { method: 'DELETE' }).then(() => {
                showToast(__('✅ تم حذف المعلم'));
                closeDeleteModal();
                setTimeout(() => location.reload(), 800);
            });
        }
    </script>
    <style>
        .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
        .badge-class { display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;margin:2px; }
        .badge-class.primary { background:#e8f0fe;color:#1a73e8; }
        .badge-class.success { background:#e6f7e6;color:#28a745; }
    </style>
@stop
