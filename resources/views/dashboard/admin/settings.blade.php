@extends('layouts.dashboard')

@section('title', __('إعدادات المدرسة'))
@section('page-title', __('إعدادات المدرسة'))

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
    <a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>
    <a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>
    <a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>
    <a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>
    <a href="/admin/settings" class="active">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>
@stop

@section('content')
<div class="card" style="max-width:600px;margin:0 auto;">
    <h3 style="margin-bottom:20px;">⚙️ {{ __('إعدادات المدرسة') }}</h3>

    <div class="loading" id="loadingSettings">{{ __('جاري التحميل...') }}</div>
    <div id="settingsForm" style="display:none;">

        <div style="text-align:center;margin-bottom:25px;" id="logoSection">
            <div id="logoPreview" style="width:120px;height:120px;border-radius:var(--radius-lg);background:#f0f0f0;margin:0 auto 15px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:48px;color:#999;border:3px solid var(--blue-main);">
                <img id="logoImg" style="width:100%;height:100%;object-fit:contain;display:none;">
                <span id="logoPlaceholder">🏫</span>
            </div>
            <div style="display:flex;gap:15px;justify-content:center;">
                <label for="logoUpload" style="cursor:pointer;color:var(--blue-main);font-weight:700;font-size:14px;">📷 {{ __('تغيير الشعار') }}</label>
                <span id="removeLogoBtn" style="display:none;cursor:pointer;color:#b8232e;font-weight:700;font-size:14px;" onclick="removeLogo()">🗑️ {{ __('حذف الشعار') }}</span>
            </div>
            <input id="logoUpload" type="file" accept="image/*" style="display:none;" onchange="previewLogo(event)">
        </div>

        <div class="form-group">
            <label>{{ __('اسم المدرسة') }}</label>
            <input id="schoolName" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;margin:5px 0 20px;font-size:16px;">
        </div>

        <button class="btn btn-primary" onclick="saveSettings()" style="width:100%;padding:12px;font-size:15px;">💾 {{ __('حفظ الإعدادات') }}</button>
    </div>
</div>

<script>
    let removeLogoFlag = false;

    apiFetch('/admin/settings').then(s => {
        document.getElementById('loadingSettings').style.display = 'none';
        document.getElementById('settingsForm').style.display = '';

        document.getElementById('schoolName').value = s.school_name || '';

        if (s.school_logo) {
            document.getElementById('logoImg').src = '/storage/' + s.school_logo;
            document.getElementById('logoImg').style.display = '';
            document.getElementById('logoPlaceholder').style.display = 'none';
            document.getElementById('removeLogoBtn').style.display = '';
        }
    });

    function previewLogo(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('logoImg').src = ev.target.result;
            document.getElementById('logoImg').style.display = '';
            document.getElementById('logoPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
        document.getElementById('removeLogoBtn').style.display = '';
        removeLogoFlag = false;
    }

    function removeLogo() {
        if (!confirm(__('حذف الشعار؟'))) return;
        removeLogoFlag = true;
        document.getElementById('logoImg').style.display = 'none';
        document.getElementById('logoPlaceholder').style.display = '';
        document.getElementById('removeLogoBtn').style.display = 'none';
        document.getElementById('logoUpload').value = '';
    }

    function saveSettings() {
        const btn = document.querySelector('.btn-primary');
        btn.textContent = __('⏳ جاري الحفظ...');
        btn.disabled = true;

        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('school_name', document.getElementById('schoolName').value.trim());
        const logo = document.getElementById('logoUpload').files[0];
        if (logo) fd.append('school_logo', logo);
        if (removeLogoFlag) fd.append('remove_logo', '1');
        removeLogoFlag = false;

        apiFetch('/admin/settings', {
            method: 'POST',
            body: fd,
        }).then(d => {
            btn.textContent = __('💾 حفظ الإعدادات');
            btn.disabled = false;
            if (d.settings) {
                document.querySelector('.logo-text').textContent = d.settings.school_name || '';
                const logoImg = document.querySelector('.logo-img');
                const logoPlaceholder = document.querySelector('.logo-img-placeholder');
                if (d.settings.school_logo) {
                    if (logoImg) {
                        logoImg.src = '/storage/' + d.settings.school_logo;
                        logoImg.style.display = '';
                    }
                    if (logoPlaceholder) logoPlaceholder.style.display = 'none';
                }
            }
            showToast(d.message || __('✅ تم الحفظ'));
        }).catch(e => {
            btn.textContent = __('💾 حفظ الإعدادات');
            btn.disabled = false;
            if (e.errors) {
                Object.values(e.errors).forEach(msg => showToast(msg[0], 'error'));
            } else {
                showToast(__('❌ فشل الحفظ'), 'error');
            }
        });
    }
</script>
<style>
    .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
    #logoUpload::-webkit-file-upload-button { display:none; }
</style>
@stop
