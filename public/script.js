const API_BASE = window.location.pathname.startsWith('/School-project') ? '/School-project/api' : '/api';

function showToast(msg, type) {
    const el = document.createElement('div');
    el.style.cssText = 'position:fixed;bottom:20px;left:20px;background:' + (type === 'error' ? '#721c24' : '#155724') + ';color:#fff;padding:14px 20px;border-radius:10px;font-weight:700;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:toastIn 0.3s ease;';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = '0.3s'; setTimeout(() => el.remove(), 300); }, 3000);
}
const styleToast = document.createElement('style');
styleToast.textContent = '@keyframes toastIn{from{transform:translateX(100px);opacity:0}to{transform:translateX(0);opacity:1}}';
document.head.appendChild(styleToast);

async function apiRequest(url, options = {}) {
  const token = localStorage.getItem('token');
  const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
  if (token) headers['Authorization'] = 'Bearer ' + token;

  try {
    const res = await fetch(API_BASE + url, { ...options, headers });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || data.errors?.[Object.keys(data.errors)[0]]?.[0] || __('حدث خطأ'));
    return data;
  } catch (err) {
    throw err;
  }
}

function openModal() {
  document.getElementById('modal').classList.add('open');
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
}

document.getElementById('modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

const gradeAgeRanges = {
  'الصف الأول':  { min: 5.5, max: 7.5, label: __('6-7 سنوات') },
  'الصف الثاني': { min: 6.5, max: 8.5, label: __('7-8 سنوات') },
  'الصف الثالث': { min: 7.5, max: 9.5, label: __('8-9 سنوات') },
  'الصف الرابع': { min: 8.5, max: 10.5, label: __('9-10 سنوات') },
  'الصف الخامس': { min: 9.5, max: 11.5, label: __('10-11 سنوات') },
};

async function submitEnrollment() {
  const guardianName  = document.getElementById('enroll-guardian-name').value.trim();
  const guardianEmail = document.getElementById('enroll-guardian-email').value.trim();
  const guardianPhone = document.getElementById('enroll-guardian-phone').value.trim();
  const studentName   = document.getElementById('enroll-student-name').value.trim();
  const studentDob    = document.getElementById('enroll-student-dob').value.trim();
  const stage         = document.getElementById('enroll-stage').value.trim();
  const errorEl       = document.getElementById('enroll-error');

  errorEl.style.display = 'none';
  errorEl.textContent   = '';

  if (!guardianName || !guardianEmail || !guardianPhone || !studentName || !studentDob) {
    errorEl.textContent = '⚠️ ' + __('يرجى ملء جميع الحقول الأساسية.');
    errorEl.style.display = 'block';
    return;
  }

  if (stage && studentDob) {
    const range = gradeAgeRanges[stage];
    if (range) {
      const birth = new Date(studentDob);
      const today = new Date();
      let ageYears = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        ageYears--;
      }
      const age = ageYears + (monthDiff < 0 ? 12 + monthDiff : monthDiff) / 12;
      if (age < range.min || age > range.max) {
        errorEl.textContent = `⚠️ ${__('عمر الطالب')} (${ageYears} ${__('سنة')}) ${__('لا يتناسب مع')} ${stage} (${__('المناسب:')} ${range.label}).`;
        errorEl.style.display = 'block';
        return;
      }
    }
  }

  try {
    const data = await apiRequest('/enrollments', {
      method: 'POST',
      body: JSON.stringify({
        guardian_name: guardianName,
        guardian_email: guardianEmail,
        guardian_phone: guardianPhone,
        student_name: studentName,
        student_dob: studentDob,
        stage: stage || null,
      }),
    });
    showToast(data.message);
    closeModal();
  } catch (err) {
    errorEl.textContent = '⚠️ ' + err.message;
    errorEl.style.display = 'block';
  }
}

async function submitContact() {
  const name    = document.getElementById('contact-name')?.value?.trim();
  const email   = document.getElementById('contact-email')?.value?.trim();
  const message = document.getElementById('contact-message')?.value?.trim();

  if (!name || !email || !message) {
    showToast('⚠️ ' + __('يرجى ملء جميع الحقول'), 'error');
    return;
  }

  try {
    const data = await apiRequest('/contact', {
      method: 'POST',
      body: JSON.stringify({ name, email, message }),
    });
    showToast(data.message);
  } catch (err) {
    showToast('⚠️ ' + err.message, 'error');
  }
}

function openLogin() {
  document.getElementById('loginScreen').classList.add('open');
  document.getElementById('roleBox').style.display = 'block';
  document.getElementById('loginForm').classList.remove('show');
  document.getElementById('login-error').style.display = 'none';
}

function closeLogin() {
  document.getElementById('loginScreen').classList.remove('open');
}

let selectedRole = '';

function selectRole(roleName, card) {
  selectedRole = roleName;
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
  setTimeout(function() {
    document.getElementById('roleBox').style.display = 'none';
    document.getElementById('loginTitle').textContent = __('تسجيل دخول كـ') + ' ' + __(roleName);
    document.getElementById('loginForm').classList.add('show');
    document.getElementById('login-error').style.display = 'none';
  }, 300);
}

function goBack() {
  document.getElementById('loginForm').classList.remove('show');
  document.getElementById('roleBox').style.display = 'block';
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
}

async function submitLogin() {
  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  const errorEl = document.getElementById('login-error');

  const roleMap = { 'أدمن': 'admin', 'معلم': 'teacher', 'طالب': 'student', 'ولي أمر': 'parent' };
  const role = roleMap[selectedRole] || selectedRole;

  errorEl.style.display = 'none';

  if (!email || !password) {
    errorEl.textContent = '⚠️ ' + __('يرجى إدخال البريد الإلكتروني وكلمة المرور');
    errorEl.style.display = 'block';
    return;
  }

  try {
    const data = await apiRequest('/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, role }),
    });
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
    showToast(__('تم تسجيل الدخول بنجاح!'));

    const userRole = data.user.role;
    const urls = { admin: '/admin', teacher: '/teacher', student: '/student', parent: '/parent' };
    window.location.href = urls[userRole] || '/';
  } catch (err) {
    errorEl.textContent = '⚠️ ' + err.message;
    errorEl.style.display = 'block';
  }
}

function logout() {
  const token = localStorage.getItem('token');
  if (token) {
    apiRequest('/logout', { method: 'POST' }).catch(() => {});
  }
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  window.location.href = '/';
}

document.getElementById('loginScreen').addEventListener('click', function(e) {
  if (e.target === this) closeLogin();
});

window.addEventListener('scroll', function() {
  const btn = document.getElementById('scrollTop');
  if (btn) btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
});
