@extends('layouts.dashboard')

@section('title', __('التعلم الإلكتروني'))
@section('page-title', __('التعلم الإلكتروني'))

@section('sidebar')
    <a href="/teacher">📊 <span>{{ __('لوحتي') }}</span></a>
    <a href="/teacher/grades">📝 <span>{{ __('الدرجات') }}</span></a>
    <a href="/teacher/schedule">📅 <span>{{ __('جدولي') }}</span></a>
    <a href="/teacher/e-learning" class="active">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>
    <a href="/teacher/library">📖 <span>{{ __('المكتبة') }}</span></a>
@stop

@section('content')
<div id="step1">
    <div class="header">
        <h1>{{ __('اختر صفأً') }}</h1>
        <p style="margin:4px 0 0;font-size:13px;color:var(--text-secondary);">{{ __('أضف المواد والاختبارات والجلسات الإلكترونية') }}</p>
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

    <div id="panelMaterials" class="tab-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div class="section-title" style="margin-bottom:0;">
                <div class="section-icon mat">📚</div>
                <h4>{{ __('المواد التعليمية') }}</h4>
            </div>
            <button onclick="showAddMaterialForm()" class="btn btn-primary" style="padding:8px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;">➕ {{ __('إضافة') }}</button>
        </div>
        <div id="addMaterialForm" style="display:none;background:var(--bg-secondary);padding:20px;border-radius:var(--radius-md);margin-bottom:16px;border:1px solid var(--border-soft);">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }} *</label><input id="addMaterialTitle" required style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;transition:border-color 0.15s;background:var(--white);color:var(--text-primary);" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></div>
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="addMaterialDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;resize:vertical;background:var(--white);color:var(--text-primary);font-family:inherit;" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></textarea></div>
            <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }}</label><input id="addMaterialLink" placeholder="https://..." style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" dir="ltr" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveNewMaterial()" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ') }}</button>
                <button onclick="document.getElementById('addMaterialForm').style.display='none'" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;border:1px solid var(--border-soft);background:var(--white);cursor:pointer;color:var(--text-secondary);">{{ __('إلغاء') }}</button>
            </div>
        </div>
        <div id="materialsList"></div>
    </div>

    <div id="editMaterialModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:teacherModalIn .25s ease;">
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
                <input type="hidden" id="editMaterialId">
                <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }}</label><input id="editMaterialTitle" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="editMaterialDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;"></textarea></div>
                <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }}</label><input id="editMaterialLink" placeholder="https://..." style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" dir="ltr"></div>
                <button class="btn btn-primary" onclick="saveMaterialEdit()" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
            </div>
        </div>
    </div>

    <div id="panelQuizzes" class="tab-panel" style="display:none;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div class="section-title" style="margin-bottom:0;">
                <div class="section-icon quiz">📝</div>
                <h4>{{ __('الاختبارات') }}</h4>
            </div>
            <button onclick="showAddQuizForm()" class="btn btn-primary" style="padding:8px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;background:linear-gradient(135deg,#d97706,#a29bfe);">➕ {{ __('إضافة') }}</button>
        </div>
        <div id="addQuizForm" style="display:none;background:var(--bg-secondary);padding:20px;border-radius:var(--radius-md);margin-bottom:16px;border:1px solid var(--border-soft);">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('عنوان الاختبار') }} *</label><input id="addQuizTitle" required style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></div>
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="addQuizDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوقت (بالدقائق)') }}</label><input id="addQuizTime" type="number" min="1" step="1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الحد الأقصى للمحاولات') }}</label><input id="addQuizMaxAttempts" type="number" min="1" placeholder="{{ __('بلا حد') }}" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد البداية') }}</label><input id="addQuizScheduledAt" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد النهاية') }}</label><input id="addQuizScheduledEnd" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveNewQuiz()" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ') }}</button>
                <button onclick="document.getElementById('addQuizForm').style.display='none'" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;border:1px solid var(--border-soft);background:var(--white);cursor:pointer;color:var(--text-secondary);">{{ __('إلغاء') }}</button>
            </div>
        </div>
        <div id="quizzesList"></div>
    </div>

    <div id="panelSessions" class="tab-panel" style="display:none;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div class="section-title" style="margin-bottom:0;">
                <div class="section-icon sess">🎥</div>
                <h4>{{ __('الحصص الإلكترونية') }}</h4>
            </div>
            <button onclick="showAddSessionForm()" class="btn btn-primary" style="padding:8px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;background:linear-gradient(135deg,#059669,#10b981);">➕ {{ __('إضافة') }}</button>
        </div>
        <div id="addSessionForm" style="display:none;background:var(--bg-secondary);padding:20px;border-radius:var(--radius-md);margin-bottom:16px;border:1px solid var(--border-soft);">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }} *</label><input id="addSessionTitle" required style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('النوع') }} *</label><select id="addSessionType" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="meet">Google Meet</option><option value="classroom">Google Classroom</option><option value="video">{{ __('فيديو مسجل') }}</option></select></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('التاريخ والوقت') }}</label><input id="addSessionDate" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }} *</label><input id="addSessionUrl" type="url" required placeholder="https://..." style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" dir="ltr" onfocus="this.style.borderColor='var(--blue-main)'" onblur="this.style.borderColor='var(--border-soft)'"></div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary" onclick="saveNewSession()" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ') }}</button>
                <button onclick="document.getElementById('addSessionForm').style.display='none'" style="flex:1;padding:10px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;border:1px solid var(--border-soft);background:var(--white);cursor:pointer;color:var(--text-secondary);">{{ __('إلغاء') }}</button>
            </div>
        </div>
        <div id="editSessionModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
            <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:teacherModalIn .25s ease;">
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
                    <input type="hidden" id="editSessionDataId">
                    <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('العنوان') }}</label><input id="editSessionTitle" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('النوع') }}</label><select id="editSessionType" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="meet">Google Meet</option><option value="classroom">Google Classroom</option><option value="video">{{ __('فيديو مسجل') }}</option></select></div>
                        <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('التاريخ والوقت') }}</label><input id="editSessionDate" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"></div>
                    </div>
                    <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الرابط') }}</label><input id="editSessionUrl" type="url" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);" dir="ltr"></div>
                    <button class="btn btn-primary" onclick="saveSessionEdit()" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
                </div>
            </div>
        </div>
        <div id="sessionsList"></div>
    </div>
