@extends('layouts.dashboard')

@section('title', __('التعلم الإلكتروني'))
@section('page-title', __('التعلم الإلكتروني'))

@section('sidebar')
    <a href="/student">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/student/e-learning" class="active">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/student/library">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
<div id="step1">
    <div class="header">
        <h1>{{ __('صفوفي') }}</h1>
    </div>
    <div class="loading" id="loadingSections">{{ __('جاري التحميل...') }}</div>
    <div id="sectionsGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px;"></div>
</div>

<div id="step2" style="display:none;">
    <div class="header">
        <div style="display:flex;align-items:center;gap:14px;">
            <button onclick="backToSections()" style="width:42px;height:42px;border-radius:var(--radius-sm);border:1px solid var(--border-soft);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;transition:all 0.2s;box-shadow:var(--shadow-sm);">⬅️</button>
            <h1 id="sectionTitle" style="margin:0;"></h1>
        </div>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('materials')" id="tabMaterials"><span class="tab-icon">📚</span> {{ __('المادة') }}</button>
        <button class="tab-btn" onclick="switchTab('quizzes')" id="tabQuizzes"><span class="tab-icon">📝</span> {{ __('الاختبارات') }}</button>
        <button class="tab-btn" onclick="switchTab('sessions')" id="tabSessions"><span class="tab-icon">🎥</span> {{ __('الحصص الإلكترونية') }}</button>
    </div>

    <div id="panelMaterials" class="tab-panel"><div id="materialsList"></div></div>
    <div id="panelQuizzes" class="tab-panel" style="display:none;"><div id="quizzesList"></div></div>
    <div id="panelSessions" class="tab-panel" style="display:none;"><div id="sessionsList"></div></div>
</div>

