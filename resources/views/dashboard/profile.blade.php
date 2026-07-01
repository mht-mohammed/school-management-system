@extends('layouts.dashboard')

@section('title', __('الملف الشخصي'))
@section('page-title', __('الملف الشخصي'))

{{-- No sidebar section — uses layout default based on role --}}

@section('content')
<div class="card" style="max-width:600px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:25px;" id="avatarSection">
        <div id="avatarPreview" style="width:100px;height:100px;border-radius:50%;background:#e0e0e0;margin:0 auto 15px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:36px;color:#999;border:3px solid var(--blue-main);">
            <img id="avatarImg" style="width:100%;height:100%;object-fit:cover;display:none;">
            <span id="avatarPlaceholder">👤</span>
        </div>
        <label for="avatarUpload" style="cursor:pointer;color:var(--blue-main);font-weight:700;font-size:14px;">📷 {{ __('تغيير الصورة') }}</label>
        <span id="removeAvatarBtn" style="display:none;cursor:pointer;color:#b8232e;font-weight:700;font-size:14px;margin-right:15px;" onclick="removeAvatar()">🗑️ {{ __('حذف الصورة') }}</span>
        <input id="avatarUpload" type="file" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
    </div>

    <div class="loading" id="loadingProfile">{{ __('جاري التحميل...') }}</div>
    <div id="profileForm" style="display:none;">
        <div class="form-group"><label>{{ __('الاسم') }}</label><input id="editName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
        <div class="form-group"><label>{{ __('البريد الإلكتروني') }}</label><input id="editEmail" type="email" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
        <div class="form-group"><label>{{ __('رقم الجوال') }}</label><input id="editPhone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>

        <div id="teacherFields" style="display:none;">
            <hr style="margin:15px 0;"><h4 style="margin-bottom:10px;">👨‍🏫 {{ __('بيانات المعلم') }}</h4>
            <div class="form-group"><label>{{ __('المؤهل') }}</label><input id="editQualification" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div class="form-group"><label>{{ __('الاختصاص') }}</label><input id="editSpecialization" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div id="teacherReadonlyFields" style="display:none;">
                <div class="form-group"><label>{{ __('الراتب') }}</label><div id="displaySalary" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
                <div class="form-group"><label>{{ __('تاريخ التعيين') }}</label><div id="displayHireDate" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
            </div>
        </div>
        <div id="studentFields" style="display:none;">
            <hr style="margin:15px 0;"><h4 style="margin-bottom:10px;">🎓 {{ __('بيانات الطالب') }}</h4>
            <div class="form-group"><label>{{ __('تاريخ الميلاد') }}</label><input id="editDob" type="date" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div class="form-group"><label>{{ __('العنوان') }}</label><input id="editAddress" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div class="form-group"><label>{{ __('هاتف ولي الأمر') }}</label><input id="editGuardianPhone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div id="studentReadonlyFields" style="display:none;">
                <div class="form-group"><label>{{ __('الصف / الشعبة') }}</label><div id="displayClass" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
                <div class="form-group"><label>{{ __('الحالة الأكاديمية') }}</label><div id="displayStatus" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
                <div class="form-group"><label>{{ __('تاريخ الالتحاق') }}</label><div id="displayEnrollDate" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
            </div>
        </div>
        <div id="parentFields" style="display:none;">
            <hr style="margin:15px 0;"><h4 style="margin-bottom:10px;">👪 {{ __('بيانات ولي الأمر') }}</h4>
            <div class="form-group"><label>{{ __('المهنة') }}</label><input id="editOccupation" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div class="form-group"><label>{{ __('العنوان') }}</label><input id="editParentAddress" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
            <div id="parentReadonlyFields" style="display:none;">
                <div class="form-group"><label>{{ __('الأبناء المسجلون') }}</label><div id="displayChildren" style="width:100%;padding:10px;border:1px solid #eee;border-radius:8px;margin:5px 0 15px;background:#f9f9f9;color:#555;"></div></div>
            </div>
        </div>

        <hr style="margin:15px 0;">
        <h4 style="margin-bottom:10px;">🔑 {{ __('تغيير كلمة المرور') }}</h4>
        <div class="form-group"><label>{{ __('كلمة المرور الجديدة') }}</label><input id="editPassword" type="password" placeholder="{{ __('اتركه فارغاً إن لم ترد التغيير') }}" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px 0 15px;"></div>
        <button class="btn btn-primary" onclick="saveProfile()" style="width:100%;padding:12px;font-size:15px;">💾 {{ __('حفظ التغييرات') }}</button>
    </div>
    <div id="profileResult" style="margin-top:15px;"></div>
</div>