</div>

<div id="questionsModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:700px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:teacherModalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">❓</div>
                <h3 id="questionsTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('الأسئلة') }}</h3>
            </div>
            <button onclick="closeQuestionsModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
        </div>
        <div style="padding:20px 28px;">
            <div id="questionsList"></div>
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border-soft);">
                <h4 style="margin:0 0 14px;font-size:15px;font-weight:700;color:var(--text-primary);">➕ {{ __('إضافة سؤال جديد') }}</h4>
                <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('السؤال') }} *</label><textarea id="qText" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;"></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('النوع') }}</label><select id="qType" onchange="toggleQuestionOptions()" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="mc">{{ __('اختيار من متعدد') }}</option><option value="tf">{{ __('صح / خطأ') }}</option><option value="text">{{ __('سؤال حر') }}</option></select></div>
                    <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الدرجة') }}</label><input id="qMarks" type="number" value="1" min="1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"></div>
                </div>
                <div id="mcOptions">
                    <div style="margin-bottom:10px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الخيار 1') }} *</label><input id="qOpt1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                    <div style="margin-bottom:10px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الخيار 2') }} *</label><input id="qOpt2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px;">
                        <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الخيار 3') }}</label><input id="qOpt3" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                        <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الخيار 4') }}</label><input id="qOpt4" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                    </div>
                    <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الإجابة الصحيحة') }}</label><select id="qCorrect" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="0">{{ __('الخيار 1') }}</option><option value="1">{{ __('الخيار 2') }}</option><option value="2">{{ __('الخيار 3') }}</option><option value="3">{{ __('الخيار 4') }}</option></select></div>
                </div>
                <div id="tfOptions" style="display:none;">
                    <div style="margin-bottom:14px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الإجابة الصحيحة') }}</label><select id="qTfCorrect" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;background:var(--white);color:var(--text-primary);"><option value="1">✅ {{ __('صح') }}</option><option value="0">❌ {{ __('خطأ') }}</option></select></div>
                </div>
                <button class="btn btn-primary" onclick="saveQuestion()" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">➕ {{ __('إضافة السؤال') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes teacherModalIn { from { opacity:0; transform:translateY(16px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    @keyframes iconFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-3px)} }

    .el-card { background:var(--white); border-radius:var(--radius-lg); padding:24px; text-align:center; cursor:pointer; transition:all 0.25s; border:2px solid var(--border-soft); position:relative; overflow:hidden; }
    .el-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:var(--blue-main); }
    .el-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; background:linear-gradient(135deg,rgba(74,127,247,0.06),transparent); border-radius:0 0 0 80px; }
    .el-card .icon { width:56px; height:56px; border-radius:var(--radius-lg); background:linear-gradient(135deg,var(--blue-main),#6c5ce7); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:#fff; box-shadow:0 4px 14px rgba(74,127,247,0.3); }
    .el-card .name { font-weight:800; font-size:17px; margin-bottom:6px; color:var(--text-primary); }
    .el-card .section-tag { display:inline-block; padding:4px 14px; border-radius:var(--radius-sm); font-size:12px; font-weight:700; background:var(--blue-50); color:var(--blue-main); }
    .el-card .info { color:var(--text-secondary); font-size:12px; margin-top:8px; }

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

    .empty-state { text-align:center; padding:56px 20px; color:var(--text-secondary); }
    .empty-state .icon { font-size:52px; margin-bottom:14px; }
</style>

<div id="reviewModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:92%;max-width:800px;max-height:88vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:teacherModalIn .25s ease;">
        <div style="padding:24px 28px 0;border-bottom:1px solid var(--border-soft);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">📋</div>
                <h3 id="reviewTitle" style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">📋 {{ __('مراجعة المحاولات') }}</h3>
            </div>
            <button onclick="closeReviewModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
        </div>
        <div style="padding:20px 28px 24px;">
            <div id="reviewAttemptsList"></div>
        </div>
    </div>
</div>

<div id="editQuizModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(6px);">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:0;width:90%;max-width:500px;box-shadow:var(--shadow-xl);overflow:hidden;animation:teacherModalIn .25s ease;">
        <div style="padding:24px 28px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,#d97706,#a29bfe);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">✏️</div>
                    <h3 style="margin:0;font-size:17px;font-weight:800;color:var(--text-primary);">{{ __('تعديل الاختبار') }}</h3>
                </div>
                <button onclick="closeEditQuizModal()" style="width:32px;height:32px;border-radius:var(--radius-sm);border:none;background:var(--bg-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--text-secondary);">✕</button>
            </div>
        </div>
        <div style="padding:0 28px 24px;">
            <input type="hidden" id="editQuizId">
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('عنوان الاختبار') }} *</label><input id="editQuizTitle" required style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            <div style="margin-bottom:12px;"><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوصف') }}</label><textarea id="editQuizDesc" rows="2" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);font-family:inherit;resize:vertical;"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الوقت (بالدقائق)') }}</label><input id="editQuizTime" type="number" min="1" step="1" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">{{ __('الحد الأقصى للمحاولات') }}</label><input id="editQuizMaxAttempts" type="number" min="1" placeholder="{{ __('بلا حد') }}" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد البداية') }}</label><input id="editQuizScheduledAt" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
                <div><label style="font-weight:700;font-size:13px;display:block;margin-bottom:5px;color:var(--text-primary);">📅 {{ __('موعد النهاية') }}</label><input id="editQuizScheduledEnd" type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;background:var(--white);color:var(--text-primary);"></div>
            </div>
            <button class="btn btn-primary" onclick="saveEditQuiz()" style="width:100%;padding:11px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __('حفظ التعديلات') }}</button>
        </div>
    </div>
</div>

