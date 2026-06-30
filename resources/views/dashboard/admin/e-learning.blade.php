@extends('layouts.dashboard')

@section('title', __('التعلم الإلكتروني'))
@section('page-title', __('التعلم الإلكتروني - الإدارة'))

@section('sidebar')
    <a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>
    <a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>
    <a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>
    <a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>
    <a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>
    <a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>
    <a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>
    <a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>
    <a href="/admin/e-learning" class="active">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/admin/library">📖 <span>{{ __('المكتبة') }}</span></a>
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
<div id="dashboardView">

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0;margin-bottom:24px;border:1px solid var(--border-soft);border-radius:var(--radius-md);overflow:hidden;background:var(--white);">
        <div style="padding:20px 12px;text-align:center;border-inline-end:1px solid var(--border-soft);">
            <div style="font-size:26px;margin-bottom:6px;">👨‍🏫</div>
            <div style="font-size:24px;font-weight:800;color:var(--blue-main);" id="statTeachers">-</div>
            <div style="color:var(--text-secondary);font-size:12px;font-weight:600;">{{ __('معلم') }}</div>
        </div>
        <div style="padding:20px 12px;text-align:center;border-inline-end:1px solid var(--border-soft);">
            <div style="font-size:26px;margin-bottom:6px;">🏫</div>
            <div style="font-size:24px;font-weight:800;color:#059669;" id="statSections">-</div>
            <div style="color:var(--text-secondary);font-size:12px;font-weight:600;">{{ __('صف') }}</div>
        </div>
        <div style="padding:20px 12px;text-align:center;border-inline-end:1px solid var(--border-soft);">
            <div style="font-size:26px;margin-bottom:6px;">📚</div>
            <div style="font-size:24px;font-weight:800;color:var(--blue-main);" id="statMaterials">-</div>
            <div style="color:var(--text-secondary);font-size:12px;font-weight:600;">{{ __('مادة') }}</div>
        </div>
        <div style="padding:20px 12px;text-align:center;border-inline-end:1px solid var(--border-soft);">
            <div style="font-size:26px;margin-bottom:6px;">📝</div>
            <div style="font-size:24px;font-weight:800;color:#d97706;" id="statQuizzes">-</div>
            <div style="color:var(--text-secondary);font-size:12px;font-weight:600;">{{ __('اختبار') }}</div>
        </div>
        <div style="padding:20px 12px;text-align:center;">
            <div style="font-size:26px;margin-bottom:6px;">👥</div>
            <div style="font-size:24px;font-weight:800;color:#6c5ce7;" id="statAttempts">-</div>
            <div style="color:var(--text-secondary);font-size:12px;font-weight:600;">{{ __('محاولة') }}</div>
        </div>
    </div>

    <div style="display:flex;gap:6px;margin-bottom:24px;background:var(--bg-secondary);border-radius:var(--radius-md);padding:4px;">
        <button class="tab-btn active" onclick="switchDashboardTab('teachers')" id="dashTabTeachers" style="flex:1;padding:12px 16px;border:none;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:700;background:var(--white);color:var(--blue-main);box-shadow:var(--shadow-sm);transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;">👨‍🏫 {{ __('المعلمون') }}</button>
        <button class="tab-btn" onclick="switchDashboardTab('sections')" id="dashTabSections" style="flex:1;padding:12px 16px;border:none;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:700;background:transparent;color:var(--text-secondary);transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;">🏫 {{ __('الصفوف') }}</button>
    </div>

    <div id="dashTeachersPanel">
        <div class="loading" id="loadingTeachers">{{ __('جاري التحميل...') }}</div>
        <div id="teachersGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;"></div>
    </div>

    <div id="dashSectionsPanel" style="display:none;">
        <div id="sectionsGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;"></div>
    </div>

    <!-- Teacher Detail Modal -->
    <div id="teacherDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:700px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:adminModalIn .25s ease;">
            <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">👨‍🏫</div>
                    <h3 id="teacherDetailTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);"></h3>
                </div>
                <button onclick="document.getElementById('teacherDetailModal').style.display='none'" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
            <div style="padding:20px 28px 24px;">
                <div id="teacherDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<div id="sectionView" style="display:none;">
    <div class="header">
        <div style="display:flex;align-items:center;gap:14px;">
            <button onclick="backToDashboard()" style="width:42px;height:42px;border-radius:var(--radius-sm);border:1px solid var(--border-soft);background:var(--white);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;transition:all 0.2s;box-shadow:var(--shadow-sm);">⬅️</button>
            <h1 id="sectionTitle" style="margin:0;"></h1>
        </div>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('materials')" id="tabMaterials"><span class="tab-icon">📚</span> {{ __('المواد') }}</button>
        <button class="tab-btn" onclick="switchTab('quizzes')" id="tabQuizzes"><span class="tab-icon">📝</span> {{ __('الاختبارات') }}</button>
        <button class="tab-btn" onclick="switchTab('sessions')" id="tabSessions"><span class="tab-icon">🎥</span> {{ __('الحصص') }}</button>
    </div>

    <div id="panelMaterials" class="tab-panel">
        <div class="section-title">
            <div class="section-icon mat">📚</div>
            <h4>{{ __('المواد التعليمية') }}</h4>
        </div>
        <div id="materialsList"></div>
    </div>

    <div id="panelQuizzes" class="tab-panel" style="display:none;">
        <div class="section-title">
            <div class="section-icon quiz">📝</div>
            <h4>{{ __('الاختبارات') }}</h4>
        </div>
        <div id="quizzesList"></div>
    </div>

    <div id="panelSessions" class="tab-panel" style="display:none;">
        <div class="section-title">
            <div class="section-icon sess">🎥</div>
            <h4>{{ __('الحصص الإلكترونية') }}</h4>
        </div>
        <div id="sessionsList"></div>
    </div>
