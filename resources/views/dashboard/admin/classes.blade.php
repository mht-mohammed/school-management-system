@extends('layouts.dashboard')

@section('title', __('الصفوف'))
@section('page-title', __('إدارة الصفوف والشعب'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes" class="active">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
@stop

@section('content')
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">{{ __('المراحل الدراسية') }}</h3>
        </div>
        <div class="loading" id="loadingGradeLevels">{{ __('جاري التحميل...') }}</div>
        <div id="gradeLevelsContainer" style="display:none;"></div>
    </div>


    <!-- Section Edit Modal -->
    <div id="sectionModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:14px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;">
            <h3 id="sectionModalTitle" style="margin-bottom:15px;">{{ __('تعديل شعبة') }}</h3>
            <input id="sectionId" type="hidden">
            <div class="form-group"><label>{{ __('المرحلة') }}</label>
                <select id="sectionGradeLevel" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"><option value="">{{ __('اختر المرحلة') }}</option></select>
            </div>
            <div class="form-group"><label>{{ __('اسم الشعبة') }}</label>
                <select id="sectionName" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;">
                    <option value="">{{ __('اختر الشعبة') }}</option>
                    <option value="أ">{{ __('أ') }}</option>
                    <option value="ب">{{ __('ب') }}</option>
                    <option value="ج">{{ __('ج') }}</option>
                    <option value="د">{{ __('د') }}</option>
                </select>
            </div>
            <div class="form-group"><label>{{ __('مربي الصف (معلم)') }}</label>
                <select id="sectionTeacher" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"><option value="">{{ __('بدون') }}</option></select>
            </div>
            <div class="form-group"><label>{{ __('العام الدراسي') }}</label><input id="sectionYear" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveSection()">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#ddd;" onclick="closeSectionModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    <div id="studentsInSectionModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:14px;width:90%;max-width:600px;max-height:85vh;overflow-y:auto;">
            <h3 id="sisModalTitle" style="margin-bottom:15px;"></h3>
            <div id="sisContent"></div>
            <div style="margin-top:15px;">
                <button class="btn" style="background:#ddd;" onclick="closeSisModal()">{{ __('إغلاق') }}</button>
            </div>
        </div>
    </div>



    <script>
        let allSections = [];
        let allGradeLevels = [];
        let teachersList = [];
        let allStudents = [];

        apiFetch('/admin/teachers-list').then(list => {
            teachersList = list || [];
        });

        apiFetch('/admin/students').then(list => {
            allStudents = list || [];
        });

        function loadData() {
            apiFetch('/admin/sections').then(list => {
                allSections = list || [];
                apiFetch('/admin/grade-levels').then(gl => {
                    allGradeLevels = gl || [];
                    document.getElementById('loadingGradeLevels').style.display = 'none';
                    renderGradeLevels();
                });
            });
        }

        function getStudentsCount(sectionId) {
            return allStudents.filter(s => s.class_id == sectionId).length;
        }

        function renderGradeLevels() {
            const container = document.getElementById('gradeLevelsContainer');
            container.style.display = 'block';
            container.innerHTML = '';
            if (!allGradeLevels.length) { container.innerHTML = '<div class="empty">' + __('لا توجد مراحل — أضف مرحلة أولاً') + '</div>'; return; }

            // count unassigned students
            const unassigned = allStudents.filter(s => !s.class_id).length;

            allGradeLevels.forEach(gl => {
                const sections = allSections.filter(s => s.grade_level_id == gl.id);
                const totalInGrade = sections.reduce((sum, s) => sum + getStudentsCount(s.id), 0);
                const sectionHtml = sections.map(s => {
                    const cnt = getStudentsCount(s.id);
                    return `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:#f9f9fb;margin:6px 0;">
                        <div>
                            <strong>${__('شعبة')} ${sectionLabel(s.section)}</strong>
                            <span style="color:#666;font-size:13px;margin-right:12px;">👨‍🏫 ${s.teacher?.avatar ? `<img src="/storage/${s.teacher.avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:4px;">` : ''}${s.teacher?.name || __('بدون مربي')}</span>
                            <span style="color:#666;font-size:13px;margin-right:10px;">👥 ${toArabicNum(cnt)} ${__('طالب')}</span>
                        </div>
                        <div style="display:flex;gap:6px;">
                            ${cnt > 0 ? `<button class="btn btn-sm" style="background:#17a2b8;color:#fff" onclick="viewStudentsInSection('${s.id}')">👥</button>` : ''}
                            <button class="btn btn-sm btn-primary" onclick="editSection('${s.id}')">✏️</button>
                        </div>
                    </div>`;
                }).join('') || '<div style="color:#aaa;padding:8px;font-size:13px;">' + __('لا توجد شعب بعد') + '</div>';

                container.innerHTML += `
                    <div style="border:1px solid #e0e0e0;border-radius:14px;padding:16px;margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <h4 style="margin:0;">📚 ${gl.name} <span style="font-size:13px;color:#666;font-weight:400;">(${gl.stage || '—'})</span> <span style="font-size:13px;color:#17a2b8;">— ${toArabicNum(totalInGrade)} ${__('طالب')}</span></h4>
                            <div style="display:flex;gap:6px;">
                                <button class="btn btn-sm" style="background:#17a2b8;color:#fff" onclick="distributeStudents(${gl.id}, '${gl.name}')">${__('🎲 توزيع طلاب')}</button>
                                <button class="btn btn-sm" style="background:#ffc107;" onclick="editGradeLevel('${gl.id}')">✏️</button>
                            </div>
                        </div>
                        <div style="margin-right:10px;">${sectionHtml}</div>
                    </div>`;
            });

            if (unassigned > 0) {
                container.innerHTML += `
                    <div style="border:1px solid #ffc107;border-radius:14px;padding:16px;margin-bottom:14px;background:#fffbe6;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h4 style="margin:0;">${__('⚠️ طلاب بدون شعبة')} <span style="color:#dc3545;font-weight:700;">${unassigned}</span></h4>
                            <button class="btn btn-sm" style="background:#17a2b8;color:#fff" onclick="viewUnassignedStudents()">${__('👥 عرض الطلاب')}</button>
                        </div>
                    </div>`;
            }
        }

        function populateTeacherDropdown(gradeLevelId, selectedTeacherId) {
            const sel = document.getElementById('sectionTeacher');
            sel.innerHTML = '<option value="">' + __('— بدون مربي —') + '</option>';
            const filtered = gradeLevelId
                ? teachersList.filter(t => t.grade_level_ids && t.grade_level_ids.includes(parseInt(gradeLevelId)))
                : teachersList;
            filtered.forEach(t => {
                sel.innerHTML += `<option value="${t.id}" ${t.id == selectedTeacherId ? 'selected' : ''}>${t.name}</option>`;
            });
            if (!filtered.length) {
                sel.innerHTML += '<option value="" disabled>' + __('لا يوجد معلمون لهذه المرحلة') + '</option>';
            }
        }

        function closeSectionModal() { document.getElementById('sectionModal').style.display = 'none'; }

        function editSection(id) {
            const s = allSections.find(c => c.id == id);
            if (!s) return;
            document.getElementById('sectionModal').style.display = 'flex';
            document.getElementById('sectionModalTitle').textContent = __('تعديل شعبة');
            document.getElementById('sectionId').value = s.id;
            const sel = document.getElementById('sectionGradeLevel');
            sel.innerHTML = '<option value="">' + __('اختر المرحلة') + '</option>';
            allGradeLevels.forEach(g => { sel.innerHTML += `<option value="${g.id}" ${g.id == s.grade_level_id ? 'selected' : ''}>${g.name}</option>`; });
            document.getElementById('sectionName').value = s.section || 'أ';
            document.getElementById('sectionYear').value = s.academic_year || '';
            populateTeacherDropdown(s.grade_level_id, s.teacher_id || '');
        }

        function saveSection() {
            const id = document.getElementById('sectionId').value;
            const gradeLevelId = document.getElementById('sectionGradeLevel').value;
            const sectionName = document.getElementById('sectionName').value;
            if (!gradeLevelId || !sectionName) { showToast(__('اختر المرحلة واسم الشعبة'), 'error'); return; }
            const data = {
                grade_level_id: gradeLevelId,
                section: sectionName,
                teacher_id: document.getElementById('sectionTeacher').value || null,
                academic_year: document.getElementById('sectionYear').value || new Date().getFullYear() + '-' + (new Date().getFullYear() + 1),
            };
            const url = '/admin/sections/' + id;
            apiFetch(url, { method: 'PUT', body: JSON.stringify(data) }).then(r => {
                if (r.message && r.message.includes('مربي')) {
                    showToast(r.message, 'error');
                } else if (r.error) {
                    showToast(r.error, 'error');
                } else {
                    location.reload();
                }
            }).catch(e => showToast(__('حدث خطأ: ') + e.message, 'error'));
        }

        // --- Grade Level ---
        function editGradeLevel(id) {
            const gl = allGradeLevels.find(g => g.id == id);
            if (!gl) return;
            const newName = prompt(__('تعديل اسم المرحلة:'), gl.name);
            if (!newName || newName === gl.name) return;
            apiFetch('/admin/grade-levels/' + id, { method: 'PUT', body: JSON.stringify({ name: newName, stage: gl.stage, academic_year: gl.academic_year }) }).then(() => location.reload());
        }

        function distributeStudents(glId, glName) {
            if (!confirm(__('🎲 توزيع طلاب') + ' "' + glName + '" ' + __('بشكل عشوائي على شعبها؟'))) return;
            apiFetch('/admin/grade-levels/' + glId + '/distribute-students', { method: 'POST' }).then(r => {
                if (r.errors) { showToast((__('⚠️')) + ' ' + Object.values(r.errors).flat().join(' | '), 'error'); return; }
                showToast(r.message || __('✅ تم التوزيع'));
                setTimeout(() => location.reload(), 800);
            });
        }

        // --- Students in Section Modal ---
        function viewStudentsInSection(sectionId) {
            const section = allSections.find(s => s.id == sectionId);
            if (!section) return;
            const students = allStudents.filter(s => s.class_id == sectionId);
            document.getElementById('sisModalTitle').textContent = `👥 ${__('طلاب شعبة')} ${sectionLabel(section.section)} - ${section.name || ''}`;

            // Only sections in the same grade level
            const siblingSections = allSections.filter(s => s.grade_level_id == section.grade_level_id);
            const allSectionsOpts = siblingSections.map(s =>
                `<option value="${s.id}" ${s.id == sectionId ? 'selected' : ''}>${__('شعبة')} ${sectionLabel(s.section)}</option>`
            ).join('');

            let html = '';
            if (!students.length) {
                html = '<div class="empty">' + __('لا يوجد طلاب في هذه الشعبة') + '</div>';
            } else {
                html = `<table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="border-bottom:2px solid #eee;">
                        <th style="padding:8px;text-align:right;">#</th>
                        <th style="padding:8px;text-align:right;">${__('الاسم')}</th>
                        <th style="padding:8px;text-align:right;">${__('البريد')}</th>
                        <th style="padding:8px;text-align:right;">${__('نقل إلى')}</th>
                        <th style="padding:8px;text-align:right;"></th>
                    </tr></thead><tbody>`;
                students.forEach((s, i) => {
                    html += `<tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:8px;">${i + 1}</td>
                        <td style="padding:8px;">${s.user?.avatar ? `<img src="/storage/${s.user.avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:4px;">` : ''}${s.user?.name || '-'}</td>
                        <td style="padding:8px;color:#666;">${s.user?.email || ''}</td>
                        <td style="padding:8px;">
                            <select id="moveSection_${s.id}" style="padding:5px;border:1px solid #ddd;border-radius:6px;font-size:12px;">
                                <option value="">${__('اختر شعبة')}</option>
                                ${allSectionsOpts}
                            </select>
                        </td>
                        <td style="padding:8px;">
                            <button class="btn btn-sm btn-primary" onclick="moveStudent('${s.id}')">${__('نقل')}</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }

            document.getElementById('sisContent').innerHTML = html;
            document.getElementById('studentsInSectionModal').style.display = 'flex';
        }

        function viewUnassignedStudents() {
            const students = allStudents.filter(s => !s.class_id);
            document.getElementById('sisModalTitle').textContent = __('⚠️ طلاب بدون شعبة') + ` (${students.length})`;

            // Group sections by grade level
            const glOpts = allGradeLevels.map(gl => {
                const secs = allSections.filter(s => s.grade_level_id == gl.id);
                if (!secs.length) return '';
                const secOpts = secs.map(s => `<option value="${s.id}">${__('شعبة')} ${sectionLabel(s.section)}</option>`).join('');
                return `<optgroup label="${gl.name}">${secOpts}</optgroup>`;
            }).join('');

            let html = '';
            if (!students.length) {
                html = '<div class="empty">' + __('جميع الطلاب لديهم شعب') + '</div>';
            } else {
                html = `<table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="border-bottom:2px solid #eee;">
                        <th style="padding:8px;text-align:right;">#</th>
                        <th style="padding:8px;text-align:right;">${__('الاسم')}</th>
                        <th style="padding:8px;text-align:right;">${__('البريد')}</th>
                        <th style="padding:8px;text-align:right;">${__('نقل إلى')}</th>
                        <th style="padding:8px;text-align:right;"></th>
                    </tr></thead><tbody>`;
                students.forEach((s, i) => {
                    html += `<tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:8px;">${i + 1}</td>
                        <td style="padding:8px;">${s.user?.avatar ? `<img src="/storage/${s.user.avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-left:4px;">` : ''}${s.user?.name || '-'}</td>
                        <td style="padding:8px;color:#666;">${s.user?.email || ''}</td>
                        <td style="padding:8px;">
                            <select id="moveSection_${s.id}" style="padding:5px;border:1px solid #ddd;border-radius:6px;font-size:12px;">
                                <option value="">${__('اختر شعبة')}</option>
                                ${glOpts}
                            </select>
                        </td>
                        <td style="padding:8px;">
                            <button class="btn btn-sm btn-primary" onclick="moveStudent('${s.id}')">${__('نقل')}</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }

            document.getElementById('sisContent').innerHTML = html;
            document.getElementById('studentsInSectionModal').style.display = 'flex';
        }

        function moveStudent(studentId) {
            const sectionId = document.getElementById('moveSection_' + studentId).value;
            if (!sectionId) { showToast(__('⚠️ اختر شعبة أولاً')); return; }
            const s = allStudents.find(x => x.id == studentId);
            if (s && s.class_id == sectionId) {
                showToast(__('⚠️ الطالب موجود في هذه الشعبة بالفعل'));
                return;
            }
            apiFetch('/admin/students/' + studentId, {
                method: 'PUT',
                body: JSON.stringify({ class_id: sectionId }),
            }).then(r => {
                showToast(__('✅ تم نقل الطالب'));
                // Update local data
                if (s) s.class_id = parseInt(sectionId);
                // Re-render
                renderGradeLevels();
                viewStudentsInSection(sectionId); // refresh modal
            }).catch(e => showToast((__('⚠️ ')) + (e.message || __('خطأ')), 'error'));
        }

        function closeSisModal() { document.getElementById('studentsInSectionModal').style.display = 'none'; }

        // Filter teachers when grade level changes
        document.getElementById('sectionGradeLevel')?.addEventListener('change', function() {
            populateTeacherDropdown(this.value, '');
        });

        loadData();

        document.querySelectorAll('input[id$="Year"]').forEach(el => { if (!el.value) el.value = new Date().getFullYear() + '-' + (new Date().getFullYear() + 1); });
    </script>
    <style>
        .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); }
        .btn-sm { padding: 5px 12px; font-size: 13px; }
    </style>
@stop
