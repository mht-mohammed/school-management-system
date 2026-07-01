<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ __('لوحة التحكم') }}</title>
    @if($schoolSettings->school_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $schoolSettings->school_logo) }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏫</text></svg>">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #080b14; --dark-nav: #0c1120; --dark-card: #141b2c;
            --indigo: #4338ca; --blue-main: #3357e8; --blue-light: #5b7cf7; --blue-text: #93acff;
            --violet: #7c3aed;
            --gold: #d4af5e; --gold-light: #f0d394;
            --blue-50: #eef1fd;
            --white: #fff; --gray-light: #f2f4fa; --gray-text: #8a93a8; --text-dark: #131a2c;
            --border-soft: #e7eaf3;
            --sidebar-width: 276px;
            --radius-lg: 20px; --radius-md: 14px; --radius-sm: 10px;
            --shadow-sm: 0 1px 3px rgba(19,26,44,0.06), 0 1px 2px rgba(19,26,44,0.04);
            --shadow-md: 0 10px 28px rgba(51,87,232,0.08);
            --shadow-lg: 0 24px 60px rgba(19,26,44,0.2);
            --shadow-glow: 0 8px 22px rgba(51,87,232,0.28);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif; -webkit-font-smoothing: antialiased;
            background:
                radial-gradient(1100px 560px at 105% -12%, rgba(51,87,232,0.10), transparent 60%),
                radial-gradient(760px 420px at -8% 8%, rgba(124,58,237,0.06), transparent 55%),
                radial-gradient(640px 380px at 50% 115%, rgba(212,175,94,0.06), transparent 55%),
                var(--gray-light);
            display: flex; min-height: 100vh; color: var(--text-dark); letter-spacing: 0.1px;
        }
        body.en { font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif; }
        .sidebar {
            width: var(--sidebar-width);
            background:
                radial-gradient(480px 260px at 105% 0%, rgba(212,175,94,0.12), transparent 60%),
                radial-gradient(420px 300px at -10% 100%, rgba(124,58,237,0.14), transparent 60%),
                linear-gradient(180deg, var(--dark-nav) 0%, var(--dark-bg) 100%);
            color: var(--white);
            padding: 22px 16px; display: flex; flex-direction: column; position: fixed; top: 0; height: 100vh; z-index: 100;
            overflow-y: auto; box-shadow: 8px 0 36px rgba(0,0,0,0.3);
        }
        [dir="rtl"] .sidebar { right: 0; border-left: 1px solid rgba(255,255,255,0.05); }
        [dir="ltr"] .sidebar { left: 0; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar .logo {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            margin-bottom: 26px; padding: 28px 10px 24px;
            border: 1px solid rgba(212,175,94,0.25);
            background: linear-gradient(160deg, rgba(212,175,94,0.12) 0%, rgba(51,87,232,0.10) 100%);
            border-radius: var(--radius-lg);
            position: relative;
        }
        .sidebar .logo::after {
            content: ''; position: absolute; bottom: 0; left: 16%; right: 16%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,175,94,0.6), transparent);
        }
        .sidebar .logo-img {
            width: 76px; height: 76px; border-radius: 20px; object-fit: contain;
            border: 2px solid rgba(212,175,94,0.4); padding: 4px;
            background: rgba(255,255,255,0.97);
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
            margin-bottom: 15px;
        }
        .sidebar .logo-img-placeholder {
            width: 76px; height: 76px; border-radius: 20px;
            background: linear-gradient(135deg, var(--violet), var(--blue-main));
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin-bottom: 15px;
            box-shadow: 0 12px 30px rgba(51,87,232,0.45);
            border: 2px solid rgba(212,175,94,0.4);
        }
        .sidebar .logo-text {
            font-size: 17px; font-weight: 900; color: var(--white);
            text-align: center; line-height: 1.35; letter-spacing: -0.1px;
        }
        .sidebar a {
            display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #97a1b8;
            text-decoration: none; border-radius: var(--radius-sm); margin-bottom: 4px; transition: all 0.25s ease; font-size: 13.5px; font-weight: 600;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.06); color: #eef1fb; transform: translateX(-2px); }
        [dir="ltr"] .sidebar a:hover { transform: translateX(2px); }
        .sidebar a.active {
            background: linear-gradient(135deg, var(--violet), var(--blue-main));
            color: var(--white); font-weight: 800;
            box-shadow: 0 8px 22px rgba(67,56,202,0.45), inset 0 1px 0 rgba(255,255,255,0.18);
        }
        [dir="rtl"] .sidebar a { border-right: 3px solid transparent; }
        [dir="ltr"] .sidebar a { border-left: 3px solid transparent; }
        [dir="rtl"] .sidebar a.active { border-right-color: var(--gold-light); }
        [dir="ltr"] .sidebar a.active { border-left-color: var(--gold-light); }
        .sidebar .logout { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px; }
        .sidebar .logout a, .sidebar .logout { color: #d98a8a; }
        .main { flex: 1; padding: 32px 36px; min-height: 100vh; max-width: 100%; }
        [dir="rtl"] .main { margin-right: var(--sidebar-width); }
        [dir="ltr"] .main { margin-left: var(--sidebar-width); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .header h1 { font-size: 24px; font-weight: 900; color: var(--text-dark); letter-spacing: -0.5px; position: relative; padding-{{ app()->getLocale() === 'en' ? 'left' : 'right' }}: 16px; }
        .header h1::before {
            content: ''; position: absolute; {{ app()->getLocale() === 'en' ? 'left' : 'right' }}: 0; top: 4px; bottom: 4px; width: 4px;
            background: linear-gradient(180deg, var(--blue-main), var(--gold)); border-radius: 4px;
        }
        .user-badge { display: flex; align-items: center; gap: 10px; background: var(--white); padding: 6px 18px 6px 6px; border-radius: 50px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-soft); }
        .user-badge .name { font-weight: 800; font-size: 13.5px; color: var(--text-dark); }
        .user-badge .role { font-size: 11px; color: var(--gold); font-weight: 800; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 32px; }
        .stat-card {
            background: var(--white); border-radius: var(--radius-lg); padding: 24px 20px; text-align: center;
            box-shadow: var(--shadow-sm); border: 1px solid var(--border-soft); position: relative; overflow: hidden;
        }
        .stat-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:4px;
            background: linear-gradient(90deg, var(--violet), var(--blue-main), var(--gold));
        }
        .stat-card .number { font-size: 32px; font-weight: 900; color: var(--text-dark); letter-spacing: -0.7px; background: linear-gradient(135deg, var(--text-dark), #3a4566); -webkit-background-clip: text; background-clip: text; }
        .stat-card .label { font-size: 11.5px; color: var(--gray-text); margin-top: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
        .card {
            background: var(--white); border-radius: var(--radius-lg); padding: 26px;
            box-shadow: var(--shadow-sm); border: 1px solid var(--border-soft); margin-bottom: 22px;
        }
        .card h3 { font-size: 16.5px; font-weight: 800; color: var(--text-dark); margin-bottom: 18px; letter-spacing: -0.2px; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        th { padding: 13px 14px; background: linear-gradient(135deg, var(--blue-50), #f6f0e3); color: var(--text-dark); font-weight: 800; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px; }
        [dir="rtl"] th:first-child { border-top-right-radius: var(--radius-sm); border-bottom-right-radius: var(--radius-sm); }
        [dir="rtl"] th:last-child { border-top-left-radius: var(--radius-sm); border-bottom-left-radius: var(--radius-sm); }
        [dir="ltr"] th:first-child { border-top-left-radius: var(--radius-sm); border-bottom-left-radius: var(--radius-sm); }
        [dir="ltr"] th:last-child { border-top-right-radius: var(--radius-sm); border-bottom-right-radius: var(--radius-sm); }
        [dir="rtl"] th { text-align: right; }
        [dir="ltr"] th { text-align: left; }
        td { padding: 12px 14px; border-bottom: 1px solid var(--border-soft); color: #4b5468; }
        .badge { padding: 4px 14px 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.2px; }
        [dir="ltr"] .badge { padding: 4px 10px 4px 14px; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        .badge-success { background: #e1f7e7; color: #157a35; }
        .badge-warning { background: #fdf1d9; color: #93680c; }
        .badge-danger { background: #fde3e3; color: #b8232e; }
        .btn { padding: 10px 20px; border: none; border-radius: var(--radius-sm); font-family: inherit; font-size: 13px; font-weight: 800; cursor: pointer; }
        .btn-primary {
            background: linear-gradient(135deg, var(--violet), var(--blue-main));
            color: var(--white); box-shadow: var(--shadow-glow);
        }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-sm { padding: 7px 15px; font-size: 12px; }
        .loading { text-align: center; padding: 40px; color: var(--gray-text); font-size: 14px; }
        .empty { text-align: center; padding: 40px; color: var(--gray-text); font-size: 14px; }

        .toast-container { position: fixed; bottom: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        [dir="rtl"] .toast-container { left: 20px; }
        [dir="ltr"] .toast-container { right: 20px; }
        .toast {
            padding: 14px 20px; border-radius: var(--radius-md); color: #fff; font-weight: 700; font-size: 14px;
            box-shadow: var(--shadow-lg); animation: toastIn 0.3s ease; min-width: 250px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .toast-success { background: #157a35; }
        .toast-error { background: #b8232e; }
        .toast-info { background: var(--blue-main); }
        .toast-warning { background: #93680c; }
        @keyframes toastIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        input, select, textarea { font-family: inherit; }
        .card { transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
        .card:hover { box-shadow: var(--shadow-md); border-color: #dce1f5; }
        table tbody tr { transition: background 0.15s ease; }
        table tbody tr:hover { background: var(--blue-50) !important; }
        .btn { transition: all 0.2s ease; }
        .btn:hover { transform: translateY(-1px); }
        .stat-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .sidebar a { transition: all 0.2s ease; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c7cedb; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a7b0c1; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); }
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 12px 8px; }
            .sidebar .logo { font-size: 0; padding: 14px 4px; }
            .sidebar .logo::before { font-size: 20px; }
            .sidebar a span { display: none; }
            .sidebar a { justify-content: center; padding: 12px; }
            [dir="rtl"] .main { margin-right: 60px; }
            [dir="ltr"] .main { margin-left: 60px; }
            .main { padding: 20px 16px; }
        }
    </style>
</head>
<body class="{{ app()->getLocale() === 'en' ? 'en' : '' }}">
    <div class="sidebar">
        <div class="logo">
            @if($schoolSettings->school_logo)
                <img src="{{ asset('storage/' . $schoolSettings->school_logo) }}" alt="Logo" class="logo-img">
            @else
                <div class="logo-img-placeholder">🏫</div>
            @endif
            <span class="logo-text">{{ $schoolSettings->school_name }}</span>
        </div>
        @if(trim($__env->yieldContent('sidebar')))
            @yield('sidebar')
        @else
            <div id="sidebarFallback"></div>
            <script>
                (function(){
                    var u = JSON.parse(localStorage.getItem('user') || '{}');
                    var role = u.role || '';
                    var links = {
                        admin: [
                            '<a href="/admin">📊 <span>{{ __('الإحصائيات') }}</span></a>',
                            '<a href="/admin/enrollments">📋 <span>{{ __('طلبات الالتحاق') }}</span></a>',
                            '<a href="/admin/messages">✉️ <span>{{ __('رسائل التواصل') }}</span></a>',
                            '<a href="/admin/students">🎓 <span>{{ __('الطلاب') }}</span></a>',
                            '<a href="/admin/teachers">👨‍🏫 <span>{{ __('المعلمون') }}</span></a>',
                            '<a href="/admin/classes">🏫 <span>{{ __('الصفوف') }}</span></a>',
                            '<a href="/admin/subjects">📚 <span>{{ __('المواد') }}</span></a>',
                            '<a href="/admin/schedules">📅 <span>{{ __('الجداول') }}</span></a>',
                            '<a href="/admin/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>',
                            '<a href="/admin/library">📖 <span>{{ __('المكتبة') }}</span></a>',
                            '<a href="/admin/parents">👪 <span>{{ __('أولياء الأمور') }}</span></a>',
                            '<a href="/admin/grades-report">📊 <span>{{ __('تقرير الدرجات') }}</span></a>',
                            '<a href="/admin/attendance-report">📋 <span>{{ __('تقرير الحضور') }}</span></a>',
                            '<a href="/admin/profile-requests">🔄 <span>{{ __('طلبات التعديل') }}</span></a>',
                            '<a href="/admin/settings">⚙️ <span>{{ __('إعدادات المدرسة') }}</span></a>',
                        ],
                        teacher: [
                            '<a href="/teacher">📊 <span>{{ __('لوحتي') }}</span></a>',
                            '<a href="/teacher/grades">📝 <span>{{ __('الدرجات') }}</span></a>',
                            '<a href="/teacher/schedule">📅 <span>{{ __('جدولي') }}</span></a>',
                            '<a href="/teacher/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>',
                            '<a href="/teacher/library">📖 <span>{{ __('المكتبة') }}</span></a>',
                        ],
                        student: [
                            '<a href="/student">📊 <span>{{ __('لوحتي') }}</span></a>',
                            '<a href="/student/e-learning">💻 <span>{{ __('التعلم الإلكتروني') }}</span></a>',
                            '<a href="/student/library">📖 <span>{{ __('المكتبة') }}</span></a>',
                        ],
                        parent: [
                            '<a href="/parent">👪 <span>{{ __('أبنائي') }}</span></a>',
                        ],
                    };
                    var html = (links[role] || []).join('');
                    document.getElementById('sidebarFallback').innerHTML = html;
                })();
            </script>
        @endif
        <a href="/profile">👤 <span>{{ __('الملف الشخصي') }}</span></a>
        <a href="#" class="logout" onclick="logout()">🚪 <span>{{ __('تسجيل خروج') }}</span></a>
        <script>
            (function(){
                var path = window.location.pathname.replace(/\?.*$/, '');
                document.querySelectorAll('.sidebar a[href]').forEach(function(a) {
                    var href = a.getAttribute('href').replace(/\?.*$/, '');
                    if (href !== '#' && path === href) {
                        a.classList.add('active');
                    }
                });
            })();
        </script>
    </div>
    <div class="main">
        <div class="header">
            <h1>@yield('page-title')</h1>
            <div style="display:flex;align-items:center;gap:12px;">
                <div onclick="toggleLang()" style="cursor:pointer;font-size:14px;font-weight:800;width:40px;height:40px;background:var(--white);border:1px solid var(--border-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);color:var(--blue-main);transition:all .2s;">{{ app()->getLocale() === 'en' ? 'ع' : 'EN' }}</div>
                <div id="bellIcon" onclick="toggleNotifications()" style="position:relative;cursor:pointer;font-size:19px;width:40px;height:40px;background:var(--white);border:1px solid var(--border-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-sm);transition:all .2s;">
                    🔔
                    <span id="notifBadge" style="position:absolute;top:-3px;{{ app()->getLocale() === 'en' ? 'left' : 'right' }}:-3px;background:#dc3545;color:#fff;font-size:10px;padding:2px 6px;border-radius:50%;display:none;box-shadow:0 0 0 2px var(--white);">0</span>
                </div>
                <div id="notifPanel" style="display:none;position:absolute;top:55px;{{ app()->getLocale() === 'en' ? 'right' : 'left' }}:30px;width:350px;max-height:400px;overflow-y:auto;background:var(--white);border-radius:var(--radius-md);box-shadow:var(--shadow-lg);border:1px solid var(--border-soft);z-index:999;padding:10px;"></div>
                <div class="user-badge" style="gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div id="userAvatar" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--blue-main),var(--blue-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;overflow:hidden;flex-shrink:0;">
                            <img id="avatarHeaderImg" style="width:100%;height:100%;object-fit:cover;display:none;">
                            <span id="avatarHeaderLetter"></span>
                        </div>
                        <div><div class="name" id="userName"></div><div class="role" id="userRole"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="notifBackdrop" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:998;background:transparent;" onclick="closeNotifications()"></div>
        <div class="toast-container" id="toastContainer"></div>

        <script>
            var LOCALE = '{{ app()->getLocale() }}';
            var API_BASE = window.location.pathname.startsWith('/School-project') ? '/School-project/api' : '/api';
            var token = localStorage.getItem('token');
            var user = JSON.parse(localStorage.getItem('user') || '{}');

            var trans = {
                ar: @json(require(resource_path('lang/ar/validation.php'))),
                en: {}
            };

            @if (File::exists(resource_path('lang/en.json')))
                trans.en = @json(json_decode(File::get(resource_path('lang/en.json')), true) ?? []);
            @endif

            function __(text) {
                if (LOCALE === 'en' && trans.en[text]) return trans.en[text];
                return text;
            }

            function toArabicNum(n) {
                if (LOCALE !== 'ar' || n === null || n === undefined) return n;
                var d = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                return String(n).replace(/[0-9]/g, function(c) { return d[c]; });
            }

            function setNum(id, val) {
                var el = document.getElementById(id);
                if (el) el.textContent = toArabicNum(val);
            }

            function sectionLabel(letter) {
                if (!letter) return '—';
                if (LOCALE === 'en') {
                    var map = { 'أ': 'A', 'ب': 'B', 'ج': 'C', 'د': 'D' };
                    return map[letter] || letter;
                }
                return letter;
            }

            function subjectName(name) {
                if (!name) return '—';
                if (LOCALE === 'en') {
                    var map = {
                        'الرياضيات': 'Mathematics',
                        'العلوم الحياتية': 'Life Sciences',
                        'اللغة العربية': 'Arabic Language',
                        'اللغة الإنجليزية': 'English Language',
                        'التربية الإسلامية': 'Islamic Education',
                        'الدراسات الاجتماعية': 'Social Studies',
                        'التكنولوجيا والحاسوب': 'Technology & Computer',
                        'التربية الرياضية': 'Physical Education'
                    };
                    return map[name] || name;
                }
                return name;
            }

            if (!token) window.location.href = '/';
            document.getElementById('userName').textContent = user.name || '';
            document.getElementById('userRole').textContent = user.role || '';
            if (user.avatar) { document.getElementById('avatarHeaderImg').src = '/storage/' + user.avatar; document.getElementById('avatarHeaderImg').style.display = ''; document.getElementById('avatarHeaderLetter').style.display = 'none'; } else { document.getElementById('avatarHeaderLetter').textContent = (user.name || '?')[0]; }

            function showToast(message, type, duration) {
                type = type || 'success';
                duration = duration || (type === 'info' ? 15000 : 8000);
                var container = document.getElementById('toastContainer');
                var toast = document.createElement('div');
                toast.className = 'toast toast-' + type;
                toast.style.whiteSpace = 'pre-line';
                toast.innerHTML = message;
                container.appendChild(toast);
                setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = '0.3s'; setTimeout(function() { toast.remove(); }, 300); }, duration);
            }

            async function apiFetch(url, opts) {
                opts = opts || {};
                var headers = { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token };
                if (!(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
                var fetchUrl = API_BASE + url;
                var sep = fetchUrl.indexOf('?') > -1 ? '&' : '?';
                if (LOCALE !== 'ar') fetchUrl += sep + 'lang=' + LOCALE;
                var res = await fetch(fetchUrl, Object.assign({}, opts, { headers: headers }));
                if (res.status === 401 || res.status === 403) {
                    localStorage.removeItem('token'); localStorage.removeItem('user');
                    window.location.href = '/'; return;
                }
                var data = await res.json();
                if (!res.ok) throw { status: res.status, message: data.message || __('خطأ في الطلب'), errors: data.errors };
                return data;
            }

            function logout() {
                apiFetch('/logout', { method: 'POST' }).catch(function() {});
                localStorage.removeItem('token'); localStorage.removeItem('user');
                window.location.href = '/';
            }

            function toggleLang() {
                var next = LOCALE === 'ar' ? 'en' : 'ar';
                document.cookie = 'lang=' + next + ';path=/;max-age=' + (365*24*60*60);
                var url = new URL(window.location.href);
                url.searchParams.set('lang', next);
                window.location.href = url.toString();
            }

            // --- Notifications ---
            var notifOpen = false;
            function loadNotifBadge() {
                apiFetch('/notifications/unread-count').then(function(d) {
                    var badge = document.getElementById('notifBadge');
                    if (d.count > 0) { badge.textContent = d.count; badge.style.display = ''; }
                    else { badge.style.display = 'none'; }
                }).catch(function() {});
            }
            function toggleNotifications() {
                if (notifOpen) { closeNotifications(); return; }
                notifOpen = true;
                document.getElementById('notifBackdrop').style.display = '';
                apiFetch('/notifications').then(function(list) {
                    var panel = document.getElementById('notifPanel');
                    if (!list || !list.length) {
                        panel.innerHTML = '<div style="padding:15px;text-align:center;color:#888;">' + __('لا توجد إشعارات') + '</div>';
                    } else {
                        var html = '<div style="display:flex;justify-content:space-between;margin-bottom:8px;"><strong>' + __('الإشعارات') + '</strong><button onclick="markAllRead()" style="background:none;border:none;color:var(--blue-main);font-size:12px;cursor:pointer;">' + __('تحديد الكل مقروء') + '</button></div>';
                        list.forEach(function(n) {
                            var clickAction = "markRead(" + n.id + ")";
                            if (n.type === 'profile_change') clickAction = "markRead(" + n.id + ");window.location.href='/admin/profile-requests'";
                            else if (n.type === 'profile_change_approved' || n.type === 'profile_change_rejected') clickAction = "markRead(" + n.id + ");window.location.href='/profile'";
                            html += '<div style="padding:10px;border-bottom:1px solid #eee;cursor:pointer;' + (!n.is_read ? 'background:#f0f4ff;' : '') + '" onclick="' + clickAction + '">';
                            html += '<div style="font-size:13px;font-weight:700;">' + n.title + '</div>';
                            html += '<div style="font-size:12px;color:#666;margin-top:3px;">' + n.message + '</div>';
                            html += '<div style="font-size:11px;color:#999;margin-top:3px;">' + new Date(n.created_at).toLocaleDateString(LOCALE === 'en' ? 'en' : 'ar') + '</div>';
                            html += '</div>';
                        });
                        panel.innerHTML = html;
                    }
                    panel.style.display = '';
                    loadNotifBadge();
                });
            }
            function closeNotifications() {
                notifOpen = false;
                document.getElementById('notifPanel').style.display = 'none';
                document.getElementById('notifBackdrop').style.display = 'none';
            }
            function markRead(id) {
                apiFetch('/notifications/' + id + '/read', { method: 'PUT' }).then(function() { loadNotifBadge(); });
            }
            function markAllRead() {
                apiFetch('/notifications/read-all', { method: 'PUT' }).then(function() { closeNotifications(); loadNotifBadge(); });
            }
            loadNotifBadge();

        document.querySelectorAll('.sidebar a[href]').forEach(function(a) {
            var href = a.getAttribute('href');
            if (href && href !== '#' && !href.includes('?lang=')) {
                a.setAttribute('href', href + '?lang=' + LOCALE);
            }
        });
        </script>

        @yield('content')
    </div>
</body>
</html>