<script>
    let sections = [];
    let currentSectionId = null;
    let currentQuizId = null;

    const isEn = () => document.documentElement.lang === 'en';
    const gradeMap = {'الصف الأول':'Class 1','الصف الثاني':'Class 2','الصف الثالث':'Class 3','الصف الرابع':'Class 4','الصف الخامس':'Class 5'};
    const sectionMap = {'أ':'A','ب':'B','ج':'C','د':'D'};
    const tGrade = (name) => isEn() ? (gradeMap[name] || name) : name;
    const tSection = (letter) => isEn() ? (sectionMap[letter] || letter) : letter;

    loadSections();

    function loadSections() {
        apiFetch('/teacher/elearning/sections').then(data => {
            sections = data;
            renderSections();
            document.getElementById('loadingSections').style.display = 'none';
            document.getElementById('sectionsGrid').style.display = 'grid';
        }).catch(e => {
            document.getElementById('loadingSections').innerHTML = '<div class="empty-state"><div class="icon">❌</div><p>' + (e.message || 'خطأ') + '</p></div>';
        });
    }

    function renderSections() {
        const g = document.getElementById('sectionsGrid');
        if (!sections.length) {
            g.innerHTML = '<div style="grid-column:1/-1;" class="empty-state"><div class="icon">🏫</div><p>{{ __("لا توجد صفوف مخصصة لك بعد") }}</p></div>';
            return;
        }
        g.innerHTML = sections.map(s => `
            <div class="el-card" onclick="openSection(${s.id}, '${s.name} ${s.section}', '${s.grade_level}')">
                <div class="icon">🏫</div>
                <div class="name">${tGrade(s.name)}</div>
                <div class="section-tag">{{ __("شعبة") }} ${tSection(s.section)}</div>
                <div class="info">📚 ${s.materials_count} · 📝 ${s.quizzes_count} · 🎥 ${s.sessions_count}</div>
            </div>
        `).join('');
    }

    function openSection(id, name, gradeLevel) {
        currentSectionId = id;
        const s = sections.find(sec => sec.id === id);
        document.getElementById('sectionTitle').textContent = tGrade(gradeLevel) + ' — {{ __("شعبة") }} ' + tSection(s ? s.section : '');
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = '';
        loadSectionData();
    }

    function backToSections() {
        document.getElementById('step1').style.display = '';
        document.getElementById('step2').style.display = 'none';
        currentSectionId = null;
        loadSections();
    }

    function loadSectionData() { loadMaterials(); loadQuizzes(); loadSessions(); }

    function switchTab(tab) {
        ['materials', 'quizzes', 'sessions'].forEach(t => {
            const panel = document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1));
            const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
            panel.style.display = t === tab ? '' : 'none';
            if (t === tab) { btn.classList.add('active'); }
            else { btn.classList.remove('active'); }
        });
    }

    function loadMaterials() {
        apiFetch(`/teacher/elearning/${currentSectionId}/materials`).then(data => {
            const l = document.getElementById('materialsList');
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📚</div><p>{{ __("لا توجد مواد بعد") }}</p></div>'; return; }
            l.innerHTML = data.map(m => `
                <div class="content-item">
                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div class="icon-box material">📚</div>
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${m.title}</div>
                            <div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;">
                                ${m.file_path ? '<a href="/storage/' + m.file_path + '" target="_blank" style="color:var(--blue-main);font-size:12px;font-weight:700;text-decoration:none;">📎 {{ __("تحميل") }}</a>' : ''}
                                ${m.link ? '<a href="' + m.link + '" target="_blank" style="color:#15803d;font-size:12px;font-weight:700;text-decoration:none;">🔗 {{ __("رابط") }}</a>' : ''}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <button onclick="openEditMaterialModal(${m.id}, '${m.title.replace(/'/g, "\\'")}', '${(m.description||'').replace(/'/g, "\\'")}', '${(m.link||'').replace(/'/g, "\\'")}')" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:7px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">✏️</button>
                        <button onclick="deleteMaterial(${m.id})" style="background:#fecaca;color:#dc2626;border:none;padding:7px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">🗑️</button>
                    </div>
                </div>
            `).join('');
        });
    }

    function openEditMaterialModal(id, title, desc, link) {
        document.getElementById('editMaterialId').value = id;
        document.getElementById('editMaterialTitle').value = title;
        document.getElementById('editMaterialDesc').value = desc;
        document.getElementById('editMaterialLink').value = link;
        document.getElementById('editMaterialModal').style.display = 'flex';
    }

    function saveMaterialEdit() {
        const id = document.getElementById('editMaterialId').value;
        apiFetch('/teacher/elearning/materials/' + id, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('editMaterialTitle').value,
                description: document.getElementById('editMaterialDesc').value,
                link: document.getElementById('editMaterialLink').value || null,
            }),
        }).then(d => { showToast(d.message); document.getElementById('editMaterialModal').style.display = 'none'; loadMaterials(); });
    }

    function showAddMaterialForm() { document.getElementById('addMaterialForm').style.display = ''; }

    function saveNewMaterial() {
        apiFetch('/teacher/elearning/' + currentSectionId + '/materials', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('addMaterialTitle').value,
                description: document.getElementById('addMaterialDesc').value,
                link: document.getElementById('addMaterialLink').value || null,
            }),
        }).then(d => {
            showToast(d.message);
            document.getElementById('addMaterialForm').style.display = 'none';
            ['addMaterialTitle','addMaterialDesc','addMaterialLink'].forEach(id => document.getElementById(id).value = '');
            loadMaterials();
        }).catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function deleteMaterial(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف المادة؟") }}')) return;
        apiFetch('/teacher/elearning/materials/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadMaterials(); });
    }

    function loadQuizzes() {
        apiFetch(`/teacher/elearning/${currentSectionId}/quizzes`).then(data => {
            const l = document.getElementById('quizzesList');
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">📝</div><p>{{ __("لا توجد اختبارات بعد") }}</p></div>'; return; }
            l.innerHTML = data.map(q => {
                const attInfo = q.max_attempts ? ' · 🔁 {{ __("حد") }}: ' + q.max_attempts : '';
                const attemptsCount = q.attempts?.length || 0;
                const hasAttempts = attemptsCount > 0;
                return `
                    <div class="content-item">
                        <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                            <div class="icon-box quiz">📝</div>
                            <div style="min-width:0;">
                                <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${q.title}</div>
                                <div style="color:var(--text-secondary);font-size:12px;margin-top:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">⏱️ ${q.time_limit || '{{ __("بلا حد") }}'} {{ __("دقيقة") }} · ❓ ${q.questions?.length || 0} {{ __("سؤال") }} · 👥 ${attemptsCount} {{ __("محاولة") }}${attInfo}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
                            ${hasAttempts ? '<button onclick="openReviewModal('+q.id+')" style="background:#dcfce7;color:#15803d;border:none;padding:6px 12px;border-radius:var(--radius-sm);cursor:pointer;font-size:12px;font-weight:700;transition:all 0.15s;">📋 {{ __("مراجعة") }}</button>' : ''}
                            <button onclick="openQuestionsModal(${q.id}, '${q.title.replace(/'/g, "\\'")}')" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:6px 12px;border-radius:var(--radius-sm);cursor:pointer;font-size:12px;font-weight:700;transition:all 0.15s;">❓ {{ __("الأسئلة") }}</button>
                            <button onclick="openEditQuiz(${q.id})" style="background:#fef3c7;color:#92400e;border:none;padding:6px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">✏️</button>
                            <button onclick="deleteQuiz(${q.id})" style="background:#fecaca;color:#dc2626;border:none;padding:6px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">🗑️</button>
                        </div>
                    </div>
                `;
            }).join('');
        });
    }

    function openEditQuiz(quizId) {
        apiFetch(`/teacher/elearning/${currentSectionId}/quizzes`).then(data => {
            const q = data.find(x => x.id === quizId);
            if (!q) return;
            document.getElementById('editQuizId').value = q.id;
            document.getElementById('editQuizTitle').value = q.title;
            document.getElementById('editQuizDesc').value = q.description || '';
            document.getElementById('editQuizTime').value = q.time_limit || '';
            document.getElementById('editQuizMaxAttempts').value = q.max_attempts || '';
            document.getElementById('editQuizScheduledAt').value = q.scheduled_at ? q.scheduled_at.slice(0, 16) : '';
            document.getElementById('editQuizScheduledEnd').value = q.scheduled_end ? q.scheduled_end.slice(0, 16) : '';
            document.getElementById('editQuizModal').style.display = 'flex';
        });
    }

    function closeEditQuizModal() {
        document.getElementById('editQuizModal').style.display = 'none';
    }

    function saveEditQuiz() {
        const id = document.getElementById('editQuizId').value;
        apiFetch('/teacher/elearning/quizzes/' + id, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('editQuizTitle').value,
                description: document.getElementById('editQuizDesc').value,
                time_limit: document.getElementById('editQuizTime').value || null,
                max_attempts: document.getElementById('editQuizMaxAttempts').value || null,
                scheduled_at: document.getElementById('editQuizScheduledAt').value || null,
                scheduled_end: document.getElementById('editQuizScheduledEnd').value || null,
            }),
        }).then(d => {
            showToast(d.message);
            closeEditQuizModal();
            loadQuizzes();
        }).catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function showAddQuizForm() { document.getElementById('addQuizForm').style.display = ''; }

    function saveNewQuiz() {
        apiFetch('/teacher/elearning/' + currentSectionId + '/quizzes', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('addQuizTitle').value,
                description: document.getElementById('addQuizDesc').value,
                time_limit: document.getElementById('addQuizTime').value || null,
                max_attempts: document.getElementById('addQuizMaxAttempts').value || null,
                scheduled_at: document.getElementById('addQuizScheduledAt').value || null,
                scheduled_end: document.getElementById('addQuizScheduledEnd').value || null,
            }),
        }).then(d => {
            showToast(d.message);
            document.getElementById('addQuizForm').style.display = 'none';
            ['addQuizTitle','addQuizDesc','addQuizTime','addQuizMaxAttempts','addQuizScheduledAt','addQuizScheduledEnd'].forEach(id => document.getElementById(id).value = '');
            loadQuizzes();
        }).catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function deleteQuiz(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف الاختبار؟ سيتم حذف جميع أسئلته ومحاولات طلابه.") }}')) return;
        apiFetch('/teacher/elearning/quizzes/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadQuizzes(); });
    }

    function openQuestionsModal(quizId, title) {
        currentQuizId = quizId;
        document.getElementById('questionsTitle').textContent = '❓ ' + title;
        loadQuestions();
        document.getElementById('questionsModal').style.display = 'flex';
    }
    function closeQuestionsModal() { document.getElementById('questionsModal').style.display = 'none'; }

    function loadQuestions() {
        apiFetch(`/teacher/elearning/${currentSectionId}/quizzes`).then(data => {
            const quiz = data.find(q => q.id === currentQuizId);
            if (!quiz) return;
            const l = document.getElementById('questionsList');
            if (!quiz.questions?.length) { l.innerHTML = '<div class="empty-state" style="padding:24px;"><div class="icon">❓</div><p>{{ __("لا توجد أسئلة بعد") }}</p></div>'; return; }
            l.innerHTML = quiz.questions.map((q, i) => {
                let typeLabel = q.type === 'mc' ? '🔘 {{ __("اختيار من متعدد") }}' : q.type === 'tf' ? '✅ {{ __("صح / خطأ") }}' : '📝 {{ __("سؤال حر") }}';
                let answerInfo = '';
                if (q.type === 'mc' && q.options) {
                    answerInfo = q.options.map((o, oi) => '<span style="display:inline-block;margin:2px;padding:3px 10px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;background:'+(oi==q.correct_answer?'#dcfce7;':'var(--bg-secondary);')+'color:'+(oi==q.correct_answer?'#15803d':'var(--text-secondary)')+'">'+String.fromCharCode(65+oi)+'. '+o+'</span>').join('');
                } else if (q.type === 'tf') {
                    answerInfo = q.correct_answer === '1' ? '✅ {{ __("صح") }}' : '❌ {{ __("خطأ") }}';
                } else {
                    answerInfo = '<em style="color:var(--text-secondary);">{{ __("إجابة حرة") }}</em>';
                }
                return '<div class="content-item"><div style="flex:1;"><div style="font-weight:700;font-size:14px;color:var(--text-primary);">'+(i+1)+'. '+q.question+'</div><div style="font-size:12px;color:var(--text-secondary);margin-top:4px;">'+typeLabel+' · '+q.marks+' {{ __("درجة") }}</div><div style="margin-top:6px;">'+answerInfo+'</div></div><button onclick="deleteQuestion('+q.id+')" style="background:#fecaca;color:#dc2626;border:none;padding:5px 8px;border-radius:var(--radius-sm);cursor:pointer;font-size:12px;transition:all 0.15s;">🗑️</button></div>';
            }).join('');
        });
    }

    function toggleQuestionOptions() {
        const type = document.getElementById('qType').value;
        document.getElementById('mcOptions').style.display = type === 'mc' ? '' : 'none';
        document.getElementById('tfOptions').style.display = type === 'tf' ? '' : 'none';
    }

    function saveQuestion() {
        const type = document.getElementById('qType').value;
        const data = { question: document.getElementById('qText').value, type: type, marks: parseInt(document.getElementById('qMarks').value) || 1 };
        if (type === 'mc') {
            data.options = [document.getElementById('qOpt1').value, document.getElementById('qOpt2').value, document.getElementById('qOpt3').value, document.getElementById('qOpt4').value];
            data.correct_answer = document.getElementById('qCorrect').value;
        } else if (type === 'tf') {
            data.correct_answer = document.getElementById('qTfCorrect').value;
        } else { data.correct_answer = null; }
        apiFetch('/teacher/elearning/quizzes/' + currentQuizId + '/questions', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
        }).then(d => {
            showToast(d.message);
            ['qText','qOpt1','qOpt2','qOpt3','qOpt4'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('qMarks').value = '1';
            document.getElementById('qType').value = 'mc';
            toggleQuestionOptions();
            loadQuestions();
            loadQuizzes();
        }).catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function deleteQuestion(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف السؤال؟") }}')) return;
        apiFetch('/teacher/elearning/quizzes/' + currentQuizId + '/questions/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadQuestions(); loadQuizzes(); });
    }

    function loadSessions() {
        apiFetch(`/teacher/elearning/${currentSectionId}/sessions`).then(data => {
            const l = document.getElementById('sessionsList');
            if (!data.length) { l.innerHTML = '<div class="empty-state"><div class="icon">🎥</div><p>{{ __("لا توجد حصص بعد") }}</p></div>'; return; }
            const labels = { meet: 'Google Meet', classroom: 'Google Classroom', video: '🎬 {{ __("فيديو") }}' };
            const locale = isEn() ? 'en' : 'ar';
            l.innerHTML = data.map(s => `
                <div class="content-item">
                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div class="icon-box session ${s.type}">🎥</div>
                        <div style="min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${s.title}</div>
                            <div style="display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap;">
                                <span class="session-tag ${s.type}">${labels[s.type]||s.type}</span>
                                ${s.scheduled_at ? '<span style="color:var(--text-secondary);font-size:12px;">📅 '+new Date(s.scheduled_at).toLocaleString(locale)+'</span>' : ''}
                            </div>
                            <a href="${s.url}" target="_blank" style="color:var(--blue-main);font-size:12px;font-weight:700;text-decoration:none;display:inline-block;margin-top:4px;">🔗 {{ __("فتح الرابط") }}</a>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <button onclick="openEditSessionModal(${s.id})" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:7px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">✏️</button>
                        <button onclick="deleteSession(${s.id})" style="background:#fecaca;color:#dc2626;border:none;padding:7px 10px;border-radius:var(--radius-sm);cursor:pointer;font-size:13px;transition:all 0.15s;">🗑️</button>
                    </div>
                </div>
            `).join('');
        });
    }

    function openEditSessionModal(sessionId) {
        apiFetch(`/teacher/elearning/${currentSectionId}/sessions`).then(data => {
            const s = data.find(x => x.id === sessionId);
            if (!s) return;
            document.getElementById('editSessionTitle').value = s.title;
            document.getElementById('editSessionUrl').value = s.url;
            document.getElementById('editSessionType').value = s.type;
            document.getElementById('editSessionDate').value = s.scheduled_at ? s.scheduled_at.slice(0,16) : '';
            document.getElementById('editSessionDataId').value = s.id;
            document.getElementById('editSessionModal').style.display = 'flex';
        });
    }

    function saveSessionEdit() {
        const id = document.getElementById('editSessionDataId').value;
        apiFetch(`/teacher/elearning/sessions/${id}`, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('editSessionTitle').value,
                type: document.getElementById('editSessionType').value,
                url: document.getElementById('editSessionUrl').value,
                scheduled_at: document.getElementById('editSessionDate').value || null,
            }),
        }).then(d => { showToast(d.message); document.getElementById('editSessionModal').style.display = 'none'; loadSessions(); });
    }

    function showAddSessionForm() { document.getElementById('addSessionForm').style.display = ''; }

    function saveNewSession() {
        apiFetch('/teacher/elearning/' + currentSectionId + '/sessions', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: document.getElementById('addSessionTitle').value,
                type: document.getElementById('addSessionType').value,
                url: document.getElementById('addSessionUrl').value,
                scheduled_at: document.getElementById('addSessionDate').value || null,
            }),
        }).then(d => {
            showToast(d.message);
            document.getElementById('addSessionForm').style.display = 'none';
            ['addSessionTitle','addSessionUrl','addSessionDate'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('addSessionType').value = 'meet';
            loadSessions();
        }).catch(e => { if (e.errors) Object.values(e.errors).forEach(m => showToast(m[0], 'error')); });
    }

    function deleteSession(id) {
        if (!confirm('{{ __("هل أنت متأكد من حذف الحصة؟") }}')) return;
        apiFetch('/teacher/elearning/sessions/' + id, { method: 'DELETE' }).then(d => { showToast(d.message); loadSessions(); });
    }

    // --- Review Attempts ---
    let currentReviewQuiz = null;
    let currentReviewAttempts = [];
    let currentExpandedStudent = null;

    function openReviewModal(quizId) {
        apiFetch(`/teacher/elearning/quizzes/${quizId}/attempts`).then(data => {
            currentReviewQuiz = data.quiz;
            currentReviewAttempts = data.attempts || [];
            document.getElementById('reviewTitle').textContent = '📋 ' + data.quiz.title;
            renderStudentsList();
            document.getElementById('reviewModal').style.display = 'flex';
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function renderStudentsList() {
        if (!currentReviewAttempts.length) {
            document.getElementById('reviewAttemptsList').innerHTML = '<div class="empty-state" style="padding:24px;"><div class="icon">👥</div><p>{{ __("لا توجد محاولات بعد") }}</p></div>';
            return;
        }

        const hiddenCount = currentReviewAttempts.filter(st => !st.best_visible).length;

        let html = '';
        if (hiddenCount > 0) {
            html += `<button onclick="showAllAttempts()" style="width:100%;padding:12px;border-radius:var(--radius-sm);border:none;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:14px;font-weight:700;cursor:pointer;margin-bottom:16px;transition:all 0.15s;">👁️ {{ __("عرض النتائج للطلاب") }} (${hiddenCount} {{ __("طالب") }})</button>`;
        }

        html += '<div style="display:flex;flex-direction:column;gap:10px;">';
        currentReviewAttempts.forEach(st => {
            const pct = st.best_total > 0 ? Math.round((st.best_score / st.best_total) * 100) : 0;
            const statusColor = st.best_visible ? '#059669' : (st.best_has_text ? '#d97706' : '#6b7280');
            const statusText = st.best_visible ? '{{ __("تم العرض") }}' : (st.best_has_text ? '{{ __("بانتظار التصحيح") }}' : '{{ __("جاهز للعرض") }}');
            const attCount = st.attempts.length;

            html += `<div style="border:1.5px solid var(--border-soft);border-radius:var(--radius-md);background:var(--white);overflow:hidden;transition:all 0.2s;">
                <div onclick="toggleStudentAttempts(${st.student_id})" style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer;transition:all 0.15s;" onmouseover="this.parentElement.style.borderColor='var(--blue-main)'" onmouseout="this.parentElement.style.borderColor='var(--border-soft)'">
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:42px;height:42px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--blue-main),#6c5ce7);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:15px;">👤</div>
                        <div>
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);">${st.student_name}</div>
                            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">${st.student_number} ${attCount > 1 ? '· <span style="background:var(--blue-50);color:var(--blue-main);padding:2px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:700;">×' + attCount + ' {{ __("محاولات") }}</span>' : ''}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="padding:4px 10px;border-radius:var(--radius-sm);font-size:11px;font-weight:700;background:'+statusColor+'15;color:'+statusColor+';">${statusText}</span>
                        <div style="font-weight:800;font-size:15px;color:${pct>=50?'#059669':'#dc2626'};">🏆 ${st.best_score}/${st.best_total} <span style="font-size:11px;font-weight:600;color:var(--text-secondary);">(${pct}%)</span></div>
                        <span id="expandIcon_${st.student_id}" style="font-size:12px;color:var(--text-secondary);">▼</span>
                    </div>
                </div>
                <div id="studentAttempts_${st.student_id}" style="display:none;border-top:1px solid var(--border-soft);padding:12px 18px;background:var(--bg-secondary);">`;

            st.attempts.forEach((att, idx) => {
                const aPct = att.total_marks > 0 ? Math.round((att.score / att.total_marks) * 100) : 0;
                const isBest = att.id === st.best_id;
                const aColor = att.visible ? '#059669' : (att.has_text_questions ? '#d97706' : '#6b7280');
                const aText = att.visible ? '{{ __("مرئي") }}' : (att.has_text_questions ? '{{ __("بانتظار التصحيح") }}' : '{{ __("جاهز") }}');

                html += `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid ${isBest?'#059669':'var(--border-soft)'};border-radius:var(--radius-sm);background:${isBest?'#f0fdf4':'var(--white)'};margin-bottom:8px;transition:all 0.2s;">
                    <div onclick="showAttemptDetail(${att.id})" style="display:flex;align-items:center;gap:10px;flex:1;cursor:pointer;">
                        <div style="font-size:13px;font-weight:700;color:var(--text-secondary);">{{ __("محاولة") }} ${idx + 1}</div>
                        ${isBest ? '<span style="background:#059669;color:#fff;padding:2px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:700;">🏆 {{ __("الأفضل") }}</span>' : ''}
                        <div style="color:var(--text-secondary);font-size:11px;">📅 ${new Date(att.completed_at).toLocaleString(isEn()?'en':'ar')}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="padding:3px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:700;background:'+aColor+'15;color:'+aColor+';">${aText}</span>
                        <div style="font-weight:800;font-size:14px;color:${aPct>=50?'#059669':'#dc2626'};">${att.score}/${att.total_marks}</div>
                        ${!isBest ? `<button onclick="event.stopPropagation();confirmSetBest(${att.id}, ${att.has_text_questions ? 'true' : 'false'})" title="{{ __("تحديد كأفضل محاولة") }}" style="background:#fef3c7;color:#92400e;border:1px solid #fbbf24;padding:3px 8px;border-radius:var(--radius-sm);font-size:11px;cursor:pointer;font-weight:700;transition:all 0.15s;">🏆</button>` : ''}
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
        if (el.style.display === 'none') {
            el.style.display = 'block';
            icon.textContent = '▲';
        } else {
            el.style.display = 'none';
            icon.textContent = '▼';
        }
    }

    function showStudentAttempts(studentId) {
        const st = currentReviewAttempts.find(s => s.student_id === studentId);
        if (!st) { renderStudentsList(); currentExpandedStudent = null; return; }
        currentExpandedStudent = studentId;
        renderStudentsList();
        setTimeout(() => {
            const el = document.getElementById('studentAttempts_' + studentId);
            if (el) { el.style.display = 'block'; const icon = document.getElementById('expandIcon_' + studentId); if (icon) icon.textContent = '▲'; }
        }, 50);
    }

    function showAllAttempts() {
        let allAttemptIds = [];
        currentReviewAttempts.forEach(st => {
            st.attempts.forEach(att => {
                if (!att.visible) allAttemptIds.push(att.id);
            });
        });
        if (!allAttemptIds.length) {
            showToast('👁️ {{ __("جميع النتائج معروضة بالفعل") }}', 'info');
            return;
        }
        let done = 0;
        let failed = false;
        allAttemptIds.forEach(id => {
            apiFetch('/teacher/elearning/attempts/' + id + '/toggle-visibility', { method: 'POST' })
                .then(() => {
                    done++;
                    if (done === allAttemptIds.length && !failed) {
                        showToast('👁️ {{ __("تم عرض جميع النتائج") }}');
                        reloadReview();
                    }
                })
                .catch(() => {
                    failed = true;
                    showToast('❌ {{ __("خطأ في عرض بعض النتائج") }}', 'error');
                });
        });
    }

    function showAttemptDetail(attemptId) {
        let att = null;
        let stData = null;
        for (const st of currentReviewAttempts) {
            const found = st.attempts.find(a => a.id === attemptId);
            if (found) { att = found; stData = st; break; }
        }
        if (!att) return;

        const pct = att.total_marks > 0 ? Math.round((att.score / att.total_marks) * 100) : 0;
        const isBest = att.id === stData.best_id;
        const hasUngraded = att.has_text_questions && att.answers && att.answers.some(a => a.type === 'text' && a.teacher_marks == null);

        let html = '<div style="margin-bottom:16px;">';
        html += `<div onclick="showStudentAttempts(${stData.student_id})" style="cursor:pointer;color:var(--blue-main);font-size:13px;font-weight:700;margin-bottom:14px;display:inline-flex;align-items:center;gap:6px;">⬅️ {{ __("العودة") }}</div>`;
        html += `<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">`;
        html += `<div><div style="font-weight:800;font-size:17px;color:var(--text-primary);">👤 ${stData.student_name} <small style="color:var(--text-secondary);">(${stData.student_number})</small></div>`;
        html += `<div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">📅 ${new Date(att.completed_at).toLocaleString(isEn()?'en':'ar')}</div></div>`;
        html += `<div style="text-align:center;padding:12px 20px;border-radius:var(--radius-md);background:var(--bg-secondary);"><div style="font-size:26px;font-weight:900;color:${pct>=50?'#059669':'#dc2626'};">${att.score}/${att.total_marks}</div><div style="font-size:12px;color:var(--text-secondary);">${pct}%</div></div>`;
        html += `</div></div>`;

        if (hasUngraded) {
            html += `<div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:var(--radius-sm);padding:14px;margin-bottom:16px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:8px;">⚠️ {{ __("تحتوي على أسئلة نصية لم تُصحّح بعد. صحّحها أولاً قبل تحديد المحاولة كأفضل") }}</div>`;
        }

        if (isBest) {
            html += `<div style="background:#dcfce7;border:1px solid #059669;border-radius:var(--radius-sm);padding:14px;margin-bottom:16px;font-size:13px;color:#059669;font-weight:700;">🏆 {{ __("هذه هي المحاولة المحتسبة للطالب") }}</div>`;
        }

        if (att.answers && att.answers.length) {
            html += '<div style="display:flex;flex-direction:column;gap:12px;">';
            att.answers.forEach((ans, i) => {
                const borderColor = ans.type === 'text' ? (ans.teacher_marks != null ? (ans.teacher_marks > 0 ? '#059669' : '#dc2626') : '#d97706') : (ans.is_correct ? '#059669' : '#dc2626');
                const headerBg = ans.type === 'text' ? (ans.teacher_marks != null ? (ans.teacher_marks > 0 ? '#dcfce7' : '#fecaca') : '#fef3c7') : (ans.is_correct ? '#dcfce7' : '#fecaca');

                html += `<div style="border:1.5px solid ${borderColor};border-radius:var(--radius-md);overflow:hidden;">`;
                html += `<div style="background:${headerBg};padding:12px 16px;display:flex;align-items:center;gap:10px;">`;
                html += `<span style="width:28px;height:28px;border-radius:50%;background:var(--white);display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;">${i+1}</span>`;
                html += `<span style="font-weight:700;font-size:14px;color:var(--text-primary);">{{ __("درجة") }}: ${ans.marks}</span>`;
                html += `</div>`;

                html += `<div style="padding:14px 16px;background:var(--white);">`;
                if (ans.type === 'mc') {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    const options = q?.options || [];
                    html += `<div style="font-size:14px;color:var(--text-primary);margin-bottom:8px;font-weight:600;">${q?.question || ''}</div>`;
                    html += `<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">`;
                    options.forEach((o, oi) => {
                        const isUser = String(ans.answer) === String(oi);
                        const isCorrect = String(q?.correct_answer) === String(oi);
                        let bg = 'var(--bg-secondary)';
                        if (isCorrect) bg = '#dcfce7';
                        if (isUser && !isCorrect) bg = '#fecaca';
                        html += `<span style="padding:5px 12px;border-radius:var(--radius-sm);font-size:12px;background:${bg};font-weight:600;color:${isCorrect?'#059669':isUser?'#dc2626':'var(--text-secondary)'};">${String.fromCharCode(65+oi)}. ${o}</span>`;
                    });
                    html += '</div>';
                    html += `<div style="font-size:12px;font-weight:700;color:${ans.is_correct?'#059669':'#dc2626'};">${ans.is_correct?'✅ {{ __("صحيح") }}':'❌ {{ __("خطأ") }}'}</div>`;
                } else if (ans.type === 'tf') {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    html += `<div style="font-size:14px;color:var(--text-primary);margin-bottom:8px;font-weight:600;">${q?.question || ''}</div>`;
                    html += `<div style="font-size:14px;margin-bottom:6px;">{{ __("إجابة الطالب") }}: <strong>${ans.answer == '1' ? '✅ {{ __("صح") }}' : '❌ {{ __("خطأ") }}'}</strong></div>`;
                    html += `<div style="font-size:12px;font-weight:700;color:${ans.is_correct?'#059669':'#dc2626'};">${ans.is_correct?'✅ {{ __("صحيح") }}':'❌ {{ __("خطأ") }}'}</div>`;
                } else {
                    const q = (currentReviewQuiz.questions || []).find(qq => qq.id === ans.question_id);
                    html += `<div style="font-size:14px;color:var(--text-primary);margin-bottom:8px;font-weight:600;">${q?.question || ''}</div>`;
                    html += `<div style="background:var(--bg-secondary);padding:12px;border-radius:var(--radius-sm);font-size:14px;margin-bottom:10px;white-space:pre-wrap;border:1px solid var(--border-soft);color:var(--text-primary);line-height:1.7;">${ans.answer || '—'}</div>`;
                    html += '<div style="display:flex;align-items:center;gap:8px;">';
                    html += '<label style="font-size:12px;font-weight:700;color:var(--text-primary);">{{ __("العلامة") }}:</label>';
                    html += `<input type="number" min="0" max="${ans.marks}" value="${ans.teacher_marks != null ? ans.teacher_marks : ''}" data-qid="${ans.question_id}" id="marks_${att.id}_${ans.question_id}" style="width:70px;padding:7px;border:1.5px solid var(--border-soft);border-radius:var(--radius-sm);font-size:13px;text-align:center;background:var(--white);color:var(--text-primary);" placeholder="${ans.marks}">`;
                    html += `<span style="color:var(--text-secondary);font-size:12px;">/ ${ans.marks}</span>`;
                    html += '</div>';
                }
                html += '</div></div>';
            });
            html += '</div>';

            html += '<div style="display:flex;flex-direction:column;gap:8px;margin-top:18px;">';

            if (att.has_text_questions) {
                html += `<button onclick="saveGrades(${att.id})" class="btn btn-primary" style="padding:12px;border-radius:var(--radius-sm);font-size:14px;font-weight:700;border:none;cursor:pointer;">💾 {{ __("حفظ التصحيح") }}</button>`;
            }

            html += `<button onclick="toggleVisibility(${att.id})" style="background:${att.visible?'#fef3c7;color:#92400e':'#dcfce7;color:#059669'};border:none;padding:12px;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:700;transition:all 0.15s;">${att.visible?'🚫 {{ __("إخفاء عن الطالب") }}':'👁️ {{ __("عرض النتيجة للطالب") }}'}</button>`;

            if (isBest) {
                html += `<div style="text-align:center;padding:12px;background:#dcfce7;border-radius:var(--radius-sm);font-weight:700;color:#059669;">🏆 {{ __("هذه هي المحاولة المحتسبة") }}</div>`;
            } else {
                const btnMsg = hasUngraded
                    ? '🏆 {{ __("تحديد كأفضل (بعد التصحيح)") }}'
                    : '🏆 {{ __("تحديد كأفضل محاولة") }}';
                html += `<button onclick="confirmSetBest(${att.id}, ${hasUngraded ? 'true' : 'false'})" style="background:var(--blue-50);color:var(--blue-main);border:none;padding:12px;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:700;transition:all 0.15s;">${btnMsg}</button>`;
            }

            html += `<button onclick="confirmDeleteAttempt(${att.id})" style="background:#fecaca;color:#dc2626;border:none;padding:12px;border-radius:var(--radius-sm);cursor:pointer;font-size:14px;font-weight:700;transition:all 0.15s;">🗑️ {{ __("حذف المحاولة") }}</button>`;

            html += '</div>';
        }

        document.getElementById('reviewAttemptsList').innerHTML = html;
    }

    function confirmSetBest(attemptId, hasUngraded) {
        const msg = hasUngraded
            ? '⚠️ {{ __("هذه المحاولة تحتوي أسئلة نصية لم تُصحّح بعد. هل أنت متأكد من تحديدها كأفضل محاولة؟") }}'
            : '🏆 {{ __("هل تريد تحديد هذه المحاولة كأفضل محاولة للطالب؟") }}';
        if (!confirm(msg)) return;
        setBest(attemptId);
    }

    function reloadReview() {
        return apiFetch(`/teacher/elearning/quizzes/${currentReviewQuiz.id}/attempts`).then(data => {
            currentReviewAttempts = data.attempts || [];
            renderStudentsList();
            loadQuizzes();
        });
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    function setBest(attemptId) {
        apiFetch('/teacher/elearning/attempts/' + attemptId + '/set-best', { method: 'POST' }).then(d => {
            showToast(d.message);
            reloadReview().then(() => showAttemptDetail(attemptId));
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function saveGrades(attemptId) {
        const teacherMarks = [];
        document.querySelectorAll('[id^="marks_'+attemptId+'_"]').forEach(input => {
            const questionId = input.dataset.qid;
            teacherMarks.push({ question_id: questionId, marks: parseFloat(input.value) || 0 });
        });

        apiFetch('/teacher/elearning/attempts/'+attemptId+'/grade', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ teacher_marks: teacherMarks }),
        }).then(d => {
            showToast(d.message);
            reloadReview().then(() => showAttemptDetail(attemptId));
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function toggleVisibility(attemptId) {
        apiFetch('/teacher/elearning/attempts/'+attemptId+'/toggle-visibility', {
            method: 'POST',
        }).then(d => {
            showToast(d.message);
            reloadReview().then(() => showAttemptDetail(attemptId));
        }).catch(e => showToast(e.message || '❌', 'error'));
    }

    function confirmDeleteAttempt(attemptId) {
        if (!confirm('⚠️ {{ __("هل أنت متأكد من حذف هذه المحاولة؟ سيتم السماح للطالب بإعادة المحاولة.") }}')) return;
        apiFetch('/teacher/elearning/attempts/' + attemptId, { method: 'DELETE' })
            .then(d => {
                showToast(d.message);
                const el = document.getElementById('studentAttempts_' + currentExpandedStudent);
                if (el && el.style.display !== 'none') {
                    reloadReview().then(() => showStudentAttempts(currentExpandedStudent));
                } else {
                    reloadReview();
                }
            });
    }
</script>
@stop