<script>
    let currentRole = '';
    function previewAvatar(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatarImg').src = ev.target.result;
            document.getElementById('avatarImg').style.display = '';
            document.getElementById('avatarPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
        document.getElementById('removeAvatarBtn').style.display = '';
    }

    let removeAvatarFlag = false;
    function removeAvatar() {
        if (!confirm(__('حذف الصورة الشخصية؟'))) return;
        removeAvatarFlag = true;
        document.getElementById('avatarImg').style.display = 'none';
        document.getElementById('avatarPlaceholder').style.display = '';
        document.getElementById('removeAvatarBtn').style.display = 'none';
        document.getElementById('avatarUpload').value = '';
    }

    apiFetch('/user').then(u => {
        currentRole = u.role;
        document.getElementById('loadingProfile').style.display = 'none';
        document.getElementById('profileForm').style.display = '';

        document.getElementById('editName').value = u.name || '';
        document.getElementById('editEmail').value = u.email || '';
        document.getElementById('editPhone').value = u.phone || '';

        if (u.avatar) {
            document.getElementById('avatarImg').src = '/storage/' + u.avatar;
            document.getElementById('avatarImg').style.display = '';
            document.getElementById('avatarPlaceholder').style.display = 'none';
            document.getElementById('removeAvatarBtn').style.display = '';
        }

        if (u.role === 'teacher' && u.teacher) {
            document.getElementById('teacherFields').style.display = '';
            document.getElementById('editQualification').value = u.teacher.qualification || '';
            document.getElementById('editSpecialization').value = u.teacher.specialization || '';
            document.getElementById('teacherReadonlyFields').style.display = '';
            document.getElementById('displaySalary').textContent = u.teacher.salary ? toArabicNum(u.teacher.salary) + __(' دولار') : __('غير محدد');
            document.getElementById('displayHireDate').textContent = u.teacher.hire_date || __('غير محدد');
        }
        if (u.role === 'student' && u.student) {
            document.getElementById('studentFields').style.display = '';
            document.getElementById('editDob').value = u.student.dob || '';
            document.getElementById('editAddress').value = u.student.address || '';
            document.getElementById('editGuardianPhone').value = u.student.guardian_phone || '';
            document.getElementById('studentReadonlyFields').style.display = '';
            document.getElementById('displayClass').textContent = u.student.class?.name || u.student.class_id || __('غير محدد');
            const statusMap = { active: __('نشط'), inactive: __('غير نشط') };
            document.getElementById('displayStatus').textContent = statusMap[u.student.status] || u.student.status || __('غير محدد');
            document.getElementById('displayEnrollDate').textContent = u.student.enrollment_date || __('غير محدد');
        }
        if (u.role === 'parent' && u.parent) {
            document.getElementById('parentFields').style.display = '';
            document.getElementById('editOccupation').value = u.parent.occupation || '';
            document.getElementById('editParentAddress').value = u.parent.address || '';
            document.getElementById('parentReadonlyFields').style.display = '';
            const children = (u.parent?.children || []).map(c => c.user?.name || c.user?.email || __('غير معروف')).join('، ') || __('لا يوجد');
            document.getElementById('displayChildren').textContent = children;
        }
    }).catch(e => {
        document.getElementById('loadingProfile').textContent = __('❌ تعذر تحميل البيانات: ') + (e.message || __('خطأ'));
    });

    function saveProfile() {
        const btn = document.querySelector('.btn-primary');
        btn.textContent = __('⏳ جاري الحفظ...');
        btn.disabled = true;

        const fd = new FormData();
        fd.append('_method', 'PUT');

        const name = document.getElementById('editName').value.trim();
        const email = document.getElementById('editEmail').value.trim();
        const phone = document.getElementById('editPhone').value.trim();
        const password = document.getElementById('editPassword').value;
        const avatar = document.getElementById('avatarUpload').files[0];

        if (name) fd.append('name', name);
        if (email) fd.append('email', email);
        if (phone) fd.append('phone', phone);
        if (password) fd.append('password', password);
        if (avatar) fd.append('avatar', avatar);

        if (currentRole === 'teacher') {
            const q = document.getElementById('editQualification').value;
            const s = document.getElementById('editSpecialization').value;
            if (q) fd.append('qualification', q);
            if (s) fd.append('specialization', s);
        }
        if (currentRole === 'student') {
            const d = document.getElementById('editDob').value;
            const a = document.getElementById('editAddress').value;
            const g = document.getElementById('editGuardianPhone').value;
            if (d) fd.append('dob', d);
            if (a) fd.append('address', a);
            if (g) fd.append('guardian_phone', g);
        }
        if (currentRole === 'parent') {
            const o = document.getElementById('editOccupation').value;
            const a = document.getElementById('editParentAddress').value;
            if (o) fd.append('occupation', o);
            if (a) fd.append('address', a);
        }

        if (removeAvatarFlag) fd.append('remove_avatar', '1');
        removeAvatarFlag = false;

        fetch('/api/user/profile', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token'), 'Accept': 'application/json' },
            body: fd,
        }).then(r => r.json()).then(d => {
            btn.textContent = __('💾 حفظ التغييرات');
            btn.disabled = false;
            const updatedUser = d.user || d;
            if (updatedUser && updatedUser.id) {
                localStorage.setItem('user', JSON.stringify(updatedUser));
                document.getElementById('userName').textContent = updatedUser.name || '';
                document.getElementById('editPassword').value = '';
                if (updatedUser.avatar) {
                    document.getElementById('avatarImg').src = '/storage/' + updatedUser.avatar;
                    document.getElementById('avatarImg').style.display = '';
                    document.getElementById('avatarPlaceholder').style.display = 'none';
                    document.getElementById('removeAvatarBtn').style.display = '';
                    document.getElementById('avatarHeaderImg').src = '/storage/' + updatedUser.avatar;
                    document.getElementById('avatarHeaderImg').style.display = '';
                    document.getElementById('avatarHeaderLetter').style.display = 'none';
                } else {
                    document.getElementById('avatarHeaderImg').style.display = 'none';
                    document.getElementById('avatarHeaderLetter').style.display = '';
                    document.getElementById('avatarHeaderLetter').textContent = (updatedUser.name || '?')[0];
                }
                showToast('✅ ' + (d.message || __('تم الحفظ')));
            } else {
                showToast('❌ ' + (d.message || __('خطأ في الحفظ')), 'error');
            }
        });
    }
</script>
<style>
    .form-group label { font-weight: 700; font-size: 14px; color: var(--text-dark); display:block; margin-bottom:4px; }
    #avatarUpload::-webkit-file-upload-button { display:none; }
</style>
@stop
