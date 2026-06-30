@extends('layouts.dashboard')

@section('title', __('الدرجات'))
@section('page-title', __('الدرجات'))

@section('sidebar')
    <a href="/teacher">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/teacher/grades" class="active">📝 <span>{{ __('الدرجات') }}</span></a>
    <a href="/teacher/schedule">📅 <span>{{ __('جدولي') }}</span></a>
    <a href="/teacher/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/teacher/library">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
    <div class="card">
        <div style="display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid #e0e0e0;">
            <button class="tab-btn active" id="tabAdd" onclick="switchTab('add')" style="padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:700;color:var(--primary);border-bottom:3px solid var(--primary);">{{ __('📥 إضافة درجات') }}</button>
            <button class="tab-btn" id="tabView" onclick="switchTab('view')" style="padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:600;color:#888;border-bottom:3px solid transparent;">{{ __('👁️ عرض الدرجات') }}</button>
        </div>

        <div id="tabAddContent">
            <!-- Grade Distribution -->
            <div style="background:#f9f9f9;border-radius:10px;padding:15px;margin-bottom:20px;">
                <h4 style="margin:0 0 10px 0;font-size:15px;">{{ __('⚖️ توزيع العلامات') }}</h4>
                <div id="distGrid" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;"></div>
                <div style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                    <span style="font-size:14px;font-weight:700;">{{ __('المجموع:') }} <span id="distTotal" style="color:#1976d2;">0</span></span>
                    <button class="btn btn-primary" onclick="saveDistribution()" style="font-size:13px;padding:6px 16px;">{{ __('💾 حفظ التوزيع') }}</button>
                </div>
                <div id="distResult" style="margin-top:8px;"></div>
                <div id="distMsg" style="margin-top:8px;padding:8px 12px;border-radius:8px;background:#fdf1d9;color:#93680c;font-size:13px;">
                    {{ __('⚠️ احفظ توزيع العلامات أولاً قبل تحميل النموذج') }}
                </div>
            </div>

            <!-- Excel Import -->
            <hr style="margin:25px 0;">
            <h3>{{ __('📥 استيراد من Excel') }}</h3>
            <div style="background:#e3f2fd;padding:12px;border-radius:10px;margin-bottom:15px;font-size:13px;">
                <p style="margin:0;color:#555;">{{ __('حمّل نموذج Excel جاهز بالطلاب، املأ الدرجات، ثم ارفع الملف.') }}</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
                <select id="importSection" onchange="onImportSectionChange()" style="flex:1;min-width:250px;padding:8px 12px;border:1px solid #ddd;border-radius:8px;">
                    <option value="">{{ __('اختر الصف...') }}</option>
                </select>
                <button class="btn btn-primary" onclick="downloadGradeTemplate()" id="downloadGradeBtn" disabled style="font-size:13px;padding:6px 16px;">{{ __('📥 تحميل النموذج') }}</button>
            </div>
            <div id="prefilledInfo" style="display:none;background:#fdf1d9;padding:10px;border-radius:8px;margin-bottom:15px;font-size:13px;"></div>
            <form id="importGradesForm">
                <input type="file" name="file" accept=".xlsx,.csv,.ods" required style="margin-bottom:10px;">
                <button type="submit" class="btn btn-primary" style="font-size:13px;padding:6px 16px;">{{ __('📤 رفع واستيراد') }}</button>
            </form>
            <div id="gradesImportResult" class="loading" style="display:none;margin-top:10px;"></div>
        </div>

        <div id="tabViewContent" style="display:none;">
            <h3>{{ __('👁️ عرض الدرجات') }}</h3>
            <div style="margin-bottom:20px;">
                <label style="font-weight:700;display:block;margin-bottom:6px;">{{ __('الصف والشعبة') }}</label>
                <select id="viewSection" onchange="viewLoadGrades()" style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:300px;"></select>
            </div>

            <div class="loading" id="viewLoading">{{ __('📌 اختر الصف والشعبة لعرض الدرجات') }}</div>
            <div id="viewSummary" style="display:none;flex-wrap:wrap;gap:12px;margin-bottom:20px;"></div>
            <div id="viewTableWrap" style="display:none;">
                <div style="overflow-x:auto;border-radius:10px;border:1px solid #e0e0e0;">
                    <table id="viewTable" style="width:100%;border-collapse:collapse;">
                        <thead id="viewThead"></thead>
                        <tbody id="viewBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let classesData = [];
        let currentDist = [];
        let distributionLabels = [__('امتحان شهري أول'), __('امتحان نصفي'), __('امتحان شهري ثاني'), __('امتحان نهائي')];
        let distributionSaved = false;

        apiFetch('/teacher/grades/distribution').then(dist => {
            currentDist = dist || [];
            if (currentDist.length === 4) {
                distributionLabels = currentDist.map(d => d.label);
                distributionSaved = true;
                renderDist();
                updateDownloadBtn();
            }
        });

        apiFetch('/teacher/classes').then(list => {
            classesData = list || [];
            const viewSectionSel = document.getElementById('viewSection');
            viewSectionSel.innerHTML = '<option value="">' + __('اختر الصف والشعبة') + '</option>';
            classesData.forEach(s => {
                const label = s.name + (s.section ? ' - ' + __('شعبة') + ' ' + sectionLabel(s.section) : '');
                viewSectionSel.innerHTML += `<option value="${s.id}">${label}</option>`;
            });

            // Populate import section dropdown
            const importSel = document.getElementById('importSection');
            importSel.innerHTML = '<option value="">' + __('اختر الصف...') + '</option>';
            classesData.forEach(s => {
                const label = s.name + (s.section ? ' - ' + __('شعبة') + ' ' + sectionLabel(s.section) : '');
                importSel.innerHTML += `<option value="${s.id}">${label}</option>`;
            });
        });

        function switchTab(tab) {
            document.getElementById('tabAddContent').style.display = tab === 'add' ? '' : 'none';
            document.getElementById('tabViewContent').style.display = tab === 'view' ? '' : 'none';
            document.getElementById('tabAdd').className = tab === 'add' ? 'tab-btn active' : 'tab-btn';
            document.getElementById('tabView').className = tab === 'view' ? 'tab-btn active' : 'tab-btn';
            if (tab === 'add') {
                document.getElementById('tabAdd').style.cssText = 'padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:700;color:var(--primary);border-bottom:3px solid var(--primary);';
                document.getElementById('tabView').style.cssText = 'padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:600;color:#888;border-bottom:3px solid transparent;';
            } else {
                document.getElementById('tabView').style.cssText = 'padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:700;color:var(--primary);border-bottom:3px solid var(--primary);';
                document.getElementById('tabAdd').style.cssText = 'padding:10px 24px;border:none;background:none;cursor:pointer;font-size:15px;font-weight:600;color:#888;border-bottom:3px solid transparent;';
            }
        }

        // ========== DISTRIBUTION ==========
        function renderDist() {
            const grid = document.getElementById('distGrid');
            if (!grid) return;
            grid.innerHTML = '';
            currentDist.forEach((item, i) => {
                const div = document.createElement('div');
                div.style.cssText = 'flex:1;min-width:140px;background:#f9f9f9;padding:12px;border-radius:10px;text-align:center;';
                div.innerHTML = `<label style="display:block;font-size:13px;margin-bottom:6px;color:#555;">${item.label}</label>
                    <input type="number" min="1" max="100" value="${item.max}" data-index="${i}"
                        style="width:80px;padding:8px;border:1px solid #ccc;border-radius:8px;text-align:center;font-size:16px;font-weight:700;"
                        oninput="updateDistTotal()">`;
                grid.appendChild(div);
            });
            updateDistTotal();
        }

        function updateDistTotal() {
            const inputs = document.querySelectorAll('#distGrid input');
            let total = 0;
            inputs.forEach(inp => { total += parseInt(inp.value) || 0; });
            const span = document.getElementById('distTotal');
            if (!span) return;
            span.textContent = toArabicNum(total);
            span.style.color = total === 100 ? '#2e7d32' : '#c62828';
        }

        function saveDistribution() {
            const inputs = document.querySelectorAll('#distGrid input');
            const dist = [];
            inputs.forEach(inp => {
                const i = parseInt(inp.dataset.index);
                dist.push({
                    key: currentDist[i].key,
                    label: currentDist[i].label,
                    max: parseInt(inp.value) || 0,
                });
            });
            const total = dist.reduce((s, d) => s + d.max, 0);
            if (total !== 100) { showToast(__('⚠️ المجموع يجب أن يساوي 100 (الموجود: ') + total + ')', 'error'); return; }
            apiFetch('/teacher/grades/distribution', {
                method: 'PUT',
                body: JSON.stringify({ distribution: dist }),
            }).then(r => {
                showToast(r.message);
                distributionSaved = true;
                currentDist = dist;
                distributionLabels = dist.map(d => d.label);
                updateDownloadBtn();
                document.getElementById('distResult').innerHTML = '<div style="padding:10px;background:#e1f7e7;color:#157a35;border-radius:8px;">' + r.message + '</div>';
            }).catch(e => {
                showToast('⚠️ ' + (e.message || __('خطأ')), 'error');
            });
        }

        // ========== EXCEL IMPORT ==========
        function updateDownloadBtn() {
            const btn = document.getElementById('downloadGradeBtn');
            const distMsg = document.getElementById('distMsg');
            const sectionId = document.getElementById('importSection').value;
            btn.disabled = !(sectionId && distributionSaved);
            if (distMsg) {
                distMsg.style.display = distributionSaved ? 'none' : '';
            }
        }

        function onImportSectionChange() {
            const sectionId = document.getElementById('importSection').value;
            const info = document.getElementById('prefilledInfo');
            info.style.display = 'none';
            if (sectionId) {
                const section = classesData.find(s => s.id == sectionId);
                if (section) {
                    const studentCount = (section.students || []).length;
                    if (studentCount > 0) {
                        info.style.display = 'block';
                        info.innerHTML = __('👨‍🎓 عدد الطلاب:') + ' <strong>' + toArabicNum(studentCount) + '</strong> ' + __('طالب');
                    }
                }
            }
            updateDownloadBtn();
        }

        function downloadGradeTemplate() {
            const sectionId = document.getElementById('importSection').value;
            if (!sectionId) { showToast(__('⚠️ اختر الصف أولاً'), 'error'); return; }
            const token = localStorage.getItem('token');
            if (!token) { showToast(__('⚠️ يجب تسجيل الدخول أولاً'), 'error'); return; }
            const url = API_BASE + '/teacher/grades/template?section_id=' + sectionId;
            fetch(url, { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } })
                .then(r => {
                    if (!r.ok) {
                        return r.text().then(body => {
                            let msg = __('خطأ') + ' ' + r.status;
                            try { const j = JSON.parse(body); msg = j.message || msg; } catch(e) {}
                            throw new Error(msg);
                        });
                    }
                    return r.blob();
                })
                .then(blob => {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = __('درجات') + '_' + sectionId + '.csv';
                    a.click();
                    URL.revokeObjectURL(a.href);
                })
                .catch(err => showToast('⚠️ ' + err.message, 'error'));
        }

        function setupImport(formId, url, resultId) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const fd = new FormData(form);
                const resultDiv = document.getElementById(resultId);
                resultDiv.style.display = '';
                resultDiv.innerHTML = __('جاري الرفع والاستيراد...');

                fetch(url, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' },
                    body: fd,
                }).then(r => {
                    if (!r.ok) {
                        return r.text().then(body => {
                            let msg = __('خطأ') + ' ' + r.status;
                            try { const j = JSON.parse(body); msg = j.message || msg; } catch(e) { msg = body.substring(0, 100); }
                            throw new Error(msg);
                        });
                    }
                    return r.json();
                }).then(d => {
                    if (d.confirmation_required) {
                        const oldList = (d.old_labels || []).join('، ');
                        const newList = (d.new_labels || []).join('، ');
                        const confirmMsg = `⚠️ ${__('توزيع العلامات الحالي مختلف عن القديم.')}\n\n${__('القديم:')} ${oldList}\n${__('الجديد:')} ${newList}\n\n${__('هل تريد استبدال التوزيعة القديمة بالجديدة؟')}`;
                        if (!confirm(confirmMsg)) {
                            resultDiv.innerHTML = '<div style="margin-top:10px;padding:12px;border-radius:8px;background:#fdf1d9;color:#93680c;">❌ ' + __('تم إلغاء الاستيراد.') + ' ' + __('التوزيعة القديمة:') + ' ' + oldList + '</div>';
                            return;
                        }
                        // Re-submit with force flag
                        fd.append('force', '1');
                        fetch(url, {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' },
                            body: fd,
                        }).then(r => r.ok ? r.json() : r.text().then(t => { throw new Error(t); }))
                        .then(d2 => {
                            let html = '<div style="margin-top:10px;padding:12px;border-radius:8px;background:#e1f7e7;color:#157a35;">';
                            html += '<div style="font-weight:700;font-size:15px;margin-bottom:8px;">✅ ' + d2.message + '</div>';
                            if (d2.sections && d2.sections.length) {
                                html += '<div style="font-size:13px;">' + d2.sections.join('، ') + '</div>';
                            }
                            html += '</div>';
                            if (d2.errors && d2.errors.length) {
                                const warnings = d2.errors.filter(e => e.includes('⚠️'));
                                const realErrors = d2.errors.filter(e => !e.includes('⚠️'));
                                if (realErrors.length) {
                                    html += '<div style="margin-top:8px;padding:10px;border-radius:8px;background:#fde3e3;color:#b8232e;font-size:13px;"><strong>' + __('أخطاء:') + '</strong><ul>';
                                    realErrors.forEach(e => { html += '<li>' + e + '</li>'; });
                                    html += '</ul></div>';
                                }
                                if (warnings.length) {
                                    html += '<div style="margin-top:8px;padding:10px;border-radius:8px;background:#fdf1d9;color:#93680c;font-size:13px;"><strong>' + __('ملاحظات:') + '</strong><ul>';
                                    warnings.forEach(e => { html += '<li>' + e + '</li>'; });
                                    html += '</ul></div>';
                                }
                            }
                            resultDiv.innerHTML = html;
                            form.reset();
                        }).catch(err => {
                            resultDiv.innerHTML = '<div style="margin-top:10px;padding:12px;border-radius:8px;background:#fde3e3;color:#b8232e;">' + __('خطأ:') + ' ' + err.message + '</div>';
                        });
                        return;
                    }
                    let html = '<div style="margin-top:10px;padding:12px;border-radius:8px;background:#e1f7e7;color:#157a35;">';
                    html += '<div style="font-weight:700;font-size:15px;margin-bottom:8px;">✅ ' + d.message + '</div>';
                    if (d.sections && d.sections.length) {
                        html += '<div style="font-size:13px;">' + d.sections.join('، ') + '</div>';
                    }
                    html += '</div>';
                    if (d.errors && d.errors.length) {
                        const warnings = d.errors.filter(e => e.includes('⚠️'));
                        const realErrors = d.errors.filter(e => !e.includes('⚠️'));
                        if (realErrors.length) {
                            html += '<div style="margin-top:8px;padding:10px;border-radius:8px;background:#fde3e3;color:#b8232e;font-size:13px;"><strong>' + __('أخطاء:') + '</strong><ul>';
                            realErrors.forEach(e => { html += '<li>' + e + '</li>'; });
                            html += '</ul></div>';
                        }
                        if (warnings.length) {
                            html += '<div style="margin-top:8px;padding:10px;border-radius:8px;background:#fdf1d9;color:#93680c;font-size:13px;"><strong>' + __('ملاحظات:') + '</strong><ul>';
                            warnings.forEach(e => { html += '<li>' + e + '</li>'; });
                            html += '</ul></div>';
                        }
                    }
                    resultDiv.innerHTML = html;
                    form.reset();
                }).catch(err => {
                    resultDiv.innerHTML = '<div style="margin-top:10px;padding:12px;border-radius:8px;background:#fde3e3;color:#b8232e;">' + __('خطأ:') + ' ' + err.message + '</div>';
                });
            });
        }

        setupImport('importGradesForm', API_BASE + '/teacher/import/grades', 'gradesImportResult');

        // ========== VIEW TAB ==========
        function viewLoadGrades() {
            const sectionId = document.getElementById('viewSection').value;
            const loading = document.getElementById('viewLoading');
            const tableWrap = document.getElementById('viewTableWrap');
            const summary = document.getElementById('viewSummary');

            if (!sectionId) {
                loading.textContent = __('📌 اختر الصف والشعبة لعرض الدرجات');
                loading.style.display = '';
                tableWrap.style.display = 'none';
                summary.style.display = 'none';
                return;
            }

            loading.textContent = __('⏳ جاري تحميل الدرجات...');
            loading.style.display = '';
            tableWrap.style.display = 'none';
            summary.style.display = 'none';

            apiFetch('/teacher/grades?section_id=' + sectionId).then(grades => {
                loading.style.display = 'none';

                if (!grades || !grades.length) {
                    loading.textContent = __('⚠️ لا توجد درجات مسجلة لهذه الشعبة');
                    loading.style.display = '';
                    return;
                }

                // Build student list from grades
                const studentsMap = {};
                grades.forEach(g => {
                    if (!studentsMap[g.student_id]) {
                        studentsMap[g.student_id] = {
                            id: g.student_id,
                            name: g.student?.user?.name || '-',
                            grades: {}
                        };
                    }
                    studentsMap[g.student_id].grades[g.exam_type] = g.score;
                });
                const students = Object.values(studentsMap);

                // Get all unique exam types
                const isFinal = type => type === 'الدرجة النهائية' || type === 'نهائي';
                const examTypes = [...new Set(grades.map(g => g.exam_type))].filter(t => !isFinal(t));
                if ([...new Set(grades.map(g => g.exam_type))].some(t => isFinal(t))) {
                    examTypes.push('الدرجة النهائية');
                }

                // Summary cards
                let gradesCount = grades.length;
                summary.innerHTML = `
                    <div style="flex:1;min-width:120px;background:#e3f2fd;padding:12px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:700;color:#1565c0;">${students.length}</div>
                        <div style="font-size:13px;color:#555;">${__('طالب')}</div>
                    </div>
                    <div style="flex:1;min-width:120px;background:#e8f5e9;padding:12px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:700;color:#2e7d32;">${gradesCount}</div>
                        <div style="font-size:13px;color:#555;">${__('درجة مسجلة')}</div>
                    </div>
                `;
                summary.style.display = 'flex';

                // Table header
                let thead = '<tr style="background:var(--blue-main);color:#fff;"><th style="width:45px;padding:10px 8px;">#</th><th style="padding:10px 8px;text-align:right;">' + __('الطالب') + '</th>';
                examTypes.forEach((l, i) => {
                    const isFinalCol = isFinal(l);
                    const bg = isFinalCol ? '#1b5e20' : '';
                    const color = isFinalCol ? '#fff' : '';
                    thead += `<th style="padding:10px 8px;white-space:nowrap;${bg ? 'background:'+bg+';' : ''}${color ? 'color:'+color+';' : ''}">${__(l)}</th>`;
                });
                thead += '</tr>';
                document.getElementById('viewThead').innerHTML = thead;

                // Table body
                let tbody = '';
                students.forEach((s, i) => {
                    const bg = i % 2 === 0 ? '#fff' : '#f8f9fa';
                    tbody += `<tr style="background:${bg};">`;
                    tbody += `<td style="text-align:center;padding:8px;color:#888;">${i + 1}</td>`;
                    tbody += `<td style="padding:8px;text-align:right;"><strong>${s.name}</strong></td>`;
                    examTypes.forEach(l => {
                        const score = s.grades[l] !== undefined ? s.grades[l] : null;
                        const isFinalCol = isFinal(l);
                        const cellBg = isFinalCol && score !== null ? '#c8e6c9' : '';
                        const fontWeight = isFinalCol ? '700' : '';
                        tbody += `<td style="text-align:center;padding:8px;${score === null ? 'color:#ccc;' : ''}${cellBg ? 'background:'+cellBg+';' : ''}${fontWeight ? 'font-weight:'+fontWeight+';' : ''}">${score !== null ? score : '—'}</td>`;
                    });
                    tbody += '</tr>';
                });

                document.getElementById('viewBody').innerHTML = tbody;
                tableWrap.style.display = '';
            }).catch(() => {
                loading.style.display = '';
                loading.textContent = __('❌ تعذر تحميل الدرجات');
            });
        }
    </script>
    <style>
        .tab-btn { transition: all 0.2s; }
        .tab-btn:hover { opacity: 0.8; }
        #viewTable td, #viewTable th { text-align: center; border-bottom: 1px solid #e0e0e0; }
        #viewTable th { white-space: nowrap; background: var(--blue-main); color: #fff; }
        #viewTable tr:hover { background: #e3f2fd !important; }
    </style>
@stop