<div id="quizInfoModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:480px;box-shadow:var(--shadow-xl);overflow:hidden;animation:modalIn .25s ease;">
        <div style="padding:24px 28px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:48px;height:48px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;">📝</div>
                    <h3 id="quizInfoTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);"></h3>
                </div>
                <button onclick="closeQuizInfoModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);transition:all 0.15s;">✕</button>
            </div>
        </div>
        <div style="padding:0 28px 24px;">
            <div id="quizInfoMeta" style="background:var(--bg-secondary);border-radius:var(--radius-md);padding:16px;margin-bottom:16px;font-size:13px;line-height:2.2;color:var(--text-secondary);"></div>
            <div id="quizInfoBlocked" style="display:none;color:#c62828;font-weight:700;padding:12px;border-radius:var(--radius-sm);background:#fff3f3;margin-bottom:16px;font-size:14px;"></div>
            <div style="display:flex;gap:10px;">
                <button onclick="closeQuizInfoModal()" style="flex:1;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;border:1px solid var(--border-soft);background:var(--white);cursor:pointer;color:var(--text-secondary);transition:all 0.15s;">{{ __('إلغاء') }}</button>
                <button id="quizStartBtn" onclick="confirmStartQuiz()" class="btn btn-primary" style="flex:1.5;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">🚀 {{ __('ابدأ الاختبار') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="quizModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:680px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:modalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <h3 id="quizModalTitle" style="margin:0;font-size:16px;font-weight:800;color:var(--text-primary);"></h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <div id="quizTimer" style="font-size:14px;font-weight:700;color:var(--text-primary);padding:6px 14px;background:var(--bg-secondary);border-radius:var(--radius-sm);font-variant-numeric:tabular-nums;"></div>
                <button onclick="closeQuizModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
        </div>
        <div style="padding:20px 28px 28px;">
            <div id="quizQuestions"></div>
            <div style="text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid var(--border-soft);">
                <button onclick="submitQuiz()" id="submitQuizBtn" class="btn btn-primary" style="padding:12px 40px;border-radius:var(--radius-sm);font-size:15px;font-weight:700;border:none;cursor:pointer;display:none;">📤 {{ __('تسليم الاختبار') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="resultQuizModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:650px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:modalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <h3 style="margin:0;font-size:17px;font-weight:800;">📊 {{ __('نتائج الاختبار') }}</h3>
            <button onclick="closeResultQuizModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
        </div>
        <div style="padding:24px 28px;">
            <div id="resultQuizBody"></div>
            <button class="btn btn-primary" onclick="closeResultQuizModal()" style="width:100%;margin-top:20px;padding:12px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">{{ __('إغلاق') }}</button>
        </div>
    </div>
</div>

<style>
    @keyframes modalIn { from { opacity:0; transform:translateY(16px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    @keyframes iconFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-3px)} }

    .el-card { background:var(--white); border-radius:var(--radius-lg); padding:24px; cursor:pointer; transition:all 0.25s; border:2px solid var(--border-soft); position:relative; overflow:hidden; }
    .el-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:var(--blue-main); }
    .el-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; background:linear-gradient(135deg,rgba(74,127,247,0.06),transparent); border-radius:0 0 0 80px; }
    .el-card .icon { width:56px; height:56px; border-radius:var(--radius-lg); background:linear-gradient(135deg,var(--blue-main),#6c5ce7); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:#fff; box-shadow:0 4px 14px rgba(74,127,247,0.3); }
    .el-card .name { font-weight:800; font-size:17px; margin-bottom:6px; color:var(--text-primary); }
    .el-card .section-tag { display:inline-block; padding:4px 14px; border-radius:var(--radius-sm); font-size:12px; font-weight:700; background:var(--blue-50); color:var(--blue-main); }

    .icon-box { width:50px; height:50px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; color:#fff; transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1); position:relative; z-index:1; }
    .icon-box::after { content:''; position:absolute; inset:-5px; border-radius:inherit; opacity:0; transition:opacity 0.3s; z-index:-1; }
    .icon-box:hover { transform:scale(1.12) rotate(-6deg); animation:iconFloat 2s ease-in-out infinite; }
    .icon-box:hover::after { opacity:1; }
    .icon-box.material { border-radius:14px; background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 4px 14px rgba(79,70,229,0.35); }
    .icon-box.material::after { background:rgba(79,70,229,0.12); }
    .icon-box.material:hover { box-shadow:0 6px 24px rgba(79,70,229,0.45); }
    .icon-box.quiz { border-radius:50%; background:linear-gradient(135deg,#d97706,#ea580c); box-shadow:0 4px 14px rgba(217,119,6,0.35); }
    .icon-box.quiz::after { background:rgba(217,119,6,0.12); }
    .icon-box.quiz:hover { box-shadow:0 6px 24px rgba(217,119,6,0.45); }
    .icon-box.session { border-radius:16px; }
    .icon-box.session.meet { background:linear-gradient(135deg,#059669,#10b981); box-shadow:0 4px 14px rgba(5,150,105,0.35); }
    .icon-box.session.classroom { background:linear-gradient(135deg,#0e7490,#22d3ee); box-shadow:0 4px 14px rgba(14,116,144,0.35); }
    .icon-box.session.video { background:linear-gradient(135deg,#dc2626,#f87171); box-shadow:0 4px 14px rgba(220,38,38,0.35); }
    .icon-box.session::after { background:rgba(0,0,0,0.08); }
    .icon-box.session:hover { box-shadow:0 6px 24px rgba(0,0,0,0.25); }

    .content-item { background:var(--white); border:1px solid var(--border-soft); border-radius:var(--radius-md); padding:18px 20px; margin-bottom:12px; transition:all 0.2s; }
    .content-item:hover { border-color:var(--blue-main); box-shadow:var(--shadow-md); }

    .tab-bar { display:flex; gap:8px; margin-bottom:20px; }
    .tab-btn { padding:10px 22px; border-radius:var(--radius-md); border:1.5px solid var(--border-soft); background:var(--white); color:var(--text-secondary); font-size:15px; font-weight:700; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px; }
    .tab-btn:hover { border-color:var(--blue-main); color:var(--blue-main); }
    .tab-btn.active { background:var(--blue-main); color:#fff; border-color:var(--blue-main); box-shadow:0 2px 8px rgba(74,127,247,0.25); }
    .tab-btn .tab-icon { font-size:18px; }

    .el-badge { display:inline-block; padding:3px 10px; border-radius:var(--radius-sm); font-size:11px; font-weight:700; }
    .el-badge-pass { background:#dcfce7; color:#15803d; }
    .el-badge-fail { background:#fecaca; color:#dc2626; }
    .el-badge-wait { background:#fef3c7; color:#92400e; }
    .el-badge-blocked { background:#ffe4e6; color:#be123c; }

    .session-tag { display:inline-block; padding:4px 12px; border-radius:var(--radius-sm); font-size:12px; font-weight:700; }
    .session-tag.meet { background:#dcfce7; color:#15803d; }
    .session-tag.classroom { background:#cffafe; color:#0e7490; }
    .session-tag.video { background:#ffe4e6; color:#be123c; }

    .q-box { background:var(--bg-secondary); border-radius:var(--radius-md); padding:18px; margin-bottom:14px; border:1px solid var(--border-soft); }
    .q-box h4 { margin:0 0 12px; font-size:15px; color:var(--text-primary); }
    .opt-label { display:flex; align-items:center; gap:10px; padding:12px 16px; margin:6px 0; border:1.5px solid var(--border-soft); border-radius:var(--radius-sm); cursor:pointer; transition:all 0.2s; font-size:14px; color:var(--text-primary); background:var(--white); }
    .opt-label:hover { background:var(--blue-50); border-color:var(--blue-main); }
    .opt-label input { margin:0; accent-color:var(--blue-main); width:16px; height:16px; }

    .empty-state { text-align:center; padding:56px 20px; color:var(--text-secondary); }
    .empty-state .icon { font-size:52px; margin-bottom:14px; }
</style>

<script>
    let sections = [];
    let currentSectionId = null;
    let currentQuiz = null;
    let timerInterval = null;
    let isSubmitting = false;

    const isEn = () => document.documentElement.lang === 'en';
    const gradeMap = {'الصف الأول':'Class 1','الصف الثاني':'Class 2','الصف الثالث':'Class 3','الصف الرابع':'Class 4','الصف الخامس':'Class 5'};
    const sectionMap = {'أ':'A','ب':'B','ج':'C','د':'D'};
    const tGrade = (name) => isEn() ? (gradeMap[name] || name) : name;
    const tSection = (letter) => isEn() ? (sectionMap[letter] || letter) : letter;
    const t = (key) => { try { return __(key); } catch(e) { return key; } };

    loadSections();

    function loadSections() {
        apiFetch('/student/elearning/sections').then(data => {
            sections = data;
            renderSections();
            document.getElementById('loadingSections').style.display = 'none';
            document.getElementById('sectionsGrid').style.display = 'grid';
        }).catch(e => {
            document.getElementById('loadingSections').innerHTML = '<div class="empty-state"><div class="icon">❌</div><p>' + (e.message || t('خطأ')) + '</p></div>';
        });
    }

    function renderSections() {
        const g = document.getElementById('sectionsGrid');
        if (!sections.length) {
            g.innerHTML = '<div style="grid-column:1/-1;" class="empty-state"><div class="icon">🏫</div><p>{{ __("لا أنت مسجل في أي صف بعد") }}</p></div>';
            return;
        }
        g.innerHTML = sections.map(s => `
            <div class="el-card" onclick="openSection(${s.id}, '${s.name} ${s.section}')">
                <div class="icon">🏫</div>
                <div class="name">${tGrade(s.name)}</div>
                <div class="section-tag">{{ __("شعبة") }} ${tSection(s.section)}</div>
            </div>
        `).join('');
    }

    function openSection(id, name) {
        currentSectionId = id;
        const s = sections.find(x => x.id === id);
        document.getElementById('sectionTitle').textContent = tGrade(s ? s.name : name) + ' — {{ __("شعبة") }} ' + tSection(s ? s.section : '');
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = '';
        loadContent();
    }

    function backToSections() {
        if (timerInterval) clearInterval(timerInterval);
        document.getElementById('quizModal').style.display = 'none';
        document.getElementById('step1').style.display = '';
        document.getElementById('step2').style.display = 'none';
        currentSectionId = null;
        loadSections();
    }

    function loadContent() {
        apiFetch(`/student/elearning/${currentSectionId}/content`).then(data => {
            renderMaterials(data.materials || []);
            renderQuizzes(data.quizzes || []);
            renderSessions(data.sessions || []);
        });
    }

    function switchTab(tab) {
        ['materials', 'quizzes', 'sessions'].forEach(t => {
            const panel = document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1));
            const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
            panel.style.display = t === tab ? '' : 'none';
            if (t === tab) { btn.classList.add('active'); }
            else { btn.classList.remove('active'); }
        });
    }

    function renderMaterials(materials) {
        const l = document.getElementById('materialsList');
        if (!materials.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📚</div><p>{{ __("لا توجد مواد بعد") }}</p></div>'; return; }
        l.innerHTML = materials.map(m => `
            <div class="content-item">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="icon-box material">📚</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:15px;margin-bottom:4px;color:var(--text-primary);">${m.title}</div>
                        ${m.description ? '<div style="color:var(--text-secondary);font-size:13px;margin-bottom:10px;line-height:1.6;">' + m.description + '</div>' : ''}
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            ${m.file_path ? '<a href="/storage/' + m.file_path + '" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);font-size:12px;font-weight:700;text-decoration:none;transition:all 0.15s;">📎 {{ __("تحميل الملف") }}</a>' : ''}
                            ${m.link ? '<a href="' + m.link + '" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:var(--radius-sm);background:#dcfce7;color:#15803d;font-size:12px;font-weight:700;text-decoration:none;transition:all 0.15s;">🔗 {{ __("فتح الرابط") }}</a>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderQuizzes(quizzes) {
        const l = document.getElementById('quizzesList');
        if (!quizzes.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📝</div><p>{{ __("لا توجد اختبارات بعد") }}</p></div>'; return; }
        l.innerHTML = quizzes.map(q => {
            const attempt = q.my_attempt;
            let badge = '';
            let clickAction = '';
            let extraBadge = '';
            const canDo = q.can_attempt !== false;
            const now = new Date();
            const scheduled = q.scheduled_at ? new Date(q.scheduled_at) : null;
            const scheduledEnd = q.scheduled_end ? new Date(q.scheduled_end) : null;
            const notStarted = scheduled && now < scheduled;
            const hasEnded = scheduledEnd && now > scheduledEnd;
            const effectiveCanDo = canDo && !notStarted && !hasEnded;

            if (attempt && attempt.completed_at) {
                const hasText = attempt.has_text_questions;
                const visible = attempt.visible;
                if (!visible) {
                    badge = hasText
                        ? '<span class="el-badge" style="background:#fef3c7;color:#92400e;">⏳ {{ __("بانتظار التصحيح") }}</span>'
                        : '<span class="el-badge" style="background:var(--blue-50);color:var(--blue-main);">📤 {{ __("تم التسجيل") }}</span>';
                    if (effectiveCanDo) {
                        clickAction = `openQuizInfo(${q.id})`;
                        extraBadge = '<span class="el-badge" style="background:#dcfce7;color:#15803d;">🔁 {{ __("محاولة جديدة") }}</span>';
                    } else {
                        clickAction = '';
                    }
                } else {
                    const pct = attempt.total_marks > 0 ? Math.round((attempt.score / attempt.total_marks) * 100) : 0;
                    badge = pct >= 50
                        ? '<span class="el-badge el-badge-pass">✅ {{ __("ناجح") }} ' + pct + '%</span>'
                        : '<span class="el-badge el-badge-fail">❌ {{ __("راسب") }} ' + pct + '%</span>';
                    clickAction = `viewAttemptResults(${attempt.id})`;
                    if (effectiveCanDo) extraBadge = '<span class="el-badge" style="background:#dcfce7;color:#15803d;cursor:pointer;" onclick="event.stopPropagation();openQuizInfo('+q.id+')">🔁 {{ __("محاولة جديدة") }}</span>';
                }
            } else if (attempt) {
                badge = '<span class="el-badge el-badge-wait">⏳ {{ __("معلقة") }}</span>';
                clickAction = `openQuizInfo(${q.id})`;
            } else {
                clickAction = effectiveCanDo ? `openQuizInfo(${q.id})` : '';
            }
            const attemptInfo = q.max_attempts ? ' · 🔁 ' + (q.completed_attempts || 0) + '/' + q.max_attempts : '';
            let scheduleInfo = '';
            if (notStarted) {
                scheduleInfo = '<div style="color:#92400e;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px;">🔒 {{ __("يبدأ") }}: ' + scheduled.toLocaleString(isEn()?'en':'ar') + '</div>';
            } else if (hasEnded) {
                scheduleInfo = '<div style="color:#dc2626;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px;">🔒 {{ __("انتهى الوقت") }}</div>';
            } else if (scheduled) {
                scheduleInfo = '<div style="color:#15803d;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px;">✅ {{ __("متاح الآن") }}</div>';
            } else if (!scheduled && !scheduledEnd) {
                scheduleInfo = '<div style="color:#15803d;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px;">✅ {{ __("متاح الآن") }}</div>';
            }
            return `
                <div class="content-item" onclick="${clickAction}" style="cursor:${clickAction?'pointer':'default'};">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                            <div class="icon-box quiz" style="${(notStarted||hasEnded)?'background:linear-gradient(135deg,#92400e,#f59e0b);box-shadow:0 4px 14px rgba(146,64,14,0.35);':''}">${(notStarted||hasEnded)?'🔒':'📝'}</div>
                            <div style="min-width:0;">
                                <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${q.title}</div>
                                <div style="color:var(--text-secondary);font-size:12px;margin-top:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">⏱️ ${q.time_limit || '{{ __("بلا حد") }}'} {{ __("دقيقة") }} · ❓ ${q.questions?.length || 0} {{ __("سؤال") }}${attemptInfo}</div>
                                ${scheduleInfo}
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">${badge}${extraBadge}${notStarted?'<span class="el-badge" style="background:#fef3c7;color:#92400e;">🔒 {{ __("قريباً") }}</span>':''}${hasEnded?'<span class="el-badge" style="background:#fecaca;color:#dc2626;">🔒 {{ __("انتهى") }}</span>':''}${!canDo&&!notStarted&&!hasEnded ? '<span class="el-badge el-badge-blocked">🚫 {{ __("انتهت المحاولات") }}</span>' : ''}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    let pendingQuizId = null;
    let pendingAttempt = null;

    function openQuizInfo(quizId) {
        pendingQuizId = quizId;
        pendingAttempt = null;
        apiFetch(`/student/elearning/quizzes/${quizId}/start`, { method: 'POST' }).then(data => {
            if (data.not_started) {
                const remaining = Math.abs(Math.round(data.time_remaining || 0));
                const h = Math.floor(remaining / 3600);
                const m = Math.floor((remaining % 3600) / 60);
                const s = remaining % 60;
                const timeStr = (h > 0 ? h + ' {{ __("ساعة") }} ' : '') + m + ' {{ __("دقيقة") }} ' + s + ' {{ __("ثانية") }}';
                showToast('🔒 {{ __("هذا الاختبار لم يبدأ بعد") }} — {{ __("متبقي") }}: ' + timeStr, 'error');
                return;
            }
            if (data.error) { showToast(data.message, 'error'); return; }
            if (data.expired) { showToast(data.message, 'error'); loadContent(); return; }
            if (data.ended) { showToast(data.message, 'error'); return; }
            currentQuiz = data.quiz;
            pendingAttempt = data.attempt;
            const q = currentQuiz;
            document.getElementById('quizInfoTitle').textContent = q.title;

            const questionCount = q.questions?.length || 0;
            let meta = '';
            meta += '<div style="display:flex;align-items:center;gap:8px;">⏱️ <strong>{{ __("الوقت") }}:</strong> <span>' + (q.time_limit || '{{ __("بلا حد") }}') + ' {{ __("دقيقة") }}</span></div>';
            if (q.scheduled_at) meta += '<div style="display:flex;align-items:center;gap:8px;">📅 <strong>{{ __("البداية") }}:</strong> <span>' + new Date(q.scheduled_at).toLocaleString(isEn()?'en':'ar') + '</span></div>';
            if (q.scheduled_end) meta += '<div style="display:flex;align-items:center;gap:8px;">📅 <strong>{{ __("النهاية") }}:</strong> <span>' + new Date(q.scheduled_end).toLocaleString(isEn()?'en':'ar') + '</span></div>';
            meta += '<div style="display:flex;align-items:center;gap:8px;">❓ <strong>{{ __("عدد الأسئلة") }}:</strong> <span>' + questionCount + '</span></div>';
            const total = (q.questions || []).reduce((s, qq) => s + (qq.marks || 1), 0);
            meta += '<div style="display:flex;align-items:center;gap:8px;">💯 <strong>{{ __("مجموع الدرجات") }}:</strong> <span>' + total + '</span></div>';
            if (data.max_attempts) meta += '<div style="display:flex;align-items:center;gap:8px;">🔁 <strong>{{ __("الحد الأقصى للمحاولات") }}:</strong> <span>' + data.completed_attempts + ' / ' + data.max_attempts + '</span></div>';
            else meta += '<div style="display:flex;align-items:center;gap:8px;">🔁 <strong>{{ __("المحاولات المستخدمة") }}:</strong> <span>' + data.completed_attempts + '</span></div>';
            if (data.resume) meta += '<div style="margin-top:8px;color:var(--blue-main);font-weight:700;font-size:14px;">↩️ {{ __("ستكمل المحاولة السابقة") }}</div>';
            document.getElementById('quizInfoMeta').innerHTML = meta;

            if (questionCount === 0) {
                document.getElementById('quizInfoBlocked').style.display = 'block';
                document.getElementById('quizInfoBlocked').textContent = '⚠️ {{ __("لا توجد أسئلة في هذا الاختبار") }}';
                document.getElementById('quizStartBtn').style.display = 'none';
            } else {
                document.getElementById('quizInfoBlocked').style.display = 'none';
                document.getElementById('quizStartBtn').style.display = '';
            }
            document.getElementById('quizInfoModal').style.display = 'flex';
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function closeQuizInfoModal() {
        document.getElementById('quizInfoModal').style.display = 'none';
        pendingQuizId = null;
        pendingAttempt = null;
    }

    function confirmStartQuiz() {
        const attempt = pendingAttempt;
        const quiz = currentQuiz;
        closeQuizInfoModal();
        if (!quiz || !attempt) return;
        currentQuiz = quiz;
        pendingAttempt = attempt;
        showQuizModal(attempt);
    }

    function startQuiz(quizId) { openQuizInfo(quizId); }

    function showQuizModal(attempt) {
        document.getElementById('quizModalTitle').textContent = '📝 ' + currentQuiz.title;
        let timeLeft = 0;
        if (currentQuiz.time_limit) {
            timeLeft = attempt.started_at
                ? Math.max(0, Math.floor(currentQuiz.time_limit * 60 - Math.floor((Date.now() - new Date(attempt.started_at).getTime()) / 1000)))
                : Math.floor(currentQuiz.time_limit * 60);
        }
        const timerEl = document.getElementById('quizTimer');
        if (timerInterval) clearInterval(timerInterval);
        function tick() {
            if (timeLeft <= 0) { clearInterval(timerInterval); timerEl.textContent = '⏱️ {{ __("انتهى الوقت!") }}'; timerEl.style.color = '#dc2626'; timerEl.style.background = '#fecaca'; submitQuiz(); return; }
            const h = Math.floor(timeLeft / 3600);
            const m = Math.floor((timeLeft % 3600) / 60);
            const s = timeLeft % 60;
            let timeStr = '';
            if (h > 0) timeStr = h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            else timeStr = m + ':' + String(s).padStart(2, '0');
            timerEl.textContent = '⏱️ ' + timeStr;
            if (timeLeft <= 60) { timerEl.style.color = '#dc2626'; timerEl.style.background = '#fecaca'; }
            else if (timeLeft <= 300) { timerEl.style.color = '#d97706'; timerEl.style.background = '#fef3c7'; }
            else { timerEl.style.color = ''; timerEl.style.background = ''; }
            timeLeft--;
        }
        tick(); timerInterval = setInterval(tick, 1000);

        const prev = {};
        if (attempt.answers && Array.isArray(attempt.answers)) attempt.answers.forEach(a => prev[a.question_id] = a.answer);

        const qEl = document.getElementById('quizQuestions');
        qEl.innerHTML = (currentQuiz.questions || []).map((q, i) => {
            const colors = ['#e0e7ff','#dcfce7','#fef3c7','#fce7f3','#e0f2fe','#f3e8ff'];
            const badgeColor = colors[i % colors.length];
            let html = '<div class="q-box"><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><span style="width:32px;height:32px;border-radius:var(--radius-sm);background:'+badgeColor+';display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:var(--text-primary);flex-shrink:0;">' + (i+1) + '</span><h4 style="margin:0;font-size:15px;color:var(--text-primary);">' + q.question + ' <small style="color:var(--text-secondary);font-weight:600;">(' + q.marks + ' {{ __("درجة") }})</small></h4></div>';
            if (q.type === 'mc') {
                html += (q.options || []).map((o, oi) => '<label class="opt-label"><input type="radio" name="q_'+q.id+'" value="'+oi+'" '+(prev[q.id]==oi?'checked':'')+'> <span style="width:26px;height:26px;border-radius:50%;border:1.5px solid var(--border-soft);display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;color:var(--text-secondary);">'+String.fromCharCode(65+oi)+'</span> '+o+'</label>').join('');
            } else if (q.type === 'tf') {
                html += '<label class="opt-label"><input type="radio" name="q_'+q.id+'" value="1" '+(prev[q.id]=='1'?'checked':'')+'> <span style="font-size:16px;">✅</span> {{ __("صح") }}</label>';
                html += '<label class="opt-label"><input type="radio" name="q_'+q.id+'" value="0" '+(prev[q.id]=='0'?'checked':'')+'> <span style="font-size:16px;">❌</span> {{ __("خطأ") }}</label>';
            } else {
                html += '<textarea name="q_'+q.id+'" rows="4" style="width:100%;padding:12px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;resize:vertical;transition:border-color 0.15s;font-family:inherit;color:var(--text-primary);background:var(--white);" placeholder="{{ __("اكتب إجابتك هنا...") }}" onfocus="this.style.borderColor=\'var(--blue-main)\'" onblur="this.style.borderColor=\'var(--border-soft)\'">'+(prev[q.id]||'')+'</textarea>';
            }
            return html + '</div>';
        }).join('');

        document.getElementById('submitQuizBtn').style.display = '';
        document.getElementById('quizModal').style.display = 'flex';
        document.getElementById('quizModal').attempt_id = attempt.id;
    }

    function closeQuizModal() {
        document.getElementById('quizModal').style.display = 'none';
        if (timerInterval) clearInterval(timerInterval);
    }

    function submitQuiz() {
        if (isSubmitting) return;
        isSubmitting = true;
        if (timerInterval) clearInterval(timerInterval);
        const answers = {};
        (currentQuiz.questions || []).forEach(q => {
            if (q.type === 'mc' || q.type === 'tf') {
                const s = document.querySelector('input[name="q_'+q.id+'"]:checked');
                answers[q.id] = s ? s.value : null;
            } else {
                const ta = document.querySelector('textarea[name="q_'+q.id+'"]');
                answers[q.id] = ta ? ta.value : null;
            }
        });
        apiFetch('/student/elearning/quizzes/'+currentQuiz.id+'/attempts/'+document.getElementById('quizModal').attempt_id+'/submit', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({answers})
        }).then(d => {
            isSubmitting = false;
            closeQuizModal();
            if (d.has_text_questions) {
                showToast('📤 {{ __("تم التسجيل — بانتظار مراجعة المعلم") }}', 'info');
            } else {
                showToast('📤 {{ __("تم التسجيل — النتيجة ستظهر بعد مراجعة المعلم") }}', 'info');
            }
            loadContent();
        }).catch(e => { isSubmitting = false; showToast(e.message || '❌', 'error'); });
    }

    function showQuizResult(data, overrideQuiz) {
        const quiz = overrideQuiz || currentQuiz;
        const questions = quiz.questions || [];
        const answers = data.answers || [];
        const pct = data.total_marks > 0 ? Math.round((data.score/data.total_marks)*100) : 0;

        let html = '<div style="text-align:center;margin-bottom:24px;padding:24px 0;">';
        html += '<div style="width:100px;height:100px;border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;background:conic-gradient('+(pct>=50?'#15803d':'#dc2626')+' '+pct+'%,var(--bg-secondary) '+pct+'%);position:relative;">';
        html += '<div style="width:80px;height:80px;border-radius:50%;background:var(--white);display:flex;align-items:center;justify-content:center;flex-direction:column;">';
        html += '<div style="font-size:22px;font-weight:900;color:'+(pct>=50?'#15803d':'#dc2626')+';">'+data.score+'<span style="font-size:14px;font-weight:600;color:var(--text-secondary);">/'+data.total_marks+'</span></div>';
        html += '<div style="font-size:11px;color:var(--text-secondary);font-weight:600;">'+pct+'%</div>';
        html += '</div></div>';
        html += '<div style="font-weight:700;font-size:15px;color:var(--text-primary);">'+(pct>=50?'🎉 {{ __("أحسنت! ناجح") }}':'⚠️ {{ __("يحتاج مراجعة") }}')+'</div>';
        html += '</div>';

        html += '<div style="display:flex;flex-direction:column;gap:14px;">';
        answers.forEach((ans, i) => {
            const q = questions.find(qq => qq.id === ans.question_id);
            if (!q) return;
            let borderColor = 'var(--border-soft)';
            let statusBg = '';
            if (ans.type !== 'text') {
                borderColor = ans.is_correct ? '#15803d' : '#dc2626';
                statusBg = ans.is_correct ? '#dcfce7' : '#fecaca';
            } else {
                borderColor = ans.teacher_marks != null ? (ans.teacher_marks > 0 ? '#15803d' : '#dc2626') : '#f59e0b';
                statusBg = ans.teacher_marks != null ? (ans.teacher_marks > 0 ? '#dcfce7' : '#fecaca') : '#fef3c7';
            }

            html += '<div style="border:1.5px solid '+borderColor+';border-radius:var(--radius-md);overflow:hidden;">';
            html += '<div style="background:'+statusBg+';padding:10px 16px;display:flex;align-items:center;justify-content:space-between;">';
            html += '<div style="font-weight:700;font-size:14px;color:var(--text-primary);display:flex;align-items:center;gap:8px;"><span style="width:26px;height:26px;border-radius:50%;background:var(--white);display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;">'+(i+1)+'</span> '+q.question+' <small style="font-weight:600;color:var(--text-secondary);">('+q.marks+' {{ __("درجة") }})</small></div>';
            html += '</div>';

            html += '<div style="padding:14px 16px;">';
            if (ans.type === 'mc' || ans.type === 'tf') {
                const options = q.options || [];
                let userAnswer = ans.answer;
                let correctAnswer = q.correct_answer;
                if (ans.type === 'tf') {
                    userAnswer = userAnswer == '1' ? '{{ __("صح") }}' : '{{ __("خطأ") }}';
                    correctAnswer = correctAnswer == '1' ? '{{ __("صح") }}' : '{{ __("خطأ") }}';
                } else {
                    userAnswer = userAnswer != null ? String.fromCharCode(65 + parseInt(userAnswer)) + '. ' + (options[parseInt(userAnswer)] || '') : '—';
                    correctAnswer = correctAnswer != null ? String.fromCharCode(65 + parseInt(correctAnswer)) + '. ' + (options[parseInt(correctAnswer)] || '') : '—';
                }
                const isCorrect = ans.is_correct;
                html += '<div style="display:flex;gap:20px;flex-wrap:wrap;">';
                html += '<div style="color:'+(isCorrect?'#15803d':'#dc2626')+';font-weight:600;font-size:14px;">{{ __("إجابتك") }}: '+userAnswer+'</div>';
                if (!isCorrect) html += '<div style="color:#15803d;font-weight:600;font-size:14px;">{{ __("الصحيحة") }}: '+correctAnswer+'</div>';
                html += '</div>';
            } else {
                html += '<div style="background:var(--bg-secondary);border:1px solid var(--border-soft);border-radius:var(--radius-sm);padding:12px;margin-bottom:8px;white-space:pre-wrap;font-size:14px;color:var(--text-primary);line-height:1.7;">{{ __("إجابتك") }}: '+(ans.answer || '—')+'</div>';
                if (ans.teacher_marks != null) {
                    html += '<div style="color:'+(ans.teacher_marks>0?'#15803d':'#dc2626')+';font-weight:700;font-size:14px;">{{ __("علامة المعلم") }}: '+ans.teacher_marks+' / '+q.marks+'</div>';
                } else {
                    html += '<div style="color:#d97706;font-weight:700;font-size:14px;">⏳ {{ __("بانتظار تصحيح المعلم") }}</div>';
                }
            }
            html += '</div></div>';
        });
        html += '</div>';

        document.getElementById('resultQuizBody').innerHTML = html;
        document.getElementById('resultQuizModal').style.display = 'flex';
    }

    function closeResultQuizModal() {
        document.getElementById('resultQuizModal').style.display = 'none';
    }

    function viewAttemptResults(attemptId) {
        apiFetch('/student/elearning/attempts/' + attemptId).then(d => {
            if (d.error) { showToast(d.message, 'error'); return; }
            const resultQuiz = { id: d.quiz_id, title: d.quiz_title, questions: d.questions };
            showQuizResult({
                score: d.score,
                total_marks: d.total_marks,
                answers: d.answers,
                has_text_questions: d.has_text_questions,
                visible: d.visible,
            }, resultQuiz);
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function renderSessions(sessions) {
        const l = document.getElementById('sessionsList');
        if (!sessions.length) { l.innerHTML = '<div class="empty-state"><div class="icon">🎥</div><p>{{ __("لا توجد حصص بعد") }}</p></div>'; return; }
        const labels = { meet:'Google Meet', classroom:'Google Classroom', video:'🎬 {{ __("فيديو مسجل") }}' };
        const icons = { meet:'🟢', classroom:'🎓', video:'🎬' };
        const locale = isEn() ? 'en' : 'ar';
        l.innerHTML = sessions.map(s => `
            <div class="content-item">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div class="icon-box session ${s.type}">🎥</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:15px;margin-bottom:6px;color:var(--text-primary);">${s.title}</div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                            <span class="session-tag ${s.type}">${labels[s.type]||s.type}</span>
                            ${s.scheduled_at ? '<span style="color:var(--text-secondary);font-size:12px;">📅 '+new Date(s.scheduled_at).toLocaleString(locale)+'</span>' : ''}
                        </div>
                        <a href="${s.url}" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);font-size:12px;font-weight:700;text-decoration:none;transition:all 0.15s;">🔗 {{ __("دخول الحصة") }}</a>
                    </div>
                </div>
            </div>
        `).join('');
    }
</script>
@stop
