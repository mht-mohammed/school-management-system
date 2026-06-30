@extends('layouts.dashboard')

@section('title', __('المواد'))
@section('page-title', __('إدارة المواد الدراسية'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects" class="active">📚 <span>{{ __('المواد') }}</span></a>
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
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h3 style="margin:0;">{{ __('📚 المواد حسب الصف') }}</h3>
                <p style="color:#888;font-size:14px;margin-top:4px;">{{ __('اختر صف لعرض المواد — يمكنك إضافة وتعديل وحذف المواد') }}</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <select id="classFilter" onchange="filterByClass()" style="padding:10px 16px;border:1px solid #ddd;border-radius:10px;min-width:220px;font-size:14px;">
                    <option value="">{{ __('🏫 اختر الصف') }}</option>
                </select>
            </div>
        </div>
        <div class="loading" id="loadingSubjects">{{ __('جاري التحميل...') }}</div>
        <div id="subjectsContent" style="display:none;">
            <div style="overflow-x:auto;">
                <table id="subjectsTable">
                    <thead><tr><th style="width:50px;">#</th><th>{{ __('المادة') }}</th><th>{{ __('حصص/أسبوع') }}</th><th>{{ __('المعلمون') }}</th><th>{{ __('إجراءات') }}</th></tr></thead>
                    <tbody id="subjectsBody"></tbody>
                </table>
            </div>
        </div>
        <div id="noSelection" style="text-align:center;padding:60px 20px;color:#aaa;display:block;">
            <div style="font-size:48px;margin-bottom:10px;">📚</div>
            {{ __('اختر الصف من القائمة أعلاه لعرض المواد') }}
        </div>
    </div>

    <div id="subjectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:var(--radius-md);width:90%;max-width:400px;">
            <h3 id="subjectModalTitle" style="margin-bottom:15px;">{{ __('➕ إضافة مادة') }}</h3>
            <input id="editSubjectId" type="hidden">
            <div class="form-group"><label>{{ __('اسم المادة') }}</label><input id="editSubjectName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveSubject()">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#ddd;" onclick="closeSubjectModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    <script>
        let allSubjects = [];
        let allGradeLevels = [];
        let currentGradeLevelId = '';

        apiFetch('/admin/grade-levels').then(list => {
            allGradeLevels = list || [];
            const sel = document.getElementById('classFilter');
            allGradeLevels.forEach(g => { sel.innerHTML += `<option value="${g.id}">${g.name}</option>`; });
        });

        function filterByClass() {
            currentGradeLevelId = document.getElementById('classFilter').value;
            document.getElementById('noSelection').style.display = currentGradeLevelId ? 'none' : 'block';
            document.getElementById('subjectsContent').style.display = currentGradeLevelId ? 'block' : 'none';
            if (!currentGradeLevelId) return;
            document.getElementById('loadingSubjects').style.display = 'block';
            apiFetch('/admin/subjects?grade_level_id=' + currentGradeLevelId).then(list => {
                allSubjects = list || [];
                document.getElementById('loadingSubjects').style.display = 'none';
                renderSubjects();
            });
        }

        function renderSubjects() {
            const tbody = document.getElementById('subjectsBody');
            tbody.innerHTML = '';
            if (!allSubjects.length) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px;">' + __('لا توجد مواد لهذا الصف — أضف مادة جديدة') + '</td></tr>';
                return;
            }
            allSubjects.forEach((s, i) => {
                const teacherHtml = (s.teachers || []).length
                    ? s.teachers.map(t => `<span style="display:inline-block;padding:2px 12px;border-radius:20px;background:#e8f0fe;color:var(--blue-main);font-weight:600;font-size:12px;margin:2px;">${t.name}</span>`).join('')
                    : '<span style="color:#b8232e;">' + __('غير معيّن') + '</span>';
                tbody.innerHTML += `<tr>
                    <td style="text-align:center;font-weight:700;color:#888;">${i + 1}</td>
                    <td><strong>${subjectName(s.name)}</strong></td>
                    <td style="text-align:center;"><span class="badge badge-primary">${s.periods_per_week || 5}</span></td>
                    <td>${teacherHtml}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-sm btn-primary" onclick="editSubject('${s.id}')">✏️</button>
                    </td>
                </tr>`;
            });
        }

        function editSubject(id) {
            const s = allSubjects.find(x => x.id == id);
            if (!s) return;
            document.getElementById('subjectModalTitle').textContent = __('✏️ تعديل مادة');
            document.getElementById('editSubjectId').value = s.id;
            document.getElementById('editSubjectName').value = s.name;
            document.getElementById('subjectModal').style.display = 'flex';
        }

        function closeSubjectModal() { document.getElementById('subjectModal').style.display = 'none'; }

        function saveSubject() {
            const id = document.getElementById('editSubjectId').value;
            const name = document.getElementById('editSubjectName').value.trim();
            if (!name) { showToast(__('⚠️ اسم المادة مطلوب')); return; }

            apiFetch('/admin/subjects/' + id, {
                method: 'PUT',
                body: JSON.stringify({ name }),
            }).then(r => {
                showToast(__('✅ تم تعديل المادة'));
                closeSubjectModal();
                filterByClass();
            }).catch(e => { if (e.errors) Object.values(e.errors).forEach(msg => showToast(msg[0])); });
        }

    </script>
<style>.badge-secondary { background:#e0e0e0;color:#555;padding:2px 8px;border-radius:10px;font-size:12px; }</style>
@stop