</div>

<!-- Edit Material Modal -->
<div id="editMaterialModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:adminModalIn .25s ease;">
        <div style="padding:24px 28px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">✏️</div>
                    <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('تعديل المادة') }}</h3>
                </div>
                <button onclick="document.getElementById('editMaterialModal').style.display='none'" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
        </div>
        <div style="padding:0 28px 24px;">
            <input type="hidden" id="matEditId">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }}</label><input id="matTitle" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="matDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;"></textarea></div>
            <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }}</label><input id="matLink" dir="ltr" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <button onclick="saveMaterial()" class="btn btn-primary" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
        </div>
    </div>
</div>

<!-- Edit Quiz Modal -->
<div id="editQuizModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:adminModalIn .25s ease;">
        <div style="padding:24px 28px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,#d97706,#a29bfe);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">✏️</div>
                    <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('تعديل الاختبار') }}</h3>
                </div>
                <button onclick="document.getElementById('editQuizModal').style.display='none'" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
        </div>
        <div style="padding:0 28px 24px;">
            <input type="hidden" id="quizEditId">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }}</label><input id="quizTitle" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="quizDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوقت (دقيقة)') }}</label><input id="quizTime" type="number" min="1" step="1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الحد الأقصى للمحاولات') }}</label><input id="quizMaxAttempts" type="number" min="1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد البداية') }}</label><input id="quizScheduledAt" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد النهاية') }}</label><input id="quizScheduledEnd" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <button onclick="saveQuiz()" class="btn btn-primary" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
        </div>
    </div>
</div>

<!-- Edit Session Modal -->
<div id="editSessionModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:adminModalIn .25s ease;">
        <div style="padding:24px 28px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">✏️</div>
                    <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('تعديل الحصة') }}</h3>
                </div>
                <button onclick="document.getElementById('editSessionModal').style.display='none'" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
        </div>
        <div style="padding:0 28px 24px;">
            <input type="hidden" id="sessionEditId">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }}</label><input id="sessionTitle" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('النوع') }}</label><select id="sessionType" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="meet">Google Meet</option><option value="classroom">Google Classroom</option><option value="video">{{ __('فيديو') }}</option></select></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('التاريخ') }}</label><input id="sessionDate" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }}</label><input id="sessionUrl" dir="ltr" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <button onclick="saveSession()" class="btn btn-primary" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
        </div>
    </div>
</div>

<!-- Questions Modal (view only) -->
<div id="questionsModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:700px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:adminModalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">❓</div>
                <h3 id="questionsTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('الأسئلة') }}</h3>
            </div>
            <button onclick="closeQuestionsModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
        </div>
        <div style="padding:20px 28px 24px;">
            <div id="questionsList"></div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:700px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:adminModalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">📋</div>
                <h3 id="reviewTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">📋 {{ __('نتائج الاختبار') }}</h3>
            </div>
            <button onclick="closeReviewModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
        </div>
        <div style="padding:20px 28px 24px;">
            <div id="reviewAttemptsList"></div>
        </div>
    </div>
</div>

