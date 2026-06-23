@extends('layouts.dashboard')

@section('title', __('الجداول'))
@section('page-title', __('إدارة الجداول الدراسية'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules" class="active">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
@stop

@section('content')
    <div class="card">
        <div id="phpDebug" style="display:none;" data-gl="{{ count($gradeLevels ?? []) }}" data-sec="{{ count($sections ?? []) }}"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h3 style="margin:0;">{{ __('📅 جدول الحصص الأسبوعي') }}</h3>
                <p style="color:#888;font-size:14px;margin-top:4px;">{{ __('اختر الشعبة لعرض الجدول — 5 حصص يومياً (الأحد → الخميس)') }}</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <select id="sectionSelect" onchange="loadGrid()" style="padding:10px 16px;border:1px solid #ddd;border-radius:10px;min-width:250px;font-size:14px;">
                    <option value="">{{ __('🏫 اختر الشعبة') }}</option>
                    @foreach ($gradeLevels ?? [] as $gl)
                        @foreach ($sections->where('grade_level_id', $gl->id) ?? [] as $sec)
                            <option value="{{ $sec->id }}">{{ $gl->name }} - {{ __('شعبة') }} {{ $sec->section ?? '—' }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Buttons always visible --}}
        <div style="margin-bottom:15px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <button class="btn" style="background:#6f42c1;color:#fff;" onclick="distributeSlots()">{{ __('📅 توزيع الكل') }}</button>
            <button class="btn" style="background:#dc3545;color:#fff;" onclick="clearAllSchedules()">{{ __('🗑️ إفراغ الجداول') }}</button>
            <span style="color:#888;font-size:13px;" id="gridStats"></span>
        </div>

        <div class="loading" id="loadingGrid">{{ __('اختر الشعبة لعرض الجدول') }}</div>

        <div id="scheduleContainer" style="display:none;">
            <div id="subjectSummary" style="margin-bottom:15px;padding:12px 16px;background:#f8f9fa;border-radius:10px;font-size:14px;"></div>
            <div style="overflow-x:auto;">
                <table id="scheduleGrid" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#4a90d9;color:#fff;">
                            <th style="padding:10px;text-align:center;min-width:80px;">{{ __('اليوم / الحصة') }}</th>
                            <th style="padding:10px;text-align:center;min-width:120px;">{{ __('الحصة 1') }}<br><small>08:00-08:45</small></th>
                            <th style="padding:10px;text-align:center;min-width:120px;">{{ __('الحصة 2') }}<br><small>08:50-09:35</small></th>
                            <th style="padding:10px;text-align:center;min-width:120px;">{{ __('الحصة 3') }}<br><small>09:40-10:25</small></th>
                            <th style="padding:10px;text-align:center;min-width:120px;">{{ __('الحصة 4') }}<br><small>10:55-11:40</small></th>
                            <th style="padding:10px;text-align:center;min-width:120px;">{{ __('الحصة 5') }}<br><small>11:45-12:30</small></th>
                        </tr>
                    </thead>
                    <tbody id="gridBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cell Edit Modal -->
    <div id="cellModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:25px;border-radius:14px;width:90%;max-width:420px;">
            <h3 id="cellModalTitle" style="margin-bottom:10px;">{{ __('تعديل الحصة') }}</h3>
            <p id="cellModalInfo" style="color:#888;font-size:14px;margin-bottom:15px;"></p>
            <input id="cellScheduleId" type="hidden">
            <input id="cellDay" type="hidden">
            <input id="cellPeriod" type="hidden">
            <div class="form-group"><label>{{ __('المادة') }}</label>
                <select id="cellSubject" onchange="onCellSubjectChange()" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 12px;">
                    <option value="">{{ __('اختر المادة') }}</option>
                </select>
            </div>
            <div class="form-group"><label>{{ __('المعلم') }}</label>
                <select id="cellTeacher" onchange="onCellTeacherChange()" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 12px;">
                    <option value="">{{ __('اختر المعلم') }}</option>
                </select>
            </div>
            <div id="cellTeacherWarning" style="display:none;padding:10px 14px;background:#f8d7da;border:1px solid #dc3545;border-radius:8px;color:#721c24;font-size:13px;font-weight:600;margin-bottom:12px;"></div>
            <div class="form-group"><label>{{ __('القاعة') }}</label>
                <input id="cellRoom" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;">
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" id="cellSaveBtn" onclick="saveCell()">{{ __('💾 حفظ') }}</button>
                <button class="btn" style="background:#dc3545;color:#fff;" id="deleteCellBtn" onclick="deleteCell()">{{ __('🗑️ حذف') }}</button>
                <button class="btn" style="background:#ddd;" onclick="closeCellModal()">{{ __('إلغاء') }}</button>
            </div>
        </div>
    </div>

    <script>
        let allSections = @json($sections ?? []);
        let allGradeLevels = @json($gradeLevels ?? []);
        let allSubjects = @json($subjects ?? []);
        let allTeachersFlat = [];
        let currentGrid = [];
        let currentSectionId = '';
        const days = ['sunday','monday','tuesday','wednesday','thursday'];
        const dayNames = {sunday:__('الأحد'),monday:__('الإثنين'),tuesday:__('الثلاثاء'),wednesday:__('الأربعاء'),thursday:__('الخميس')};
        const periodTimes = {1:'08:00-08:45',2:'08:50-09:35',3:'09:40-10:25',4:'10:55-11:40',5:'11:45-12:30'};

        function norm(s) { return s.replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/\s+/g,'').replace(/ال/g,''); }
        function shuffle(a) { for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; }

        function showToast(message, type = 'success') {
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML = message;
            container.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = '0.3s'; setTimeout(() => toast.remove(), 300); }, 12000);
        }

        // Data loaded server-side — populate immediately
        (function() {
            try {
                populateSectionSelect();
            } catch(e) {
                console.error('INIT error:', e);
            }
        })();

        function populateSectionSelect() {
            // Options are already in HTML from server — this is a backup for dynamic reloads
            const sel = document.getElementById('sectionSelect');
            if (!sel || sel.options.length > 1) return;
            sel.innerHTML = '<option value="">' + __('🏫 اختر الشعبة') + '</option>';
            allGradeLevels.forEach(gl => {
                allSections.filter(s => s.grade_level_id == gl.id).forEach(s => {
                    sel.innerHTML += `<option value="${s.id}">${gl.name} - ${__('شعبة')} ${sectionLabel(s.section)}</option>`;
                });
            });
        }


        function loadGrid() {
            currentSectionId = document.getElementById('sectionSelect').value;
            const container = document.getElementById('scheduleContainer');
            const loading = document.getElementById('loadingGrid');
            if (!currentSectionId) {
                container.style.display = 'none';
                loading.textContent = __('اختر الشعبة لعرض الجدول');
                return;
            }
            loading.textContent = __('⏳ جاري التحميل...');
            container.style.display = 'none';

            // Load teachers in background
            if (!allTeachersFlat.length) {
                apiFetch('/admin/teachers').then(t => {
                    allTeachersFlat = t || [];
                }).catch(() => {});
            }

            apiFetch('/admin/schedules-grid?section_id=' + currentSectionId).then(data => {
                if (data && data.grid) {
                    currentGrid = data.grid;
                    renderGrid();
                    renderSubjectSummary();
                    loading.style.display = 'none';
                    container.style.display = 'block';
                } else {
                    loading.textContent = __('❌ استجابة فارغة');
                }
            }).catch(() => {
                loading.textContent = __('❌ فشل تحميل الجدول');
            });
        }

        function renderGrid() {
            const tbody = document.getElementById('gridBody');
            tbody.innerHTML = '';
            let filled = 0;
            let total = 0;

            currentGrid.forEach(row => {
                const tr = document.createElement('tr');
                const dayHeader = document.createElement('td');
                dayHeader.style.cssText = 'padding:10px;font-weight:700;background:#f0f4ff;border:1px solid #e0e0e0;text-align:center;';
                dayHeader.textContent = dayNames[row.day];
                tr.appendChild(dayHeader);

                for (let p = 1; p <= 5; p++) {
                    const cell = document.createElement('td');
                    const entry = row.periods[p];
                    cell.style.cssText = 'padding:8px;border:1px solid #e0e0e0;text-align:center;vertical-align:middle;cursor:pointer;min-height:60px;transition:all 0.2s;';
                    cell.onmouseenter = function() { this.style.background = '#e8f0fe'; };
                    cell.onmouseleave = function() { this.style.background = entry ? '#f0fdf4' : '#fff'; };
                    total++;
                    if (entry) {
                        filled++;
                        cell.style.background = '#f0fdf4';
                        cell.innerHTML = `<div style="font-weight:600;font-size:14px;">${subjectName(entry.subject_name)}</div>
                                          <div style="font-size:12px;color:#555;">${entry.teacher_name}</div>
                                          ${entry.room ? `<div style="font-size:11px;color:#888;">🏠 ${entry.room}</div>` : ''}`;
                        cell.onclick = () => openEditCell(row.day, p, entry);
                    } else {
                        cell.innerHTML = `<div style="color:#ccc;font-size:12px;">🕐</div>`;
                        cell.onclick = () => openAddCell(row.day, p);
                    }
                    tr.appendChild(cell);
                }
                tbody.appendChild(tr);
            });

            document.getElementById('gridStats').textContent = __('✅ ') + toArabicNum(filled) + '/' + toArabicNum(total) + __(' حصة مملوءة');
        }

        function renderSubjectSummary() {
            const summary = {};
            currentGrid.forEach(row => {
                for (let p = 1; p <= 5; p++) {
                    const entry = row.periods[p];
                    if (entry && entry.subject_name) {
                        summary[entry.subject_name] = (summary[entry.subject_name] || 0) + 1;
                    }
                }
            });
            const div = document.getElementById('subjectSummary');
            const summaryOrder = ['الرياضيات','اللغة الإنجليزية','العلوم الحياتية','اللغة العربية','التربية الإسلامية','التكنولوجيا والحاسوب','التربية الرياضية','الدراسات الاجتماعية'];
            const entries = Object.entries(summary).sort((a, b) => summaryOrder.indexOf(a[0]) - summaryOrder.indexOf(b[0]));
            if (!entries.length) {
                div.innerHTML = '<span style="color:#888;">' + __('لم يتم توزيع أي مادة بعد') + '</span>';
                return;
            }
            div.innerHTML = '<strong style="margin-left:8px;">' + __('توزيع المواد:') + '</strong> ' +
                entries.map(([name, count]) =>
                    `<span style="display:inline-block;padding:3px 10px;border-radius:20px;background:#e8f0fe;margin:2px 4px;font-size:13px;">${subjectName(name)} <strong style="color:#4a90d9;">×${count}</strong></span>`
                ).join('');
        }

        // ---- Cell Modal ----
        function openAddCell(day, period) {
            document.getElementById('cellScheduleId').value = '';
            document.getElementById('cellModalTitle').textContent = __('➕ إضافة حصة');
            document.getElementById('deleteCellBtn').style.display = 'none';
            document.getElementById('cellModalInfo').textContent = `${dayNames[day]} - ${__('الحصة')} ${period} (${periodTimes[period]})`;
            document.getElementById('cellDay').value = day;
            document.getElementById('cellPeriod').value = period;
            document.getElementById('cellRoom').value = '';
            document.getElementById('cellTeacherWarning').style.display = 'none';
            const saveBtn = document.getElementById('cellSaveBtn');
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            populateCellSubjects();
            document.getElementById('cellTeacher').innerHTML = '<option value="">' + __('اختر المعلم') + '</option>';
            document.getElementById('cellModal').style.display = 'flex';
        }

        function openEditCell(day, period, entry) {
            document.getElementById('cellScheduleId').value = entry.id;
            document.getElementById('cellModalTitle').textContent = __('✏️ تعديل الحصة');
            document.getElementById('deleteCellBtn').style.display = 'inline-block';
            document.getElementById('cellModalInfo').textContent = `${dayNames[day]} - ${__('الحصة')} ${period} (${periodTimes[period]})`;
            document.getElementById('cellDay').value = day;
            document.getElementById('cellPeriod').value = period;
            document.getElementById('cellTeacherWarning').style.display = 'none';
            const saveBtn = document.getElementById('cellSaveBtn');
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            populateCellSubjects(entry.subject_id);
            document.getElementById('cellRoom').value = entry.room || '';
            populateCellTeachers(entry.subject_id, entry.teacher_id);
            document.getElementById('cellModal').style.display = 'flex';
        }

        function populateCellSubjects(selectedId) {
            const section = allSections.find(s => s.id == currentSectionId);
            if (!section) return;
            const sel = document.getElementById('cellSubject');
            sel.innerHTML = '<option value="">' + __('اختر المادة') + '</option>';
            allSubjects.filter(sub => sub.grade_level_id == section.grade_level_id).forEach(s => {
                sel.innerHTML += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`;
            });
        }

        function onCellSubjectChange() {
            populateCellTeachers(document.getElementById('cellSubject').value);
        }

        function onCellTeacherChange() {
            const teacherUserId = document.getElementById('cellTeacher').value;
            const warnDiv = document.getElementById('cellTeacherWarning');
            const saveBtn = document.getElementById('cellSaveBtn');
            if (!teacherUserId) { warnDiv.style.display = 'none'; saveBtn.disabled = false; saveBtn.style.opacity = '1'; return; }

            const day = document.getElementById('cellDay').value;
            const period = parseInt(document.getElementById('cellPeriod').value);
            const scheduleId = document.getElementById('cellScheduleId').value;

            apiFetch('/admin/schedules').then(allSchedules => {
                for (const s of (allSchedules || [])) {
                    if (s.teacher_id == teacherUserId && s.day_of_week === day && s.period_number == period && s.id != scheduleId) {
                        warnDiv.textContent = __('❌ "') + (s.teacher?.name || __('معلم')) + __('" مشغول مع "') + (s.section?.name || s.section_id) + '"';
                        warnDiv.style.display = 'block';
                        saveBtn.disabled = true;
                        saveBtn.style.opacity = '0.5';
                        return;
                    }
                }
                warnDiv.style.display = 'none';
                saveBtn.disabled = false;
                saveBtn.style.opacity = '1';
            }).catch(() => { warnDiv.style.display = 'none'; saveBtn.disabled = false; saveBtn.style.opacity = '1'; });
        }

        function populateCellTeachers(subjectId, selectedTeacherId) {
            const teachers = allTeachersFlat;
            if (!teachers.length) {
                apiFetch('/admin/teachers').then(t => {
                    allTeachersFlat = t || [];
                    populateCellTeachers(subjectId, selectedTeacherId);
                });
                return;
            }
            const sel = document.getElementById('cellTeacher');
            sel.innerHTML = '<option value="">' + __('اختر المعلم') + '</option>';
            if (!subjectId) return;

            const subject = allSubjects.find(s => s.id == subjectId);
            const subjNorm = subject ? norm(subject.name) : '';

            teachers.forEach(t => {
                const specNorm = norm(t.teacher?.specialization || '');
                const isMatch = specNorm && subjNorm && (subjNorm.includes(specNorm) || specNorm.includes(subjNorm));
                if (isMatch) {
                    sel.innerHTML += `<option value="${t.id}" ${t.id == selectedTeacherId ? 'selected' : ''}>${t.name || __('معلم')} (${t.teacher?.specialization || ''})</option>`;
                }
            });
        }

        function closeCellModal() { document.getElementById('cellModal').style.display = 'none'; }

        function saveCell() {
            const id = document.getElementById('cellScheduleId').value;
            if (document.getElementById('cellSaveBtn').disabled) { showToast(__('⚠️ المعلم مشغول في هذه الفترة'), 'error'); return; }
            const data = {
                section_id: currentSectionId,
                day_of_week: document.getElementById('cellDay').value,
                period_number: parseInt(document.getElementById('cellPeriod').value),
                subject_id: document.getElementById('cellSubject').value,
                teacher_id: document.getElementById('cellTeacher').value || null,
                room: document.getElementById('cellRoom').value,
            };
            if (!data.subject_id) { showToast(__('⚠️ اختر المادة')); return; }

            const url = id ? '/admin/schedules/' + id : '/admin/schedules';
            const method = id ? 'PUT' : 'POST';

            apiFetch(url, { method, body: JSON.stringify(data) }).then(() => {
                showToast(id ? __('✅ تم تعديل الحصة') : __('✅ تم إضافة الحصة'));
                closeCellModal();
                loadGrid();
            }).catch(e => showToast(__('⚠️ ') + (e.message || __('حدث خطأ')), 'error'));
        }

        function deleteCell() {
            const id = document.getElementById('cellScheduleId').value;
            if (!id || !confirm(__('تأكيد حذف هذه الحصة؟'))) return;
            apiFetch('/admin/schedules/' + id, { method: 'DELETE' }).then(() => {
                showToast(__('✅ تم حذف الحصة'));
                closeCellModal();
                loadGrid();
            });
        }

        // ---- Distribute Slots ----
        // Includes teacher assignment to avoid unfillable slots
        // gradeLevelId: optional — if provided, processes only that grade level
        function distributeSlots(gradeLevelId) {
            // Determine target grade levels and sections
            let targetGLs = [];
            let targetSections = [];
            let msgPrefix = '';

            if (gradeLevelId) {
                const gl = allGradeLevels.find(g => g.id === gradeLevelId);
                if (!gl) { showToast(__('⚠️ الصف غير موجود'), 'error'); return; }
                targetGLs = [gl];
                targetSections = allSections.filter(s => s.grade_level_id == gradeLevelId);
                msgPrefix = gl.name + ' - ';
            } else {
                if (!allGradeLevels.length) { showToast(__('⚠️ لا توجد صفوف دراسية'), 'error'); return; }
                targetGLs = allGradeLevels;
                targetSections = allSections;
            }

            if (!targetSections.length) { showToast(__('⚠️ لا توجد شعب'), 'error'); return; }
            if (!confirm(msgPrefix + __('📅 توزيع الحصص والمعلمين على ') + targetSections.length + __(' شعب؟'))) return;

            const gradeSectionIds = new Set(targetSections.map(s => s.id));
            const isAllGrades = !gradeLevelId;

            showToast(__('⏳ جاري تحميل المعلمين...'));

            Promise.all([
                apiFetch('/admin/teachers'),
                apiFetch('/admin/schedules')
            ]).then(async ([allTeachers, allSchedules]) => {
                const teacherList = allTeachers || [];
                if (!teacherList.length) { showToast(__('⚠️ لا يوجد معلمون'), 'error'); return; }

                // Build busy map:
                // - Per-grade: only from OTHER grades' schedules (they stay after deletion)
                // - All grades: empty (everything gets reset)
                let busyMap = {};
                if (!isAllGrades) {
                    (allSchedules || []).forEach(s => {
                        if (s.teacher_id && !gradeSectionIds.has(s.section_id)) {
                            const key = s.day_of_week + '_' + s.period_number;
                            if (!busyMap[s.teacher_id]) busyMap[s.teacher_id] = {};
                            busyMap[s.teacher_id][key] = true;
                        }
                    });
                }

                showToast(__('⏳ جاري مسح الجداول القديمة...'));
                const delRes = await apiFetch('/admin/schedules/bulk-delete-by-sections', {
                    method: 'POST',
                    body: JSON.stringify({ section_ids: [...gradeSectionIds] })
                });

                const periodsOverride = {
                    'الرياضيات': 4, 'اللغة الإنجليزية': 4, 'العلوم الحياتية': 4,
                    'اللغة العربية': 4, 'التربية الإسلامية': 3,
                                    'التكنولوجيا والحاسوب': 2,
                'التربية الرياضية': 5, 'الدراسات الاجتماعية': 3
                };

                // Global count: how many sections have this subject at this time slot
                // key: "day_period_subjNorm" -> count
                const globalCount = {};
                let saved = 0;
                let failed = 0;
                let skipped = 0;
                let firstError = '';

                // 1. Build teacherBySpec ONCE from ALL teachers
                const teacherBySpec = {};
                teacherList.forEach(t => {
                    const spec = norm(t.teacher?.specialization || '');
                    if (!spec) return;
                    if (!teacherBySpec[spec]) teacherBySpec[spec] = [];
                    teacherBySpec[spec].push(t);
                });

                // 2. Define helper functions once
                const findMatchingSpec = subjNorm => {
                    for (const spec of Object.keys(teacherBySpec)) {
                        if (spec.includes(subjNorm) || subjNorm.includes(spec)) return spec;
                    }
                    return null;
                };

                const getTeacherLimit = subjNorm => {
                    const spec = findMatchingSpec(subjNorm);
                    return spec ? teacherBySpec[spec].length : 0;
                };

                const getAvailableTeacher = (subjNorm, slotKey, busyMap, workloadMap) => {
                    const spec = findMatchingSpec(subjNorm);
                    if (!spec) return null;
                    let candidates = teacherBySpec[spec].filter(t => !busyMap[t.id]?.[slotKey]);
                    if (!candidates.length) return null;
                    if (subjNorm === 'تربيهرياضيه') {
                        const pe = candidates.filter(t => norm(t.teacher?.specialization || '').includes('تربيهرياضيه'));
                        if (pe.length) candidates = pe;
                    }
                    if (workloadMap) {
                        candidates.sort((a, b) => (workloadMap[a.id] || 0) - (workloadMap[b.id] || 0));
                    }
                    return candidates[0];
                };

                // 3. Build plans for ALL grades first, store in gradePlans map
                const gradePlans = {};
                for (const gl of targetGLs) {
                    const gradeSubjects = allSubjects.filter(sub => sub.grade_level_id == gl.id);
                    if (!gradeSubjects.length) continue;

                    gradeSubjects.forEach(sub => {
                        if (periodsOverride[sub.name] !== undefined) sub.periods_per_week = periodsOverride[sub.name];
                    });

                    const gradeSections = allSections.filter(s => s.grade_level_id == gl.id);

                    // Build plan for this grade's sections
                    const plan = {};
                    gradeSections.forEach(sec => {
                        const daily = [[], [], [], [], []];
                        const dailyCount = [0, 0, 0, 0, 0];
                        const subjects = shuffle([...gradeSubjects]).sort((a, b) => (b.periods_per_week || 5) - (a.periods_per_week || 5));
                        subjects.forEach(sub => {
                            const need = sub.periods_per_week || 5;
                            const order = [0, 1, 2, 3, 4].sort((a, b) => dailyCount[a] - dailyCount[b]);
                            order.slice(0, need).forEach(d => {
                                daily[d].push(sub);
                                dailyCount[d]++;
                            });
                        });
                        plan[sec.id] = daily;
                    });

                    // Coordinate plan across sections
                    for (let d = 0; d < 5; d++) {
                        const daySubCounts = {};
                        gradeSections.forEach(sec => {
                            (plan[sec.id]?.[d] || []).forEach(sub => {
                                daySubCounts[sub.id] = (daySubCounts[sub.id] || 0) + 1;
                            });
                        });
                        for (const [subjId, count] of Object.entries(daySubCounts)) {
                            if (count < 3) continue;
                            const subj = gradeSubjects.find(s => s.id == subjId);
                            if (!subj) continue;
                            const extraSecs = gradeSections.filter(sec =>
                                plan[sec.id]?.[d]?.some(s => s.id == subjId)
                            );
                            for (let i = 2; i < extraSecs.length; i++) {
                                const sec = extraSecs[i];
                                for (let altDay = 0; altDay < 5; altDay++) {
                                    if (altDay === d) continue;
                                    if (!plan[sec.id]?.[altDay]) continue;
                                    if (plan[sec.id][altDay].some(s => s.id == subjId)) continue;
                                    if (plan[sec.id][altDay].length >= 5) continue;
                                    const idx = plan[sec.id][d].indexOf(subj);
                                    if (idx !== -1) {
                                        const swap = plan[sec.id][altDay].find(s => s.id != subjId);
                                        if (swap && !plan[sec.id][d].some(s => s.id == swap.id)) {
                                            plan[sec.id][d][idx] = swap;
                                            plan[sec.id][altDay][plan[sec.id][altDay].indexOf(swap)] = subj;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    gradePlans[gl.id] = { plan, gradeSubjects, gradeSections };
                }

                // 4b. Global plan coordination based on teacher counts
                for (let d = 0; d < 5; d++) {
                    const globalDayCount = {};
                    for (const gl of targetGLs) {
                        const gp = gradePlans[gl.id];
                        if (!gp) continue;
                        for (const sec of gp.gradeSections) {
                            (gp.plan[sec.id]?.[d] || []).forEach(sub => {
                                const key = sub.id + '_' + norm(sub.name);
                                if (!globalDayCount[key]) globalDayCount[key] = { sub, sections: [] };
                                globalDayCount[key].sections.push({ gradeId: gl.id, secId: sec.id });
                            });
                        }
                    }
                    for (const [key, data] of Object.entries(globalDayCount)) {
                        const subjName = norm(data.sub.name);
                        const spec = findMatchingSpec(subjName);
                        if (!spec) continue;
                        const teacherCount = (teacherBySpec[spec] || []).length;
                        const maxPerDay = teacherCount * 5;
                        if (data.sections.length <= maxPerDay) continue;
                        const excess = data.sections.slice(maxPerDay);
                        for (const { gradeId, secId } of excess) {
                            const gp = gradePlans[gradeId];
                            if (!gp) continue;
                            const plan = gp.plan[secId];
                            if (!plan) continue;
                            const idx = plan[d].indexOf(data.sub);
                            if (idx === -1) continue;
                            for (let altDay = 0; altDay < 5; altDay++) {
                                if (altDay === d) continue;
                                if (plan[altDay].length >= 5) continue;
                                if (plan[altDay].some(s => s.id == data.sub.id)) continue;
                                const swap = plan[altDay].find(s => s.id != data.sub.id);
                                if (swap && !plan[d].some(s => s.id == swap.id)) {
                                    plan[d][idx] = swap;
                                    plan[altDay][plan[altDay].indexOf(swap)] = data.sub;
                                }
                                break;
                            }
                        }
                    }
                }

                // 4c. teacherWorkload defined before day loop (persists across grades and days)
                const teacherWorkload = {};

                // 5. Process by day with shuffled grades
                for (let d = 0; d < 5; d++) {
                    const dayOrder = shuffle([...targetGLs].filter(gl => gradePlans[gl.id]));

                    for (const gl of dayOrder) {
                        const { plan, gradeSubjects, gradeSections } = gradePlans[gl.id];

                        const sectionRemaining = {};
                        gradeSections.forEach(sec => {
                            sectionRemaining[sec.id] = shuffle([...(plan[sec.id]?.[d] || [])]);
                        });

                        const sectionAssignments = {};
                        gradeSections.forEach(sec => { sectionAssignments[sec.id] = []; });

                        for (let p = 1; p <= 5; p++) {
                            const teacherSlotKey = days[d] + '_' + p;

                            const activeSecs = gradeSections.filter(sec => sectionRemaining[sec.id]?.length);

                            const optionsList = [];
                            for (const sec of activeSecs) {
                                const rem = sectionRemaining[sec.id];
                                const candidates = [];
                                for (const sub of rem) {
                                    const subjNorm = norm(sub.name);
                                    const teacher = getAvailableTeacher(subjNorm, teacherSlotKey, busyMap, teacherWorkload);
                                    if (teacher) {
                                        candidates.push({ sub, subjNorm, teacher });
                                    }
                                }
                                if (candidates.length) {
                                    optionsList.push({ secId: sec.id, candidates, remaining: rem });
                                }
                            }

                            optionsList.sort((a, b) => a.candidates.length - b.candidates.length);
                            const assignedThisPeriod = new Set();
                            for (const opt of optionsList) {
                                opt.candidates = opt.candidates.filter(c => !busyMap[c.teacher.id]?.[teacherSlotKey]);
                                if (!opt.candidates.length) continue;

                                const rem = sectionRemaining[opt.secId];
                                opt.candidates.sort((x, y) =>
                                    (teacherWorkload[x.teacher.id] || 0) - (teacherWorkload[y.teacher.id] || 0));
                                const choice = opt.candidates[0];

                                const idx = rem.indexOf(choice.sub);
                                if (idx !== -1) rem.splice(idx, 1);

                                if (!busyMap[choice.teacher.id]) busyMap[choice.teacher.id] = {};
                                busyMap[choice.teacher.id][teacherSlotKey] = true;

                                sectionAssignments[opt.secId].push({
                                    period: p, sub: choice.sub,
                                    subjNorm: choice.subjNorm,
                                    teacherSlotKey, chosenTeacher: choice.teacher
                                });
                                assignedThisPeriod.add(opt.secId);
                            }

                            const remainingSecs = gradeSections.filter(sec =>
                                sectionRemaining[sec.id]?.length &&
                                !assignedThisPeriod.has(sec.id)
                            );
                            for (const sec of remainingSecs) {
                                const rem = sectionRemaining[sec.id];
                                let found = false;
                                for (let si = 0; si < rem.length; si++) {
                                    const sub = rem[si];
                                    const subjNorm = norm(sub.name);
                                    const teacher = getAvailableTeacher(subjNorm, teacherSlotKey, busyMap, teacherWorkload);
                                    if (teacher) {
                                        rem.splice(si, 1);
                                        if (!busyMap[teacher.id]) busyMap[teacher.id] = {};
                                        busyMap[teacher.id][teacherSlotKey] = true;
                                        sectionAssignments[sec.id].push({
                                            period: p, sub, subjNorm,
                                            teacherSlotKey, chosenTeacher: teacher
                                        });
                                        found = true;
                                        break;
                                    }
                                }
                                if (!found) {
                                    // No subject has an available teacher at this period.
                                    // Keep all subjects in remaining — they'll be picked at a later period.
                                }
                            }
                        }

                        // Cleanup: assign any remaining subjects to empty periods
                        for (const sec of gradeSections) {
                            const rem = sectionRemaining[sec.id];
                            if (!rem?.length) continue;
                            const assigns = sectionAssignments[sec.id] || [];
                            for (let p = 1; p <= 5; p++) {
                                if (!rem.length) break;
                                if (assigns.some(a => a.period === p)) continue;
                                const sub = rem.shift();
                                sectionAssignments[sec.id].push({
                                    period: p, sub,
                                    subjNorm: norm(sub.name),
                                    teacherSlotKey: days[d] + '_' + p,
                                    chosenTeacher: null
                                });
                            }
                        }

                        // Repair: try to fix no-teacher assignments by swapping periods within the same section
                        let repairImproved = true;
                        while (repairImproved) {
                            repairImproved = false;
                            for (const sec of gradeSections) {
                                const assigns = sectionAssignments[sec.id] || [];
                                for (const bad of assigns) {
                                    if (bad.chosenTeacher) continue;
                                    const subjNorm = norm(bad.sub.name);
                                    const spec = findMatchingSpec(subjNorm);
                                    if (!spec) continue;
                                    const teachers = teacherBySpec[spec] || [];
                                    let swapped = false;
                                    for (const teacher of teachers) {
                                        if (swapped) break;
                                        for (const good of assigns) {
                                            if (!good.chosenTeacher || good.period === bad.period) continue;
                                            const goodSlotKey = days[d] + '_' + good.period;
                                            const badSlotKey = days[d] + '_' + bad.period;
                                            if (busyMap[teacher.id]?.[goodSlotKey]) continue;
                                            if (busyMap[good.chosenTeacher.id]?.[badSlotKey]) continue;
                                            const origTeacher = good.chosenTeacher;
                                            const origSub = good.sub;
                                            const origNorm = good.subjNorm;
                                            if (!busyMap[origTeacher.id]) busyMap[origTeacher.id] = {};
                                            busyMap[origTeacher.id][badSlotKey] = true;
                                            if (!busyMap[teacher.id]) busyMap[teacher.id] = {};
                                            busyMap[teacher.id][goodSlotKey] = true;
                                            good.sub = bad.sub;
                                            good.subjNorm = subjNorm;
                                            good.chosenTeacher = teacher;
                                            bad.sub = origSub;
                                            bad.subjNorm = origNorm;
                                            bad.chosenTeacher = origTeacher;
                                            swapped = true;
                                            repairImproved = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // Create schedules by period
                        for (let p = 1; p <= 5; p++) {
                            for (const sec of gradeSections) {
                                const a = (sectionAssignments[sec.id] || []).find(x => x.period === p);
                                if (!a) continue;
                                try {
                                    await apiFetch('/admin/schedules', {
                                        method: 'POST',
                                        body: JSON.stringify({
                                            section_id: sec.id,
                                            day_of_week: days[d],
                                            period_number: a.period,
                                            subject_id: a.sub.id,
                                            teacher_id: a.chosenTeacher ? a.chosenTeacher.id : null,
                                            room: '',
                                        })
                                    });

                                    const slotKey = d + '_' + a.period + '_' + a.subjNorm;
                                    globalCount[slotKey] = (globalCount[slotKey] || 0) + 1;

                                    if (a.chosenTeacher) {
                                        teacherWorkload[a.chosenTeacher.id] = (teacherWorkload[a.chosenTeacher.id] || 0) + 1;
                                        saved++;
                                    } else {
                                        skipped++;
                                    }
                                } catch(e) {
                                    failed++;
                                    if (failed == 1 && e.message) firstError = e.message;
                                    if (failed <= 3 && e.message) console.error('فشلت حصة:', e.message);
                                }
                            }
                        }
                    }
                }

                        let msg = msgPrefix + __('✅ تم توزيع ') + (saved + skipped) + __(' حصة');
                        if (saved > 0) msg += __(' (') + saved + __(' مع معلم)');
                        if (skipped > 0) msg += __(' (') + skipped + __(' بدون معلم)');
                        if (failed > 0) msg += __(' ❌ ') + failed + __(' فشلت');
                        if (firstError) msg += __('\n⚠️ أول خطأ: ') + firstError;
                        showToast(msg + __(' ⏳ جاري إصلاح المتبقية...'), 'warning');
                        
                        // Final repair: auto-fill remaining empty slots
                        if (skipped > 0 || failed > 0) {
                            try {
                                const allScheds = await apiFetch('/admin/schedules');
                                const emptySlots = [];
                                for (const sec of allSections) {
                                    const secScheds = allScheds.filter(s => s.section_id == sec.id);
                                    for (const sched of secScheds) {
                                        if (!sched.teacher_id) {
                                            emptySlots.push({ sched, secId: sec.id, subjectName: sched.subject?.name || '', day: sched.day_of_week, period: sched.period_number });
                                        }
                                    }
                                }
                                if (emptySlots.length) {
                                    // Rebuild cross-grade teacher pool for repair
                                    const repairSpecs = {};
                                    teacherList.forEach(t => {
                                        const spec = norm(t.teacher?.specialization || '');
                                        if (!spec) return;
                                        if (!repairSpecs[spec]) repairSpecs[spec] = [];
                                        repairSpecs[spec].push(t);
                                    });
                                    const busy = {};
                                    allScheds.forEach(s => { if (s.teacher_id) { const k = s.day_of_week + '_' + s.period_number; if (!busy[s.teacher_id]) busy[s.teacher_id] = {}; busy[s.teacher_id][k] = true; } });
                                    const wl = {};
                                    allScheds.forEach(s => { if (s.teacher_id) wl[s.teacher_id] = (wl[s.teacher_id] || 0) + 1; });
                                    let fixed = 0;
                                    for (const slot of emptySlots) {
                                        const sk = slot.day + '_' + slot.period;
                                        const subjNorm = norm(slot.subjectName);
                                        const spec = Object.keys(repairSpecs).find(sp => sp.includes(subjNorm) || subjNorm.includes(sp));
                                        if (!spec) continue;
                                        let cands = repairSpecs[spec].filter(t => !busy[t.id]?.[sk]);
                                        if (!cands.length) {
                                            for (const busyT of repairSpecs[spec]) {
                                                const bs = allScheds.find(s => s.teacher_id === busyT.id && s.day_of_week === slot.day && s.period_number === slot.period);
                                                if (!bs) continue;
                                                const bSubj = allSubjects.find(sub => sub.id === bs.subject_id);
                                                if (!bSubj) continue;
                                                const bNorm = norm(bSubj.name);
                                                const bSpec = Object.keys(repairSpecs).find(sp => sp.includes(bNorm) || bNorm.includes(sp));
                                                if (!bSpec) continue;
                                                const alt = repairSpecs[bSpec].filter(t => t.id !== busyT.id && !busy[t.id]?.[sk]);
                                                if (alt.length) {
                                                    await apiFetch('/admin/schedules/' + slot.sched.id, { method: 'PUT', body: JSON.stringify({ teacher_id: busyT.id }) });
                                                    await apiFetch('/admin/schedules/' + bs.id, { method: 'PUT', body: JSON.stringify({ teacher_id: alt[0].id }) });
                                                    if (!busy[alt[0].id]) busy[alt[0].id] = {};
                                                    busy[alt[0].id][sk] = true;
                                                    wl[alt[0].id] = (wl[alt[0].id] || 0) + 1;
                                                    fixed++;
                                                    slot.fixed = true;
                                                    break;
                                                }
                                            }
                                            if (!slot.fixed) continue;
                                        } else {
                                            cands.sort((a, b) => (wl[a.id] || 0) - (wl[b.id] || 0));
                                            await apiFetch('/admin/schedules/' + slot.sched.id, { method: 'PUT', body: JSON.stringify({ teacher_id: cands[0].id }) });
                                            if (!busy[cands[0].id]) busy[cands[0].id] = {};
                                            busy[cands[0].id][sk] = true;
                                            wl[cands[0].id] = (wl[cands[0].id] || 0) + 1;
                                            fixed++;
                                }
                                if (fixed > 0) showToast(__('✅ تم إصلاح ') + fixed + __(' حصة بنجاح'), 'success');
                                }
                            }
                            } catch(e) { /* repair failed silently */ }
                        }

                        // Sync teacher-class assignments from schedules
                        try {
                            const allScheds = await apiFetch('/admin/schedules');
                            const teacherSections = {};
                            (allScheds || []).forEach(s => {
                                if (s.teacher_id && s.section_id) {
                                    if (!teacherSections[s.teacher_id]) teacherSections[s.teacher_id] = new Set();
                                    teacherSections[s.teacher_id].add(s.section_id);
                                }
                            });
                            for (const [tId, secIds] of Object.entries(teacherSections)) {
                                await apiFetch('/admin/teachers/' + tId + '/classes', {
                                    method: 'PUT',
                                    body: JSON.stringify({ class_ids: [...secIds] })
                                });
                            }
                        } catch(e) {}

                        const linkTeachers = `<br><small><a href="/admin/teachers" style="color:#fff;text-decoration:underline;">${__('👨‍🏫 عرض المعلمين →')}</a></small>`;
                        showToast(msgPrefix + __('✅ تم توزيع ') + saved + __(' حصة مع معلمين') + (skipped ? __(' (') + skipped + __(' بدون معلم)') : '') + linkTeachers, skipped > 0 ? 'warning' : 'success');
                        if (currentSectionId) setTimeout(() => loadGrid(), 500);
                }).catch(() => showToast(__('⚠️ فشل تحميل المعلمين'), 'error'));
        }

        // ---- Clear all schedules ----
        function clearAllSchedules() {
            if (!confirm(__('🗑️ تفريغ جميع الجداول لجميع الصفوف؟ هذا الإجراء لا يمكن التراجع عنه.'))) return;
            if (!confirm(__('تأكيد: هل أنت متأكد؟ سيتم حذف جميع الحصص.'))) return;

            showToast(__('⏳ جاري الحذف...'));
            apiFetch('/admin/schedules').then(list => {
                const all = list || [];
                if (!all.length) { showToast(__('✅ لا توجد جداول لتفريغها')); return; }

                Promise.all(all.map(s =>
                    apiFetch('/admin/schedules/' + s.id, { method: 'DELETE' }).catch(() => null)
                )).then(results => {
                    const deleted = results.filter(r => r !== null).length;
                    const failed = all.length - deleted;
                    showToast(__('✅ تم حذف ') + deleted + __(' حصة') + (failed ? __(' (فشل ') + failed + __(')') : ''));
                    if (currentSectionId) setTimeout(() => loadGrid(), 300);
                });
            }).catch(() => showToast(__('⚠️ فشل تحميل الجداول'), 'error'));
        }

    </script>
    <style>
        .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
        #scheduleGrid td:hover { background:#e8f0fe !important; }
    </style>
@stop
