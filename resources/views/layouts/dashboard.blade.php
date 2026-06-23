<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ __('لوحة التحكم') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0d1117; --dark-nav: #131a27; --dark-card: #1a2235;
            --blue-main: #2d5be3; --blue-light: #4a7ff5; --blue-text: #5b8ef0;
            --white: #fff; --gray-light: #f4f6fa; --gray-text: #8a96a8; --text-dark: #1a2235;
            --sidebar-width: 250px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: var(--gray-light); display: flex; min-height: 100vh; }
        body.en { font-family: 'Segoe UI', Tahoma, sans-serif; }
        .sidebar {
            width: var(--sidebar-width); background: var(--dark-nav); color: var(--white);
            padding: 20px; display: flex; flex-direction: column; position: fixed; top: 0; height: 100vh; z-index: 100;
        }
        [dir="rtl"] .sidebar { right: 0; }
        [dir="ltr"] .sidebar { left: 0; }
        .sidebar .logo { font-size: 18px; font-weight: 900; margin-bottom: 30px; text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .logo::before { content: "\2022"; color: var(--blue-main); margin-left: 6px; }
        .sidebar a {
            display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: var(--gray-text);
            text-decoration: none; border-radius: 10px; margin-bottom: 5px; transition: all 0.3s; font-size: 14px; font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active { background: rgba(45,91,227,0.15); color: var(--blue-text); }
        .sidebar a.active { border-color: var(--blue-main); }
        [dir="rtl"] .sidebar a { border-right: 3px solid transparent; }
        [dir="ltr"] .sidebar a { border-left: 3px solid transparent; }
        [dir="rtl"] .sidebar a:hover, [dir="rtl"] .sidebar a.active { border-right-color: var(--blue-main); }
        [dir="ltr"] .sidebar a:hover, [dir="ltr"] .sidebar a.active { border-left-color: var(--blue-main); }
        .sidebar .logout { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
        .main { flex: 1; padding: 30px; min-height: 100vh; }
        [dir="rtl"] .main { margin-right: var(--sidebar-width); }
        [dir="ltr"] .main { margin-left: var(--sidebar-width); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; font-weight: 800; color: var(--text-dark); }
        .user-badge { display: flex; align-items: center; gap: 10px; background: var(--white); padding: 8px 16px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .user-badge .name { font-weight: 700; font-size: 14px; color: var(--text-dark); }
        .user-badge .role { font-size: 12px; color: var(--gray-text); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); border-radius: 14px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card .number { font-size: 28px; font-weight: 900; color: var(--blue-main); }
        .stat-card .label { font-size: 13px; color: var(--gray-text); margin-top: 5px; }
        .card { background: var(--white); border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card h3 { font-size: 16px; font-weight: 800; color: var(--text-dark); margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { padding: 12px 10px; background: var(--gray-light); color: var(--text-dark); font-weight: 700; }
        [dir="rtl"] th { text-align: right; }
        [dir="ltr"] th { text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; color: #555; }
        .badge { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 700; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--blue-main); color: var(--white); }
        .btn-primary:hover { background: var(--blue-light); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .loading { text-align: center; padding: 40px; color: var(--gray-text); }
        .empty { text-align: center; padding: 40px; color: var(--gray-text); }

        .toast-container { position: fixed; bottom: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        [dir="rtl"] .toast-container { left: 20px; }
        [dir="ltr"] .toast-container { right: 20px; }
        .toast { padding: 14px 20px; border-radius: 10px; color: #fff; font-weight: 700; font-size: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); animation: toastIn 0.3s ease; min-width: 250px; }
        .toast-success { background: #155724; }
        .toast-error { background: #721c24; }
        .toast-info { background: var(--blue-main); }
        @keyframes toastIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        input, select, textarea { font-family: inherit; }
        .card { transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        table tbody tr:hover { background: #f0f4ff !important; }
        .btn { transition: all 0.2s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .stat-card { transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 25px rgba(0,0,0,0.1); }
        .sidebar a { transition: all 0.2s; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar .logo { font-size: 0; }
            .sidebar .logo::before { font-size: 20px; }
            .sidebar a span { display: none; }
            .main { margin-right: 60px; }
        }
    </style>
</head>
<body class="{{ app()->getLocale() === 'en' ? 'en' : '' }}">
    <div class="sidebar">
        <div class="logo">{{ __('الإبداع الحديثة') }}</div>
        @yield('sidebar')
        <a href="/profile">👤 <span>{{ __('الملف الشخصي') }}</span></a>
        <a href="#" class="logout" onclick="logout()">🚪 <span>{{ __('تسجيل خروج') }}</span></a>
    </div>
    <div class="main">
        <div class="header">
            <h1>@yield('page-title')</h1>
            <div style="display:flex;align-items:center;gap:15px;">
                <div onclick="toggleLang()" style="cursor:pointer;font-size:16px;font-weight:700;width:40px;height:40px;background:var(--white);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);color:var(--blue-main);">{{ app()->getLocale() === 'en' ? 'ع' : 'EN' }}</div>
                <div id="bellIcon" onclick="toggleNotifications()" style="position:relative;cursor:pointer;font-size:22px;width:40px;height:40px;background:var(--white);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    🔔
                    <span id="notifBadge" style="position:absolute;top:-2px;{{ app()->getLocale() === 'en' ? 'left' : 'right' }}:-2px;background:#dc3545;color:#fff;font-size:10px;padding:2px 6px;border-radius:50%;display:none;">0</span>
                </div>
                <div id="notifPanel" style="display:none;position:absolute;top:55px;{{ app()->getLocale() === 'en' ? 'right' : 'left' }}:30px;width:350px;max-height:400px;overflow-y:auto;background:var(--white);border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:999;padding:10px;"></div>
                <div class="user-badge" style="gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div id="userAvatar" style="width:38px;height:38px;border-radius:50%;background:var(--blue-main);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:900;overflow:hidden;flex-shrink:0;">
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