<style>
    @keyframes adminModalIn { from { opacity:0; transform:translateY(16px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    @keyframes iconFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-3px)} }

    .el-card { background:var(--white); border-radius:var(--radius-lg); padding:24px; cursor:pointer; transition:all 0.25s; border:2px solid var(--border-soft); position:relative; overflow:hidden; }
    .el-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:var(--blue-main); }
    .el-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; background:linear-gradient(135deg,rgba(74,127,247,0.06),transparent); border-radius:0 0 0 80px; }
    .el-card .icon { width:56px; height:56px; border-radius:var(--radius-lg); background:linear-gradient(135deg,var(--blue-main),#6c5ce7); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:#fff; box-shadow:0 4px 14px rgba(74,127,247,0.3); }
    .el-card .name { font-weight:800; font-size:16px; margin-bottom:6px; text-align:center; color:var(--text-primary); }
    .el-card .section-tag { display:inline-block; padding:4px 14px; border-radius:var(--radius-sm); font-size:12px; font-weight:700; background:var(--blue-50); color:var(--blue-main); }
    .el-card .info { color:var(--text-secondary); font-size:12px; text-align:center; margin-top:8px; }

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

    .content-item { background:var(--white); border:1px solid var(--border-soft); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; transition:all 0.2s; gap:14px; }
    .content-item:hover { border-color:var(--blue-main); box-shadow:var(--shadow-md); }

    .tab-bar { display:flex; gap:8px; margin-bottom:20px; }
    .tab-btn { padding:10px 22px; border-radius:var(--radius-md); border:1.5px solid var(--border-soft); background:var(--white); color:var(--text-secondary); font-size:15px; font-weight:700; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:8px; }
    .tab-btn:hover { border-color:var(--blue-main); color:var(--blue-main); }
    .tab-btn.active { background:var(--blue-main); color:#fff; border-color:var(--blue-main); box-shadow:0 2px 8px rgba(74,127,247,0.25); }
    .tab-btn .tab-icon { font-size:18px; }

    .section-title { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
    .section-title .section-icon { width:36px; height:36px; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; font-size:18px; color:#fff; flex-shrink:0; }
    .section-title .section-icon.mat { background:linear-gradient(135deg,#4f46e5,#7c3aed); }
    .section-title .section-icon.quiz { background:linear-gradient(135deg,#d97706,#ea580c); }
    .section-title .section-icon.sess { background:linear-gradient(135deg,#059669,#10b981); }
    .section-title h4 { margin:0; font-size:18px; font-weight:800; color:var(--text-primary); }

    .session-tag { display:inline-block; padding:4px 12px; border-radius:var(--radius-sm); font-size:12px; font-weight:700; }
    .session-tag.meet { background:#dcfce7; color:#15803d; }
    .session-tag.classroom { background:#cffafe; color:#0e7490; }
    .session-tag.video { background:#ffe4e6; color:#be123c; }

    .el-badge { display:inline-block; padding:3px 10px; border-radius:var(--radius-sm); font-size:11px; font-weight:700; }
    .empty-state { text-align:center; padding:56px 20px; color:var(--text-secondary); }
    .empty-state .icon { font-size:52px; margin-bottom:14px; }
</style>

<script>
    const isEn = () => document.documentElement.lang === 'en';
    const gradeMap = {'الصف الأول':'Class 1','الصف الثاني':'Class 2','الصف الثالث':'Class 3','الصف الرابع':'Class 4','الصف الخامس':'Class 5'};
    const sectionMap = {'أ':'A','ب':'B','ج':'C','د':'D'};
    const tGrade = (name) => isEn() ? (gradeMap[name] || name) : name;
    const tSection = (letter) => isEn() ? (sectionMap[letter] || letter) : letter;

    let currentSectionId = null;
    let currentQuizId = null;
    let currentReviewQuiz = null;
    let currentReviewAttempts = [];
    let currentDashTab = 'teachers';

    loadDashboard();

    function switchDashboardTab(tab) {
        currentDashTab = tab;
        document.getElementById('dashTeachersPanel').style.display = tab === 'teachers' ? '' : 'none';
        document.getElementById('dashSectionsPanel').style.display = tab === 'sections' ? '' : 'none';
        const tBtn = document.getElementById('dashTabTeachers');
        const sBtn = document.getElementById('dashTabSections');
        if (tab === 'teachers') {
            tBtn.style.background = 'var(--white)'; tBtn.style.color = 'var(--blue-main)'; tBtn.style.boxShadow = 'var(--shadow-sm)';
            sBtn.style.background = 'transparent'; sBtn.style.color = 'var(--text-secondary)'; sBtn.style.boxShadow = 'none';
        } else {
            sBtn.style.background = 'var(--white)'; sBtn.style.color = 'var(--blue-main)'; sBtn.style.boxShadow = 'var(--shadow-sm)';
            tBtn.style.background = 'transparent'; tBtn.style.color = 'var(--text-secondary)'; tBtn.style.boxShadow = 'none';
        }
    }

    // --- Dashboard ---
    function loadDashboard() {
        Promise.all([
            apiFetch('/admin/elearning/dashboard'),
            apiFetch('/admin/elearning/sections'),
        ]).then(([dash, sections]) => {
            document.getElementById('statTeachers').textContent = dash.stats.teachersCount;
            document.getElementById('statSections').textContent = dash.stats.sectionsCount;
            document.getElementById('statMaterials').textContent = dash.stats.materialsCount;
            document.getElementById('statQuizzes').textContent = dash.stats.quizzesCount;
            document.getElementById('statAttempts').textContent = dash.stats.attemptsCount;

            const tg = document.getElementById('teachersGrid');
            tg.style.display = 'grid';
            document.getElementById('loadingTeachers').style.display = 'none';

            const teachers = dash.teachers || [];
            if (!teachers.length) {
                tg.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:32px;color:#bbb;"><div style="font-size:40px;">👨‍🏫</div><p>{{ __("لا يوجد معلمون") }}</p></div>';
            } else {
                tg.innerHTML = teachers.map(t => {
                    const colors = ['var(--blue-main),#6c5ce7','#059669,#10b981','#d97706,#a29bfe','#dc2626,#f87171','#0e7490,#22d3ee'];
                    const ci = t.id % colors.length;
const subjectText = t.subjects.length ? t.subjects.join(' · ') : '{{ __("—") }}';
                    return `
                    <div onclick="openTeacherDetail(${t.id})" style="background:var(--white);border-radius:var(--radius-md);padding:20px;box-shadow:var(--shadow-sm);cursor:pointer;transition:all 0.2s;border:2px solid var(--border-soft);" onmouseover="this.style.borderColor='var(--blue-main)';this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.borderColor='var(--border-soft)';this.style.boxShadow='var(--shadow-sm)'">
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                            <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,${colors[ci]});display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;font-weight:800;box-shadow:0 3px 10px rgba(0,0,0,0.1);flex-shrink:0;">${t.name.charAt(0)}</div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:800;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${t.name}</div>
                                <div style="font-size:11px;color:var(--text-secondary);margin-top:3px;">${subjectText}</div>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center;">
                            <div style="background:var(--blue-50);border-radius:var(--radius-sm);padding:10px 8px;">
                                <div style="font-size:18px;font-weight:800;color:var(--blue-main);">${t.materials}</div>
                                <div style="font-size:10px;color:var(--text-secondary);">📚 {{ __('مادة') }}</div>
                            </div>
                            <div style="background:#f3e8ff;border-radius:var(--radius-sm);padding:10px 8px;">
                                <div style="font-size:18px;font-weight:800;color:#7c3aed;">${t.quizzes}</div>
                                <div style="font-size:10px;color:var(--text-secondary);">📝 {{ __('اختبار') }}</div>
                            </div>
                            <div style="background:#dcfce7;border-radius:var(--radius-sm);padding:10px 8px;">
                                <div style="font-size:18px;font-weight:800;color:#059669;">${t.sessions}</div>
                                <div style="font-size:10px;color:var(--text-secondary);">🎥 {{ __('حصة') }}</div>
                            </div>
                        </div>
                        ${t.attempts > 0 ? '<div style="margin-top:12px;text-align:center;font-size:11px;color:var(--text-secondary);">👥 '+t.attempts+' {{ __("محاولة طالب") }}</div>' : ''}
                    </div>`;
                }).join('');
            }

            const sg = document.getElementById('sectionsGrid');
            sg.style.display = 'grid';
            if (!sections.length) {
                sg.innerHTML = '<div style="grid-column:1/-1;" class="empty-state"><div class="icon">🏫</div><p>{{ __("لا توجد صفوف") }}</p></div>';
            } else {
                sg.innerHTML = sections.map(s => `
                    <div class="el-card" onclick="openSection(${s.id}, '${s.name}', '${s.section}')">
                        <div class="icon">🏫</div>
                        <div class="name">${tGrade(s.name)} — {{ __("شعبة") }} ${tSection(s.section)}</div>
                        <div class="info">📚 ${s.materials_count} · 📝 ${s.quizzes_count} · 🎥 ${s.sessions_count}</div>
                    </div>
                `).join('');
            }

            if (currentDashTab === 'sections') switchDashboardTab('sections');
        }).catch(e => {
            document.getElementById('loadingTeachers').innerHTML = '<div style="text-align:center;padding:24px;color:#bbb;"><div style="font-size:40px;">❌</div><p>' + (e.message || 'خطأ') + '</p></div>';
        });
    }

    function openTeacherDetail(teacherId) {
        apiFetch('/admin/elearning/dashboard').then(dash => {
            const t = dash.teachers.find(x => x.id === teacherId);
            if (!t) return;
            const subjectText = t.subjects.length ? t.subjects.join(' · ') : '{{ __("—") }}';
            document.getElementById('teacherDetailTitle').innerHTML = '👨‍🏫 ' + t.name + ' <small style="color:#888;font-size:12px;">(' + subjectText + ')</small>';

            let html = '';
            if (!t.sections.length) {
                html = '<div style="text-align:center;padding:40px 20px;color:var(--text-secondary);"><div style="font-size:52px;margin-bottom:14px;">📭</div><p>{{ __("لم يقم بإنشاء أي محتوى بعد") }}</p></div>';
            } else {
                html = t.sections.map(s => `
                    <div style="border:1.5px solid var(--border-soft);border-radius:var(--radius-md);padding:18px;margin-bottom:12px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--blue-main)'" onmouseout="this.style.borderColor='var(--border-soft)'">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);">🏫 ${tGrade(s.name)} — {{ __("شعبة") }} ${tSection(s.section)}</div>
                            <button onclick="document.getElementById('teacherDetailModal').style.display='none';openSection(${s.id}, '${s.name}', '${s.section}')" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:6px 12px;border-radius:var(--radius-sm);font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">📂 {{ __('إدارة') }}</button>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            ${s.materials > 0 ? '<span style="background:var(--blue-50);color:var(--blue-main);padding:4px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;">📚 '+s.materials+' {{ __("مادة") }}</span>' : ''}
                            ${s.quizzes > 0 ? '<span style="background:#f3e8ff;color:#7c3aed;padding:4px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;">📝 '+s.quizzes+' {{ __("اختبار") }}</span>' : ''}
                            ${s.sessions > 0 ? '<span style="background:#dcfce7;color:#059669;padding:4px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;">🎥 '+s.sessions+' {{ __("حصة") }}</span>' : ''}
                            ${s.materials === 0 && s.quizzes === 0 && s.sessions === 0 ? '<span style="color:var(--text-secondary);font-size:12px;">{{ __("لا يوجد محتوى") }}</span>' : ''}
                        </div>
                    </div>
                `).join('');
            }

            document.getElementById('teacherDetailContent').innerHTML = html;
            document.getElementById('teacherDetailModal').style.display = 'flex';
        });
    }

    function backToDashboard() {
        document.getElementById('dashboardView').style.display = '';
        document.getElementById('sectionView').style.display = 'none';
        currentSectionId = null;
        loadDashboard();
    }

    // --- Section ---
    function openSection(id, name, section) {
        currentSectionId = id;
        document.getElementById('sectionTitle').textContent = tGrade(name) + ' — {{ __("شعبة") }} ' + tSection(section);
        document.getElementById('dashboardView').style.display = 'none';
        document.getElementById('sectionView').style.display = '';
        loadMaterials();
        loadQuizzes();
        loadSessions();
    }

    function switchTab(tab) {
        ['materials', 'quizzes', 'sessions'].forEach(t => {
            document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1)).style.display = t === tab ? '' : 'none';
            const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
            if (t === tab) { btn.classList.add('active'); }
            else { btn.classList.remove('active'); }
        });
    }

    // --- Materials ---
    function loadMaterials() {
        apiFetch(`/admin/elearning/${currentSectionId}/materials`).then(data => {
            const l = document.getElementById('materialsList');
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📚</div><p>{{ __("لا توجد مواد") }}</p></div>'; return; }
            l.innerHTML = data.map(m => `
                <div class="content-item">
                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div class="icon-box material">📚</div>
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${m.title}</div>
                            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">${m.teacher?.user?.name || '{{ __("الإدارة") }}'} ${m.description ? '· ' + m.description.substring(0, 50) : ''}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        ${m.link ? '<a href="'+m.link+'" target="_blank" style="padding:6px 10px;border-radius:var(--radius-sm);background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;text-decoration:none;">🔗</a>' : ''}
                        <button onclick='editMaterial(${JSON.stringify(m).replace(/'/g, "&#39;")})' style="padding:6px 10px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">✏️</button>
                        <button onclick="deleteMaterial(${m.id})" style="padding:6px 10px;border-radius:var(--radius-sm);background:#fecaca;color:#dc2626;border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">🗑️</button>
                    </div>
                </div>
            `).join('');
        });
    }

    function editMaterial(m) {
        document.getElementById('matTitle').value = m.title;
        document.getElementById('matDesc').value = m.description || '';
        document.getElementById('matLink').value = m.link || '';
        document.getElementById('matEditId').value = m.id;
        document.getElementById('editMaterialModal').style.display = 'flex';
    }

    function saveMaterial() {
        const editId = document.getElementById('matEditId').value;
        const body = { title: document.getElementById('matTitle').value, description: document.getElementById('matDesc').value, link: document.getElementById('matLink').value || null };
        apiFetch(`/admin/elearning/materials/${editId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
            .then(d => { showToast(d.message); document.getElementById('editMaterialModal').style.display = 'none'; loadMaterials(); });
    }

    function deleteMaterial(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف المادة؟") }}')) return;
        apiFetch(`/admin/elearning/materials/${id}`, { method: 'DELETE' }).then(d => { showToast(d.message); loadMaterials(); });
    }

    // --- Quizzes ---
    function loadQuizzes() {
        apiFetch(`/admin/elearning/${currentSectionId}/quizzes`).then(data => {
            const l = document.getElementById('quizzesList');
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📝</div><p>{{ __("لا توجد اختبارات") }}</p></div>'; return; }
            l.innerHTML = data.map(q => `
                <div class="content-item">
                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div class="icon-box quiz">📝</div>
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${q.title}</div>
                            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">⏱️ ${q.time_limit || '{{ __("بلا حد") }}'} {{ __("دقيقة") }} · ❓ ${q.questions_count || q.questions?.length || 0} {{ __("سؤال") }} · 👥 ${q.attempts_count || 0} {{ __("محاولة") }}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
                        <button onclick="openQuestionsModal(${q.id}, '${q.title.replace(/'/g, "\\'")}')" style="padding:6px 10px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">❓ {{ __("أسئلة") }}</button>
                        <button onclick="openReviewModal(${q.id}, '${q.title.replace(/'/g, "\\'")}')" style="padding:6px 10px;border-radius:var(--radius-sm);background:#dcfce7;color:#15803d;border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">📋 {{ __("نتائج") }}</button>
                        <button onclick="editQuiz(${q.id})" style="padding:6px 10px;border-radius:var(--radius-sm);background:#fef3c7;color:#92400e;border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">✏️</button>
                        <button onclick="deleteQuiz(${q.id})" style="padding:6px 10px;border-radius:var(--radius-sm);background:#fecaca;color:#dc2626;border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">🗑️</button>
                    </div>
                </div>
            `).join('');
        });
    }

    function editQuiz(id) {
        apiFetch(`/admin/elearning/${currentSectionId}/quizzes`).then(data => {
            const q = data.find(x => x.id === id);
            if (!q) return;
            document.getElementById('quizTitle').value = q.title;
            document.getElementById('quizDesc').value = q.description || '';
            document.getElementById('quizTime').value = q.time_limit || '';
            document.getElementById('quizMaxAttempts').value = q.max_attempts || '';
            document.getElementById('quizScheduledAt').value = q.scheduled_at ? q.scheduled_at.slice(0, 16) : '';
            document.getElementById('quizScheduledEnd').value = q.scheduled_end ? q.scheduled_end.slice(0, 16) : '';
            document.getElementById('quizEditId').value = q.id;
            document.getElementById('editQuizModal').style.display = 'flex';
        });
    }

    function saveQuiz() {
        const editId = document.getElementById('quizEditId').value;
        const body = { title: document.getElementById('quizTitle').value, description: document.getElementById('quizDesc').value, time_limit: document.getElementById('quizTime').value || null, max_attempts: document.getElementById('quizMaxAttempts').value || null, scheduled_at: document.getElementById('quizScheduledAt').value || null, scheduled_end: document.getElementById('quizScheduledEnd').value || null };
        apiFetch(`/admin/elearning/quizzes/${editId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
            .then(d => { showToast(d.message); document.getElementById('editQuizModal').style.display = 'none'; loadQuizzes(); });
    }

    function deleteQuiz(id) {
        if (!confirm('{{ __("هل أنت متأكد؟ سيتم حذف جميع الأسئلة والمحاولات.") }}')) return;
        apiFetch(`/admin/elearning/quizzes/${id}`, { method: 'DELETE' }).then(d => { showToast(d.message); loadQuizzes(); });
    }

    // --- Questions ---
    function openQuestionsModal(quizId, title) {
        currentQuizId = quizId;
        document.getElementById('questionsTitle').textContent = '❓ ' + title;
        loadQuestions();
        document.getElementById('questionsModal').style.display = 'flex';
    }

    function closeQuestionsModal() { document.getElementById('questionsModal').style.display = 'none'; }

    function loadQuestions() {
        apiFetch(`/admin/elearning/quizzes/${currentQuizId}/questions`).then(data => {
            const l = document.getElementById('questionsList');
            if (!data.length) { l.innerHTML = '<div style="text-align:center;padding:16px;color:#bbb;">{{ __("لا توجد أسئلة") }}</div>'; return; }
            l.innerHTML = data.map((q, i) => {
                const typeLabel = q.type === 'mc' ? '🔘 {{ __("اختيار من متعدد") }}' : (q.type === 'tf' ? '✅ {{ __("صح/خطأ") }}' : '📝 {{ __("سؤال حر") }}');
                let answerInfo = '';
                if (q.type === 'mc' && q.options) {
                    answerInfo = q.options.map((o, oi) => '<span style="display:inline-block;margin:2px;padding:3px 10px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;background:'+(oi==q.correct_answer?'#dcfce7;':'var(--bg-secondary);')+'color:'+(oi==q.correct_answer?'#15803d':'var(--text-secondary)')+'">'+String.fromCharCode(65+oi)+'. '+o+'</span>').join('');
                } else if (q.type === 'tf') answerInfo = q.correct_answer === '1' ? '✅ {{ __("صح") }}' : '❌ {{ __("خطأ") }}';
                else answerInfo = '<em style="color:var(--text-secondary);">{{ __("إجابة حرة") }}</em>';
                return `<div class="content-item"><div style="flex:1;"><div style="font-weight:700;font-size:14px;color:var(--text-primary);">${i+1}. ${q.question}</div><div style="font-size:12px;color:var(--text-secondary);margin-top:4px;">${typeLabel} · ${q.marks} {{ __("درجة") }}</div><div style="margin-top:6px;">${answerInfo}</div></div></div>`;
            }).join('');
        });
    }

    // --- Review ---
    function openReviewModal(quizId, title) {
        currentReviewQuiz = { id: quizId, title: title };
        document.getElementById('reviewTitle').textContent = '📋 ' + title;
        apiFetch(`/admin/elearning/quizzes/${quizId}/attempts`).then(data => {
            currentReviewAttempts = data.attempts || [];
            renderStudentsList();
            document.getElementById('reviewModal').style.display = 'flex';
        });
    }

    function closeReviewModal() { document.getElementById('reviewModal').style.display = 'none'; }

    function renderStudentsList() {
        const l = document.getElementById('reviewAttemptsList');
        if (!currentReviewAttempts.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📋</div><p>{{ __("لا توجد محاولات") }}</p></div>'; return; }

        let html = '<div style="display:flex;flex-direction:column;gap:10px;">';
        currentReviewAttempts.forEach(st => {
            const pct = st.best_total > 0 ? Math.round((st.best_score / st.best_total) * 100) : 0;
            const statusColor = st.best_visible ? '#059669' : (st.best_has_text ? '#d97706' : '#6b7280');
            const statusText = st.best_visible ? '{{ __("تم العرض") }}' : (st.best_has_text ? '{{ __("بانتظار التصحيح") }}' : '{{ __("جاهز") }}');

            html += `<div style="border:1.5px solid var(--border-soft);border-radius:var(--radius-md);background:var(--white);overflow:hidden;transition:all 0.2s;">
                <div onclick="toggleStudentAttempts(${st.student_id})" style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer;transition:all 0.15s;" onmouseover="this.parentElement.style.borderColor='var(--blue-main)'" onmouseout="this.parentElement.style.borderColor='var(--border-soft)'">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;">👤</div>
                        <div>
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);">${st.student_name}</div>
                            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">${st.student_number} ${st.attempts.length > 1 ? '· ×' + st.attempts.length + ' {{ __("محاولات") }}' : ''}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="padding:4px 10px;border-radius:var(--radius-sm);font-size:11px;font-weight:700;background:'+statusColor+'15;color:'+statusColor+';">${statusText}</span>
                        <div style="font-weight:800;font-size:15px;color:${pct>=50?'#059669':'#dc2626'};">🏆 ${st.best_score}/${st.best_total} <span style="font-size:11px;color:var(--text-secondary);">(${pct}%)</span></div>
                        <span id="expandIcon_${st.student_id}" style="font-size:12px;color:var(--text-secondary);">▼</span>
                    </div>
                </div>
                <div id="studentAttempts_${st.student_id}" style="display:none;border-top:1px solid var(--border-soft);padding:12px 18px;background:var(--bg-secondary);">`;

            st.attempts.forEach((att, idx) => {
                const aPct = att.total_marks > 0 ? Math.round((att.score / att.total_marks) * 100) : 0;
                const isBest = att.id === st.best_id;
                const aColor = att.visible ? '#059669' : (att.has_text_questions ? '#d97706' : '#6b7280');
                const aText = att.visible ? '{{ __("مرئي") }}' : (att.has_text_questions ? '{{ __("بانتظار التصحيح") }}' : '{{ __("جاهز") }}');

                html += `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid ${isBest?'#059669':'var(--border-soft)'};border-radius:var(--radius-sm);background:${isBest?'#f0fdf4':'var(--white)'};margin-bottom:8px;">
                    <div onclick="showAttemptDetail(${att.id})" style="display:flex;align-items:center;gap:10px;flex:1;cursor:pointer;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-secondary);">{{ __("محاولة") }} ${idx+1}</div>
                        ${isBest ? '<span style="background:#059669;color:#fff;padding:2px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:700;">🏆</span>' : ''}
                        <div style="color:var(--text-secondary);font-size:11px;">📅 ${new Date(att.completed_at).toLocaleString(isEn()?'en':'ar')}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="padding:3px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:700;background:'+aColor+'15;color:'+aColor+';">${aText}</span>
                        <div style="font-weight:800;font-size:14px;color:${aPct>=50?'#059669':'#dc2626'};">${att.score}/${att.total_marks}</div>
                        ${!isBest ? `<button onclick="event.stopPropagation();confirmSetBest(${att.id}, ${att.has_text_questions?'true':'false'})" title="{{ __("تحديد كأفضل") }}" style="background:#fef3c7;color:#92400e;border:1px solid #fbbf24;padding:3px 8px;border-radius:var(--radius-sm);font-size:11px;cursor:pointer;font-weight:700;transition:all 0.15s;">🏆</button>` : ''}
                    </div>
                </div>`;
            });

            html += '</div></div>';
        });
        html += '</div>';
        document.getElementById('reviewAttemptsList').innerHTML = html;
    }

    function toggleStudentAttempts(studentId) {
        const el = document.getElementById('studentAttempts_' + studentId);
        const icon = document.getElementById('expandIcon_' + studentId);
        if (el.style.display === 'none') { el.style.display = 'block'; icon.textContent = '▲'; }
        else { el.style.display = 'none'; icon.textContent = '▼'; }
    }

    function confirmSetBest(attemptId, hasUngraded) {
        const msg = hasUngraded ? '⚠️ {{ __("هذه المحاولة تحتوي أسئلة نصية لم تُصحّح بعد. هل أنت متأكد؟") }}' : '🏆 {{ __("هل تريد تحديد هذه المحاولة كأفضل محاولة؟") }}';
        if (!confirm(msg)) return;
        apiFetch('/admin/elearning/attempts/' + attemptId + '/set-best', { method: 'POST' }).then(d => { showToast(d.message); openReviewModal(currentReviewQuiz.id, currentReviewQuiz.title); });
    }

    function showAttemptDetail(attemptId) {
        let att = null, stData = null;
        for (const st of currentReviewAttempts) { const f = st.attempts.find(a => a.id === attemptId); if (f) { att = f; stData = st; break; } }
        if (!att) return;
        const pct = att.total_marks > 0 ? Math.round((att.score / att.total_marks) * 100) : 0;
        const isBest = att.id === stData.best_id;
        const hasUngraded = att.has_text_questions && att.answers && att.answers.some(a => a.type === 'text' && a.teacher_marks == null);

        let html = `<div style="margin-bottom:16px;"><div onclick="renderStudentsList()" style="cursor:pointer;color:var(--blue-main);font-size:13px;font-weight:700;margin-bottom:12px;">⬅️ {{ __("العودة") }}</div>`;
        html += `<div style="display:flex;align-items:center;justify-content:space-between;"><div><div style="font-weight:800;font-size:16px;">👤 ${stData.student_name} <small style="color:#888;">(${stData.student_number})</small></div>`;
        html += `<div style="font-size:12px;color:#888;">📅 ${new Date(att.completed_at).toLocaleString(isEn()?'en':'ar')}</div></div>`;
        html += `<div style="text-align:center;"><div style="font-size:28px;font-weight:900;color:${pct>=50?'#27ae60':'#c62828'};">${att.score}/${att.total_marks}</div><div style="font-size:12px;color:#888;">${pct}%</div></div></div></div>`;

        if (hasUngraded) html += `<div style="background:#fdf1d9;border:1px solid #ffc107;border-radius:10px;padding:12px;margin-bottom:16px;font-size:13px;color:#93680c;">⚠️ {{ __("يحتوي على أسئلة نصية لم تُصحّح") }}</div>`;
        if (isBest) html += `<div style="background:#e1f7e7;border:1px solid #27ae60;border-radius:10px;padding:12px;margin-bottom:16px;font-size:13px;color:#157a35;font-weight:700;">🏆 {{ __("هذه هي المحاولة المحتسبة") }}</div>`;

        if (att.answers && att.answers.length) {
            html += '<div style="display:flex;flex-direction:column;gap:10px;">';
            att.answers.forEach((ans, i) => {
                const borderColor = ans.type === 'text' ? (ans.teacher_marks != null ? (ans.teacher_marks > 0 ? '#27ae60' : '#c62828') : '#f0ad4e') : (ans.is_correct ? '#27ae60' : '#c62828');
                html += `<div style="border:2px solid ${borderColor};border-radius:12px;padding:14px;background:#fff;">`;
                html += `<div style="font-weight:700;font-size:14px;margin-bottom:8px;">${i+1}. <small style="color:#888;">(${ans.marks} {{ __("درجة") }})</small></div>`;

                if (ans.type === 'mc') {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    const options = q?.options || [];
                    html += `<div style="font-size:13px;color:#555;margin-bottom:6px;">${q?.question || ''}</div>`;
                    html += '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;">';
                    options.forEach((o, oi) => {
                        const isUser = String(ans.answer) === String(oi);
                        const isCorrect = String(q?.correct_answer) === String(oi);
                        let bg = '#f5f5f5';
                        if (isCorrect) bg = '#e1f7e7';
                        if (isUser && !isCorrect) bg = '#fde3e3';
                        html += `<span style="padding:4px 10px;border-radius:8px;font-size:12px;background:${bg};font-weight:600;">${String.fromCharCode(65+oi)}. ${o}</span>`;
                    });
                    html += '</div>';
                    html += `<div style="font-size:12px;font-weight:700;color:${ans.is_correct?'#27ae60':'#c62828'};">${ans.is_correct?'✅ {{ __("صحيح") }}':'❌ {{ __("خطأ") }}'}</div>`;
                } else if (ans.type === 'tf') {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    html += `<div style="font-size:13px;color:#555;margin-bottom:6px;">${q?.question || ''}</div>`;
                    html += `<div style="font-size:13px;">{{ __("إجابة الطالب") }}: <strong>${ans.answer == '1' ? '✅ {{ __("صح") }}' : '❌ {{ __("خطأ") }}'}</strong></div>`;
                    html += `<div style="font-size:12px;font-weight:700;color:${ans.is_correct?'#27ae60':'#c62828'};">${ans.is_correct?'✅ {{ __("صحيح") }}':'❌ {{ __("خطأ") }}'}</div>`;
                } else {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    html += `<div style="font-size:13px;color:#555;margin-bottom:6px;">${q?.question || ''}</div>`;
                    html += `<div style="background:#f8f9fa;padding:10px;border-radius:8px;font-size:13px;margin-bottom:8px;white-space:pre-wrap;border:1px solid #e0e0e0;">${ans.answer || '—'}</div>`;
                    html += '<div style="display:flex;align-items:center;gap:8px;">';
                    html += '<label style="font-size:12px;font-weight:700;">{{ __("العلامة") }}:</label>';
                    html += `<input type="number" min="0" max="${ans.marks}" value="${ans.teacher_marks != null ? ans.teacher_marks : ''}" data-qid="${ans.question_id}" id="marks_${att.id}_${ans.question_id}" style="width:70px;padding:6px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;text-align:center;" placeholder="${ans.marks}">`;
                    html += `<span style="color:#888;font-size:12px;">/ ${ans.marks}</span></div>`;
                }
                html += '</div>';
            });
            html += '</div>';

            html += '<div style="display:flex;flex-direction:column;gap:8px;margin-top:16px;">';
            if (att.has_text_questions) html += `<button onclick="saveGrades(${att.id})" style="background:var(--blue-main);color:#fff;border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">💾 {{ __("حفظ التصحيح") }}</button>`;
            html += `<button onclick="toggleVisibility(${att.id})" style="background:${att.visible?'#fdf1d9;color:#93680c':'#e1f7e7;color:#157a35'};border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">${att.visible?'🚫 {{ __("إخفاء") }}':'👁️ {{ __("عرض") }}'}</button>`;
            if (!isBest) html += `<button onclick="confirmSetBest(${att.id}, ${hasUngraded?'true':'false'})" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">🏆 {{ __("تحديد كأفضل") }}</button>`;
            else html += `<div style="text-align:center;padding:12px;background:#e1f7e7;border-radius:10px;font-weight:700;color:#157a35;">🏆 {{ __("محتسبة") }}</div>`;
            html += `<button onclick="confirmDeleteAttempt(${att.id})" style="background:#fce4ec;color:#c62828;border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">🗑️ {{ __("حذف المحاولة") }}</button>`;
            html += '</div>';
        }

        document.getElementById('reviewAttemptsList').innerHTML = html;
    }

    function saveGrades(attemptId) {
        const teacherMarks = [];
        document.querySelectorAll('[id^="marks_'+attemptId+'_"]').forEach(input => {
            teacherMarks.push({ question_id: input.dataset.qid, marks: parseFloat(input.value) || 0 });
        });
        apiFetch('/admin/elearning/attempts/' + attemptId + '/grade', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ teacher_marks: teacherMarks }),
        }).then(d => { showToast(d.message); openReviewModal(currentReviewQuiz.id, currentReviewQuiz.title); });
    }

    function toggleVisibility(attemptId) {
        apiFetch('/admin/elearning/attempts/' + attemptId + '/toggle-visibility', { method: 'POST' })
            .then(d => { showToast(d.message); openReviewModal(currentReviewQuiz.id, currentReviewQuiz.title); });
    }

    function confirmDeleteAttempt(attemptId) {
        if (!confirm('⚠️ {{ __("هل أنت متأكد من حذف هذه المحاولة؟ سيتم السماح للطالب بإعادة المحاولة.") }}')) return;
        apiFetch('/admin/elearning/attempts/' + attemptId, { method: 'DELETE' })
            .then(d => { showToast(d.message); openReviewModal(currentReviewQuiz.id, currentReviewQuiz.title); });
    }

    // --- Sessions ---
    function loadSessions() {
        apiFetch(`/admin/elearning/${currentSectionId}/sessions`).then(data => {
            const l = document.getElementById('sessionsList');
            const labels = { meet: 'Google Meet', classroom: 'Google Classroom', video: '🎬 {{ __("فيديو") }}' };
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">🎥</div><p>{{ __("لا توجد حصص") }}</p></div>'; return; }
            l.innerHTML = data.map(s => `
                <div class="content-item">
                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div class="icon-box session ${s.type}">🎥</div>
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${s.title}</div>
                            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">${labels[s.type]||s.type} ${s.scheduled_at ? '· ' + new Date(s.scheduled_at).toLocaleString(isEn()?'en':'ar') : ''}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <a href="${s.url}" target="_blank" style="padding:6px 10px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);font-size:11px;font-weight:700;text-decoration:none;">🔗</a>
                        <button onclick='editSession(${JSON.stringify({id:s.id,title:s.title,type:s.type,url:s.url,scheduled_at:s.scheduled_at}).replace(/'/g, "&#39;")})' style="padding:6px 10px;border-radius:var(--radius-sm);background:var(--blue-50);color:var(--blue-main);border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">✏️</button>
                        <button onclick="deleteSession(${s.id})" style="padding:6px 10px;border-radius:var(--radius-sm);background:#fecaca;color:#dc2626;border:none;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">🗑️</button>
                    </div>
                </div>
            `).join('');
        });
    }

    function editSession(s) {
        document.getElementById('sessionTitle').value = s.title;
        document.getElementById('sessionType').value = s.type || 'meet';
        document.getElementById('sessionUrl').value = s.url || '';
        document.getElementById('sessionDate').value = s.scheduled_at ? s.scheduled_at.slice(0, 16) : '';
        document.getElementById('sessionEditId').value = s.id;
        document.getElementById('editSessionModal').style.display = 'flex';
    }

    function saveSession() {
        const editId = document.getElementById('sessionEditId').value;
        const body = { title: document.getElementById('sessionTitle').value, type: document.getElementById('sessionType').value, url: document.getElementById('sessionUrl').value, scheduled_at: document.getElementById('sessionDate').value || null };
        apiFetch(`/admin/elearning/sessions/${editId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
            .then(d => { showToast(d.message); document.getElementById('editSessionModal').style.display = 'none'; loadSessions(); });
    }

    function deleteSession(id) {
        if (!confirm('{{ __("هل أنت متأكد؟") }}')) return;
        apiFetch(`/admin/elearning/sessions/${id}`, { method: 'DELETE' }).then(d => { showToast(d.message); loadSessions(); });
    }
</script>
@stop
