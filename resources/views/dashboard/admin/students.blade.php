@extends('layouts.dashboard')

@section('title', __('الطلاب'))
@section('page-title', __('إدارة الطلاب'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students" class="active">🎓 <span>{{ __('الطلاب') }}</span></a>
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
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('قائمة الطلاب') }}</h3>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <select id="filterStudentClass" onchange="onGradeLevelChange()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:150px;">
                    <option value="">{{ __('🏫 جميع الصفوف') }}</option>
                </select>
                <select id="filterStudentSection" onchange="filterStudents()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:150px;">
                    <option value="">{{ __('📚 جميع الشُعب') }}</option>
                </select>
                <input id="searchStudents" placeholder="{{ __('🔍 بحث...') }}" oninput="debounceSearch()" style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;min-width:200px;">
            </div>
        </div>
        <div class="loading" id="loadingStudents">{{ __('جاري التحميل...') }}</div>
        <div style="overflow-x:auto;">
            <table id="studentsTable" style="display:none;">
                <thead><tr><th>{{ __('الطالب') }}</th><th>{{ __('الصف / الشعبة') }}</th><th>{{ __('ولي الأمر') }}</th><th>{{ __('الهاتف') }}</th><th>{{ __('تاريخ التسجيل') }}</th><th>{{ __('الحالة') }}</th><th>{{ __('إجراءات') }}</th></tr></thead>
                <tbody id="studentsBody"></tbody>
            </table>
        </div>
    </div>

    <div id="studentModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:14px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <h3 style="margin-bottom:15px;">{{ __('تعديل طالب') }}</h3>
            <input id="editStudentId" type="hidden">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label>{{ __('الاسم') }}</label><input id="editStudentName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                <div class="form-group"><label>{{ __('البريد') }}</label><input id="editStudentEmail" type="email" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                <div class="form-group"><label>{{ __('رقم الجوال') }}</label><input id="editStudentPhone" type="text" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                <div class="form-group"><label>{{ __('تاريخ الميلاد') }}</label><input id="editStudentDob" type="date" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                <div class="form-group"><label>{{ __('الشعبة / الصف') }}</label><select id="editStudentSection" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"><option value="">{{ __('بدون شعبة') }}</option></select></div>
                <div class="form-group"><label>{{ __('الحالة') }}</label><select id="editStudentStatus" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;">
                    <option value="active">{{ __('نشط') }}</option><option value="inactive">{{ __('إيقاف مؤقت') }}</option><option value="graduated">{{ __('متخرج') }}</option><option value="transferred">{{ __('منقول') }}</option>
                </select></div>
                <div class="form-group"><label>{{ __('تاريخ التسجيل') }}</label><input id="editStudentEnrollDate" type="date" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                <div class="form-group"><label>{{ __('هاتف ولي الأمر') }}</label><input id="editStudentGuardian" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            </div>
            <div class="form-group"><label>{{ __('كلمة المرور (اترك فارغاً إذا لا تريد التغيير)') }}</label><input id="editStudentPassword" type="password" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
            <div class="form-group"><label>{{ __('العنوان') }}</label><input id="editStudentAddress" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 10px;"></div>
                    <div class="form-group">
                <label>{{ __('الصورة الشخصية') }}</label>
                <div style="display:flex;align-items:center;gap:12px;margin:5px 0 15px;">
                    <div id="sAvatarPreview" style="width:50px;height:50px;border-radius:50%;background:#e0e0e0;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:20px;color:#999;border:2px solid var(--blue-main);flex-shrink:0;">
                        <img id="sAvatarImg" style="width:100%;height:100%;object-fit:cover;display:none;">
                        <span id="sAvatarPlaceholder">👤</span>
                    </div>
                    <span id="sRemoveAvatarBtn" style="display:none;cursor:pointer;color:#dc3545;font-weight:600;font-size:13px;" onclick="removeStudentAvatar()">{{ __('🗑️ حذف') }}</span>
                </div>
            </div>
            <div class="form-group" id="editStudentParentInfo" style="display:none;padding:12px;border-radius:8px;background:#f0f7ff;margin:5px 0 15px;">
                <label style="font-weight:700;font-size:14px;color:var(--text-dark);display:block;margin-bottom:4px;">{{ __('👪 ولي الأمر') }}</label>
                <div id="editStudentParentName" style="font-size:14px;"></div>
                <div id="editStudentParentEmail" style="font-size:13px;color:#666;"></div>
                <div id="editStudentParentPhone" style="font-size:13px;color:#666;"></div>
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveStudent()">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#ddd;" onclick="closeStudentModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    <script>
        let allStudents = [];
        let allSections = [];
        let allGradeLevels = [];

        apiFetch('/admin/grade-levels').then(list => {
            allGradeLevels = list || [];
            const filterCls = document.getElementById('filterStudentClass');
            allGradeLevels.forEach(g => {
                filterCls.innerHTML += `<option value="${g.id}">${g.name}</option>`;
            });
        });

        apiFetch('/admin/sections').then(list => {
            allSections = list || [];
            const editSel = document.getElementById('editStudentSection');
            allSections.forEach(s => {
                editSel.innerHTML += `<option value="${s.id}" data-grade="${s.grade_level_id}">${s.name} ${__('شعبة')} ${sectionLabel(s.section)}</option>`;
            });
            // Populate section filter with all options initially
            populateSectionFilter('');
        });

        function populateSectionFilter(gradeLevelId) {
            const filterSec = document.getElementById('filterStudentSection');
            filterSec.innerHTML = '<option value="">' + __('📚 جميع الشُعب') + '</option>';
            const sections = gradeLevelId
                ? allSections.filter(s => s.grade_level_id == gradeLevelId)
                : allSections;
            sections.forEach(s => {
                filterSec.innerHTML += `<option value="${s.id}">${s.name} ${__('شعبة')} ${sectionLabel(s.section)}</option>`;
            });
        }

        function loadStudents() {
            apiFetch('/admin/students').then(list => {
                allStudents = list || [];
                document.getElementById('loadingStudents').style.display = 'none';
                renderStudents(allStudents);
            });
        }

        function renderStudents(list) {
            const tbody = document.getElementById('studentsBody');
            tbody.innerHTML = '';
            if (!list.length) {
                document.getElementById('studentsTable').style.display = 'none';
                const empty = document.querySelector('.card .empty') || document.createElement('div');
                empty.className = 'empty'; empty.textContent = __('لا يوجد طلاب');
                if (!document.querySelector('.card .empty')) document.querySelector('.card').appendChild(empty);
                return;
            }
            document.getElementById('studentsTable').style.display = '';
            const statusMap = { active: __('نشط'), inactive: __('غير نشط'), graduated: __('متخرج'), transferred: __('منقول') };
            list.forEach(s => {
                const statusClass = s.status === 'active' ? 'badge-success' : 'badge-danger';
                const enrolDate = s.enrollment_date ? new Date(s.enrollment_date).toLocaleDateString('ar') : '—';
                tbody.innerHTML += `<tr>
                    <td><strong>${s.user?.avatar ? `<img src="/storage/${s.user.avatar}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:6px;">` : ''}${s.user?.name || '-'}</strong><br><small style="color:#888;">${s.user?.email || ''}</small></td>
                    <td>${s.class?.name || '-'} ${s.class?.section ? __('شعبة') + ' ' + sectionLabel(s.class.section) : ''}</td>
                    <td>${s.parent?.user?.name || '-'}</td>
                    <td>${s.guardian_phone || '-'}</td>
                    <td style="font-size:13px;">${enrolDate}</td>
                    <td><span class="badge ${statusClass}">${statusMap[s.status] || s.status}</span></td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-primary btn-sm" onclick="editStudent('${s.id}')">✏️</button>
                        <button class="btn btn-sm" style="background:#dc3545;color:#fff;" onclick="deleteStudent('${s.id}', '${s.user?.name || ''}')">🗑️</button>
                    </td>
                </tr>`;
            });
        }

        function onGradeLevelChange() {
            const gradeLevelId = document.getElementById('filterStudentClass').value;
            populateSectionFilter(gradeLevelId);
            filterStudents();
        }

        let searchTimeout;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterStudents, 300);
        }

        function filterStudents() {
            const q = document.getElementById('searchStudents').value.trim().toLowerCase();
            const gradeLevelId = document.getElementById('filterStudentClass').value;
            const sectionId = document.getElementById('filterStudentSection').value;
            let filtered = allStudents;

            if (gradeLevelId) {
                const sectionIds = allSections.filter(s => s.grade_level_id == gradeLevelId).map(s => s.id);
                filtered = filtered.filter(s => sectionIds.includes(s.class_id));
            }
            if (sectionId) {
                filtered = filtered.filter(s => s.class_id == sectionId);
            }
            if (q) {
                filtered = filtered.filter(s =>
                    (s.user?.name || '').toLowerCase().includes(q) ||
                    (s.user?.email || '').toLowerCase().includes(q) ||
                    (s.parent?.user?.name || '').toLowerCase().includes(q) ||
                    (s.guardian_phone || '').includes(q)
                );
            }
            renderStudents(filtered);
        }

        function editStudent(id) {
            const s = allStudents.find(x => x.id == id);
            if (!s) return;
            document.getElementById('editStudentId').value = s.id;
            document.getElementById('editStudentName').value = s.user?.name || '';
            document.getElementById('editStudentEmail').value = s.user?.email || '';
            document.getElementById('editStudentPhone').value = s.user?.phone || '';
            document.getElementById('editStudentDob').value = s.dob ? s.dob.substring(0, 10) : '';
            document.getElementById('editStudentAddress').value = s.address || '';
            document.getElementById('editStudentPassword').value = '';

            // Filter sections to same grade level as student's current section
            const editSel = document.getElementById('editStudentSection');
            const currentSection = allSections.find(sec => sec.id == s.class_id);
            const gradeLevelId = currentSection?.grade_level_id;
            editSel.innerHTML = '<option value="">' + __('بدون شعبة') + '</option>';
            allSections.forEach(sec => {
                if (!gradeLevelId || sec.grade_level_id == gradeLevelId) {
                    editSel.innerHTML += `<option value="${sec.id}" ${sec.id == s.class_id ? 'selected' : ''}>${sec.name} ${__('شعبة')} ${sectionLabel(sec.section)}</option>`;
                }
            });

            document.getElementById('editStudentStatus').value = s.status || 'active';
            document.getElementById('editStudentGuardian').value = s.guardian_phone || '';
            document.getElementById('editStudentEnrollDate').value = s.enrollment_date ? s.enrollment_date.substring(0, 10) : '';

            // Show parent info
            const pi = document.getElementById('editStudentParentInfo');
            const p = s.parent?.user;
            if (p) {
                pi.style.display = 'block';
                document.getElementById('editStudentParentName').textContent = '👤 ' + (p.name || '—');
                document.getElementById('editStudentParentEmail').textContent = '📧 ' + (p.email || '—');
                document.getElementById('editStudentParentPhone').textContent = '📞 ' + (s.parent?.phone || s.guardian_phone || '—');
            } else {
                pi.style.display = 'none';
            }

            // Avatar
            sRemoveAvatarFlag = false;
            if (s.user?.avatar) {
                document.getElementById('sAvatarImg').src = '/storage/' + s.user.avatar;
                document.getElementById('sAvatarImg').style.display = '';
                document.getElementById('sAvatarPlaceholder').style.display = 'none';
                document.getElementById('sRemoveAvatarBtn').style.display = '';
            } else {
                document.getElementById('sAvatarImg').style.display = 'none';
                document.getElementById('sAvatarPlaceholder').style.display = '';
                document.getElementById('sRemoveAvatarBtn').style.display = 'none';
            }

            document.getElementById('studentModal').style.display = 'flex';
        }

        function closeStudentModal() { document.getElementById('studentModal').style.display = 'none'; }

        let sRemoveAvatarFlag = false;
        function removeStudentAvatar() {
            if (!confirm(__('حذف الصورة الشخصية؟'))) return;
            sRemoveAvatarFlag = true;
            document.getElementById('sAvatarImg').style.display = 'none';
            document.getElementById('sAvatarPlaceholder').style.display = '';
            document.getElementById('sRemoveAvatarBtn').style.display = 'none';
            document.getElementById('sAvatarUpload').value = '';
        }

        function deleteStudent(id, name) {
            if (!confirm(__('هل أنت متأكد من حذف الطالب') + ' "' + name + '"؟')) return;
            apiFetch('/admin/students/' + id, { method: 'DELETE' }).then(r => {
                showToast(r.message || __('✅ تم حذف الطالب'));
                setTimeout(() => location.reload(), 800);
            });
        }

        function saveStudent() {
            const id = document.getElementById('editStudentId').value;
            const data = {
                name: document.getElementById('editStudentName').value,
                email: document.getElementById('editStudentEmail').value,
                phone: document.getElementById('editStudentPhone').value,
                dob: document.getElementById('editStudentDob').value || null,
                address: document.getElementById('editStudentAddress').value,
                class_id: document.getElementById('editStudentSection').value || null,
                status: document.getElementById('editStudentStatus').value,
                guardian_phone: document.getElementById('editStudentGuardian').value,
                enrollment_date: document.getElementById('editStudentEnrollDate').value || null,
                password: document.getElementById('editStudentPassword').value || null,
            };

            if (sRemoveAvatarFlag) data.remove_avatar = true;

            apiFetch('/admin/students/' + id, {
                method: 'PUT',
                body: JSON.stringify(data),
            }).then(r => {
                showToast(__('✅ تم تعديل الطالب'));
                setTimeout(() => location.reload(), 1000);
            }).catch(e => {
                if (e.errors) Object.values(e.errors).forEach(msg => showToast(msg[0], 'error'));
                else showToast('⚠️ ' + (e.message || __('خطأ')), 'error');
            });
        }

        loadStudents();
    </script>
    <style>
        .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
    </style>
@stop
