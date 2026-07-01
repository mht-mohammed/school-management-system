var components = {};

components["comp-nav"] = `
<style>

nav {
  position: fixed;
  top: 0;
  width: 100%;
  height: 65px;
  background-color: var(--dark-nav);
  border-bottom: 1px solid rgba(255,255,255,0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px;
  z-index: 1000;
}
.logo {
  font-size: 20px;
  font-weight: 800;
  color: var(--white);
  display: flex;
  align-items: center;
  gap: 8px;
}
.logo::before {
  content: "\u2022";
  color: var(--blue-main);
  font-size: 22px;
}
.nav-links {
  display: flex;
  align-items: center;
  gap: 30px;
  list-style: none;
}
.nav-links a {
  color: var(--white);
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  transition: color 0.3s;
}
.nav-links a:hover { color: var(--blue-text); }
.btn-login {
  background-color: var(--blue-main);
  color: var(--white);
  border: none;
  padding: 10px 22px;
  border-radius: 8px;
  font-family: 'Tajawal', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.3s;
}
.btn-login:hover { background-color: var(--blue-light); }
</style>

<nav>
  <div class="logo">${__('الإبداع الحديثة')}</div>
  <ul class="nav-links">
    <li><a href="#contact">${__('تواصل معنا')}</a></li>
    <li><a href="#stages">${__('المراحل الدراسية')}</a></li>
    <li><a href="#services">${__('الخدمات')}</a></li>
    <li><a href="#about">${__('عن المدرسة')}</a></li>
    <li><a href="#home">${__('الرئيسية')}</a></li>
  </ul>
  <button class="btn-login" onclick="openLogin()">${__('تسجيل دخول')}</button>
</nav>

`;

components["comp-hero"] = `
<style>

#home {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  background: linear-gradient(160deg, #0d1117 0%, #131a27 50%, #0d1117 100%);
  padding: 100px 20px 60px;
}
.badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(45,91,227,0.15);
  border: 1px solid rgba(45,91,227,0.3);
  color: var(--blue-text);
  padding: 7px 18px;
  border-radius: 50px;
  font-size: 14px;
  margin-bottom: 25px;
}
.badge::before { content: "\u2022"; color: var(--blue-main); }
#home h1 {
  font-size: 58px;
  font-weight: 900;
  line-height: 1.3;
  margin-bottom: 20px;
}
#home h1 span { color: var(--blue-text); }
#home p {
  font-size: 18px;
  color: var(--gray-text);
  margin-bottom: 40px;
}
.hero-buttons {
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-primary {
  background-color: var(--blue-main);
  color: var(--white);
  border: none;
  padding: 14px 30px;
  border-radius: 10px;
  font-family: 'Tajawal', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.3s;
}
.btn-primary:hover { background-color: var(--blue-light); }
.btn-outline {
  background-color: transparent;
  color: var(--white);
  border: 1px solid rgba(255,255,255,0.3);
  padding: 14px 30px;
  border-radius: 10px;
  font-family: 'Tajawal', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: border-color 0.3s;
}
.btn-outline:hover { border-color: var(--white); }
</style>

<section id="home">
  <div class="badge">${__('مدارس الإبداع الحديثة')} \u2014 ${__('التعليم الشامل')}</div>
  <h1>${__('مرحبًا بكم في')} <span>${__('مدارس الإبداع الحديثة')}</span></h1>
  <p>${__('نبني جيلاً واعياً في بيئة تعليمية متكاملة')}</p>
  <div class="hero-buttons">
    <button class="btn-primary" onclick="openModal()">${__('احجز مقعدك')}</button>
    <button class="btn-outline" onclick="document.getElementById('about').scrollIntoView({behavior:'smooth'})">${__('تعرف علينا أكثر')}</button>
  </div>
</section>

`;

components["comp-about"] = `
<style>

#about {
  background: linear-gradient(160deg, #0d1117 0%, #131a27 100%);
  padding: 100px 80px;
  display: flex;
  align-items: center;
  gap: 80px;
}
.about-img { flex: 1; min-width: 300px; }
.img-placeholder {
  width: 100%;
  max-width: 480px;
  height: 350px;
  background: linear-gradient(135deg, #1a2235, #2d3748);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 80px;
  overflow: hidden;
  position: relative;
  cursor: pointer;
}
.img-placeholder::after {
  content: '';
  position: absolute;
  top: -60%; left: -60%;
  width: 60%; height: 200%;
  background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,0.25) 50%, transparent 100%);
  transform: skewX(-20deg);
  transition: left 0.6s ease;
  pointer-events: none;
}
.img-placeholder:hover::after { left: 130%; }
.img-placeholder img {
  width: 100%; height: 100%;
  object-fit: cover; border-radius: 16px;
  transition: transform 0.5s cubic-bezier(0.25,0.46,0.45,0.94), filter 0.5s ease;
}
.img-placeholder:hover img {
  transform: scale(1.12) rotate(1deg);
  filter: brightness(1.1) saturate(1.2);
}
.about-content { flex: 1; text-align: right; }
.section-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(45,91,227,0.15);
  border: 1px solid rgba(45,91,227,0.3);
  color: var(--blue-text);
  padding: 7px 18px;
  border-radius: 50px;
  font-size: 14px;
  margin-bottom: 20px;
}
.section-badge::before { content: "\u2022"; color: var(--blue-main); }
.about-content h2 { font-size: 42px; font-weight: 900; margin-bottom: 10px; }
.underline-blue { width: 60px; height: 3px; background-color: var(--blue-main); margin-bottom: 25px; }
.about-content p { color: var(--gray-text); font-size: 16px; line-height: 1.9; margin-bottom: 35px; }
.stats { display: flex; gap: 15px; }
.stat-card {
  background-color: var(--dark-card);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  padding: 20px 25px;
  text-align: center;
  flex: 1;
}
.stat-card .number { font-size: 26px; font-weight: 900; color: var(--blue-text); }
.stat-card .label { font-size: 13px; color: var(--gray-text); margin-top: 5px; }
</style>

<section id="about">
  <div class="about-img">
    <div class="img-placeholder">
      <img src="img/children.jpg" alt="${__('صورة المدرسة')}" style="width:100%; height:100%; object-fit:cover; border-radius:16px;" />
    </div>
  </div>
  <div class="about-content">
    <div class="section-badge">${__('تعرف علينا')}</div>
    <h2>${__('عن المدرسة')}</h2>
    <div class="underline-blue"></div>
    <p>${__('نؤمن أن التعليم الجيد يصنع الفرق. لذلك بنينا مدرسة تجمع بين المنهج الأكاديمي المتميز والبيئة التي تلهم الطالب وتدفعه للأمام.')}</p>
    <div class="stats">
      <div class="stat-card"><div class="number">+500</div><div class="label">${__('طالب وطالبة')}</div></div>
      <div class="stat-card"><div class="number">50+</div><div class="label">${__('كادر تعليمي')}</div></div>
      <div class="stat-card"><div class="number">+15</div><div class="label">${__('سنة خبرة')}</div></div>
    </div>
  </div>
</section>

`;

components["comp-services"] = `
<style>

#services {
  background-color: var(--gray-light);
  padding: 100px 80px;
  text-align: center;
}
#services h2 { font-size: 42px; font-weight: 900; color: var(--text-dark); margin-bottom: 10px; }
.underline-blue-dark { width: 60px; height: 3px; background-color: var(--blue-main); margin: 0 auto 50px; }
.services-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  max-width: 1100px;
  margin: 0 auto;
}
.services-last-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  max-width: 750px;
  margin: 20px auto 0;
}
.service-card {
  border: 1px solid #e8ecf0;
  border-radius: 14px;
  padding: 30px;
  text-align: right;
  transition: box-shadow 0.3s;
}
.service-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
.service-card.blue   { background-color: #eef2ff; }
.service-card.orange { background-color: #fff7ed; }
.service-card.purple { background-color: #f5f3ff; }
.service-card.green  { background-color: #f0fdf4; }
.service-card.teal   { background-color: #f0fdfa; }
.service-card.lime   { background-color: #f7fee7; }
.service-card.pink   { background-color: #fdf2f8; }
.service-card.rose   { background-color: #fff1f2; }
.service-icon { font-size: 32px; margin-bottom: 15px; display: block; text-align: left; }
.service-card h3 { font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; }
.service-card p  { font-size: 14px; color: #666; line-height: 1.7; }
</style>

<section id="services">
  <div class="section-badge" style="color:var(--blue-main);background:rgba(45,91,227,0.08);">${__('ما نقدمه')}</div>
  <h2>${__('الخدمات')}</h2>
  <div class="underline-blue-dark"></div>
  <div class="services-grid">
    <div class="service-card blue">
      <span class="service-icon">👥</span>
      <h3>${__('الأنشطة الطلابية')}</h3>
      <p>${__('مجتمع صغير يصنع شخصية كبيرة \u2014 نوادي تنمي الانتماء وتبني الصداقات.')}</p>
    </div>
    <div class="service-card orange">
      <span class="service-icon">🎨</span>
      <h3>${__('أنشطة فنية')}</h3>
      <p>${__('الإبداع لغة يتحدثها كل طالب \u2014 فرص فنية تكتشف المواهب وتصقلها الآن.')}</p>
    </div>
    <div class="service-card purple">
      <span class="service-icon">🔬</span>
      <h3>${__('مختبرات علمية')}</h3>
      <p>${__('تتعلم بالتجربة لا بالحفظ \u2014 مختبرات مجهزة تحول النظرية إلى واقع.')}</p>
    </div>
  </div>
  <div class="services-grid" style="margin-top:20px;">
    <div class="service-card green">
      <span class="service-icon">🥗</span>
      <h3>${__('مقصف صحي ومتوازن')}</h3>
      <p>${__('وجبات صحية متوازنة تعزز تركيز الطالب وطاقته طوال اليوم الدراسي.')}</p>
    </div>
    <div class="service-card teal">
      <span class="service-icon">🖥️</span>
      <h3>${__('منصات التعليم الذكي')}</h3>
      <p>${__('تعلّم في أي وقت ومن أي مكان \u2014 منصات تفاعلية تجعل المعرفة في متناول يدك.')}</p>
    </div>
    <div class="service-card lime">
      <span class="service-icon">📚</span>
      <h3>${__('مكتبة متكاملة')}</h3>
      <p>${__('كل كتاب باب لعالم جديد \u2014 مكتبة غنية تُشعل شغف القراءة والبحث.')}</p>
    </div>
  </div>
  <div class="services-last-row">
    <div class="service-card pink">
      <span class="service-icon">🔧</span>
      <h3>${__('ورش العمل المهنية')}</h3>
      <p>${__('مهارات اليوم وظائف الغد \u2014 نُعد طلابنا لسوق العمل منذ الآن.')}</p>
    </div>
    <div class="service-card rose">
      <span class="service-icon">🏃</span>
      <h3>${__('أنشطة رياضية')}</h3>
      <p>${__('جسم صحي عقل متفتح \u2014 برامج رياضية تبني الطالب من الداخل والخارج.')}</p>
    </div>
  </div>
</section>

`;

components["comp-stages"] = `
<style>

#stages {
  background-color: var(--gray-light);
  padding: 100px 80px;
  text-align: center;
}
#stages h2 { font-size: 42px; font-weight: 900; color: var(--text-dark); margin-bottom: 10px; }
.stages-grid {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 50px;
}
.stage-card {
  background-color: var(--white);
  border: 1px solid #e8ecf0;
  border-top: 3px solid var(--blue-main);
  border-radius: 14px;
  padding: 30px 25px;
  text-align: right;
  width: 200px;
  position: relative;
}
.stage-num-small {
  position: absolute;
  top: 15px; left: 15px;
  font-size: 12px; color: #ccc; font-weight: 700;
}
.stage-circle {
  width: 55px; height: 55px;
  background-color: #eef2ff;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; font-weight: 900;
  color: var(--blue-main);
  margin-bottom: 15px;
}
.stage-card h3 { font-size: 17px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; }
.stage-card p  { font-size: 13px; color: #888; line-height: 1.7; }
</style>

<section id="stages">
  <div class="section-badge" style="color:var(--blue-main);background:rgba(45,91,227,0.08);">${__('التعليم لكل المراحل')}</div>
  <h2>${__('المراحل الدراسية')}</h2>
  <div class="underline-blue-dark"></div>
  <div class="stages-grid">
    <div class="stage-card">
      <span class="stage-num-small">01</span>
      <div class="stage-circle">1</div>
      <h3>${__('الصف الأول')}</h3>
      <p>${__('خطوة في رحلة العلم \u2014 نبني الأساس بأسلوب ممتع يُحبّب الطفل بالتعلم.')}</p>
    </div>
    <div class="stage-card">
      <span class="stage-num-small">02</span>
      <div class="stage-circle">2</div>
      <h3>${__('الصف الثاني')}</h3>
      <p>${__('جذور تتعمق \u2014 نوسّع آفاق الطالب في الرياضيات والعلوم واللغة بثقة وتدرج.')}</p>
    </div>
    <div class="stage-card">
      <span class="stage-num-small">03</span>
      <div class="stage-circle">3</div>
      <h3>${__('الصف الثالث')}</h3>
      <p>${__('فضول بلا حدود \u2014 نفتح الطالب أمام أبواب جديدة ونرشّح ما تعلمه.')}</p>
    </div>
    <div class="stage-card">
      <span class="stage-num-small">04</span>
      <div class="stage-circle">4</div>
      <h3>${__('الصف الرابع')}</h3>
      <p>${__('عقل يفكر وشخصية تتضح \u2014 نطور مهارات التحليل والتواصل جنبًا إلى جنب.')}</p>
    </div>
    <div class="stage-card">
      <span class="stage-num-small">05</span>
      <div class="stage-circle">5</div>
      <h3>${__('الصف الخامس')}</h3>
      <p>${__('على أعتاب مرحلة جديدة \u2014 نهيّء الطالب بكل ما يحتاجه من علم وثقة وجاهزية.')}</p>
    </div>
  </div>
</section>

`;

components["comp-contact"] = `
<style>

#contact {
  background-color: var(--gray-light);
  padding: 100px 80px;
  text-align: center;
}
#contact h2 { font-size: 42px; font-weight: 900; color: var(--text-dark); margin-bottom: 10px; }
#contact h2 span { color: var(--blue-main); }
.contact-wrapper {
  display: flex; gap: 30px;
  max-width: 1100px; margin: 50px auto 0;
  text-align: right;
}
.contact-form {
  flex: 2;
  background-color: var(--white);
  border: 1px solid #e8ecf0;
  border-radius: 14px;
  padding: 40px;
}
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 14px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
.form-group input,
.form-group textarea {
  width: 100%; padding: 12px 16px;
  border: 1px solid #dde2ea; border-radius: 8px;
  font-family: 'Tajawal', sans-serif; font-size: 14px;
  color: var(--text-dark); background-color: var(--white);
  text-align: right; direction: rtl;
  transition: border-color 0.3s;
}
.form-group input:focus,
.form-group textarea:focus { outline: none; border-color: var(--blue-main); }
.form-group textarea { height: 130px; resize: none; }
.btn-send {
  width: 100%; background-color: var(--blue-main);
  color: var(--white); border: none; padding: 14px;
  border-radius: 10px; font-family: 'Tajawal', sans-serif;
  font-size: 16px; font-weight: 700; cursor: pointer;
  transition: background 0.3s;
}
.btn-send:hover { background-color: var(--blue-light); }
.contact-info { flex: 1; display: flex; flex-direction: column; gap: 15px; }
.info-card {
  background-color: var(--white);
  border: 1px solid #e8ecf0; border-radius: 12px;
  padding: 18px 20px;
  display: flex; align-items: center; justify-content: flex-end; gap: 15px;
}
.info-card .text h4 { font-size: 15px; font-weight: 800; color: var(--text-dark); }
.info-card .text p  { font-size: 13px; color: #888; margin-top: 3px; }
.info-card .icon {
  width: 40px; height: 40px;
  background-color: #eef2ff; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
}
</style>

<section id="contact">
  <div class="section-badge" style="color:var(--blue-main);background:rgba(45,91,227,0.08);">${__('نحن هنا لمساعدتك')}</div>
  <h2>${__('تواصل')} <span>${__('معنا')}</span></h2>
  <div class="underline-blue-dark"></div>
  <div class="contact-wrapper">
    <div class="contact-form">
      <div class="form-group">
        <label>${__('الاسم الكامل')}</label>
        <input id="contact-name" type="text" placeholder="${__('محمد أحمد')}" />
      </div>
      <div class="form-group">
        <label>${__('البريد الإلكتروني')}</label>
        <input id="contact-email" type="email" placeholder="${__('بريدك@example.com')}" />
      </div>
      <div class="form-group">
        <label>${__('الرسالة')}</label>
        <textarea id="contact-message" placeholder="${__('اكتب رسالتك هنا...')}"></textarea>
      </div>
      <button class="btn-send" onclick="submitContact()">✈ ${__('إرسال الرسالة')}</button>
    </div>
    <div class="contact-info">
      <div class="info-card">
        <div class="text"><h4>${__('العنوان')}</h4><p>${__('فلسطين، غزة')}</p></div>
        <div class="icon">📍</div>
      </div>
      <div class="info-card">
        <div class="text"><h4>${__('الهاتف')}</h4><p>+970592388493</p></div>
        <div class="icon">📞</div>
      </div>
      <div class="info-card">
        <div class="text"><h4>${__('البريد الإلكتروني')}</h4><p>info@alebdaa.edu</p></div>
        <div class="icon">✉️</div>
      </div>
      <div class="info-card">
        <div class="text"><h4>${__('ساعات الدوام')}</h4><p>${__('الأحد \u2014 الخميس، 8 ص \u2014 3 م')}</p></div>
        <div class="icon">🕐</div>
      </div>
    </div>
  </div>
</section>

`;

components["comp-footer"] = `
<style>

footer {
  background-color: var(--dark-nav);
  padding: 60px 80px 30px;
  color: var(--white);
}
.footer-top {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  gap: 40px; margin-bottom: 40px; text-align: right;
}
.footer-logo {
  font-size: 20px; font-weight: 800;
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 12px;
}
.footer-logo::before { content: "\u2022"; color: var(--blue-main); }
.footer-col p  { color: var(--gray-text); font-size: 14px; line-height: 1.8; }
.footer-col h4 { font-size: 16px; font-weight: 800; margin-bottom: 15px; }
.footer-col ul { list-style: none; }
.footer-col ul li { margin-bottom: 10px; }
.footer-col ul li a { color: var(--gray-text); text-decoration: none; font-size: 14px; transition: color 0.3s; }
.footer-col ul li a:hover { color: var(--white); }
.footer-contact-item { display: flex; align-items: center; gap: 10px; color: var(--gray-text); font-size: 14px; margin-bottom: 12px; }
.footer-bottom {
  border-top: 1px solid rgba(255,255,255,0.08);
  padding-top: 25px;
  display: flex; align-items: center; justify-content: space-between;
}
.footer-bottom p { color: var(--gray-text); font-size: 13px; }
.social-icons { display: flex; gap: 12px; }
.social-icon {
  width: 36px; height: 36px;
  background-color: rgba(255,255,255,0.08);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 16px;
  text-decoration: none; transition: background 0.3s;
}
.social-icon:hover { background-color: var(--blue-main); }

#scrollTop {
  position: fixed; bottom: 30px; left: 30px;
  width: 48px; height: 48px;
  background-color: var(--blue-main); color: var(--white);
  border: none; border-radius: 50%; font-size: 20px;
  cursor: pointer; display: none;
  align-items: center; justify-content: center;
  z-index: 999; transition: background 0.3s;
}
#scrollTop:hover { background-color: var(--blue-light); }
</style>

<footer>
  <div class="footer-top">
    <div class="footer-col">
      <div class="footer-logo">${__('الإبداع الحديثة')}</div>
      <p>${__('نزرع اليوم ما يحصده الوطن غداً.')}</p>
    </div>
    <div class="footer-col">
      <h4>${__('روابط سريعة')}</h4>
      <ul>
        <li><a href="#home">← ${__('الرئيسية')}</a></li>
        <li><a href="#about">← ${__('عن المدرسة')}</a></li>
        <li><a href="#services">← ${__('الخدمات')}</a></li>
        <li><a href="#stages">← ${__('المراحل الدراسية')}</a></li>
        <li><a href="#contact">← ${__('تواصل معنا')}</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>${__('تواصل معنا')}</h4>
      <div class="footer-contact-item">📍 ${__('فلسطين، غزة')}</div>
      <div class="footer-contact-item">📞 +970592388493</div>
      <div class="footer-contact-item">✉️ info@alebdaa.edu</div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>${__('© 2026 مدرسة الإبداع الحديثة. جميع الحقوق محفوظة.')}</p>
    <div class="social-icons">
      <a href="https://www.facebook.com" target="_blank" class="social-icon" title="${__('فيسبوك')}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
      </a>
      <a href="https://www.instagram.com" target="_blank" class="social-icon" title="${__('انستقرام')}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
      </a>
      <a href="https://wa.me/970592388493" target="_blank" class="social-icon" title="${__('واتساب')}">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.761-1.653-2.059-1.852-.297-.198-.05-.304.13-.453.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.5a8.5 8.5 0 0 1-4.346-1.212l-.312-.185-3.24.807.86-3.166-.203-.323A8.485 8.485 0 0 1 3.5 12c0-4.694 3.806-8.5 8.5-8.5s8.5 3.806 8.5 8.5-3.806 8.5-8.5 8.5z"/></svg>
      </a>
    </div>
  </div>
</footer>

<button id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>

`;

components["comp-modal"] = `
<style>

.modal-overlay {
  position: fixed; inset: 0;
  background-color: rgba(0,0,0,0.6);
  display: none; align-items: center; justify-content: center;
  z-index: 2000;
}
.modal-overlay.open { display: flex; }
@keyframes slideUp {
  from { transform: translateY(40px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}
.modal {
  background-color: var(--white);
  border-radius: 16px; width: 90%; max-width: 500px;
  overflow: hidden; animation: slideUp 0.3s ease;
}
.modal-header {
  background-color: var(--dark-nav); padding: 20px 25px;
  display: flex; align-items: center; justify-content: space-between;
}
.modal-header h3 { color: var(--white); font-size: 18px; font-weight: 800; }
.modal-close { background: none; border: none; color: var(--white); font-size: 22px; cursor: pointer; }
.modal-body { padding: 30px 25px; text-align: right; direction: rtl; color: var(--text-dark); }
.modal-body .subtitle { color: #888; font-size: 14px; margin-bottom: 25px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.btn-submit {
  width: 100%; background-color: var(--blue-main);
  color: var(--white); border: none; padding: 14px;
  border-radius: 10px; font-family: 'Tajawal', sans-serif;
  font-size: 16px; font-weight: 700; cursor: pointer;
  margin-top: 20px; transition: background 0.3s;
}
.btn-submit:hover { background-color: var(--blue-light); }
</style>

<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-header">
      <button class="modal-close" onclick="closeModal()">✕</button>
      <h3>${__('طلب التحاق')}</h3>
    </div>
    <div class="modal-body">
      <p class="subtitle">📋 ${__('تقديم طلب التحاق لابنك — سيتم إنشاء حساب لك ولي أمر بعد الموافقة')}</p>
      <div id="enroll-error" style="display:none; background:#fff0f0; border:1px solid #f5a0a0; color:#c0392b; border-radius:8px; padding:10px 14px; margin-bottom:15px; font-size:14px; text-align:right;"></div>

      <div style="background:#eef2ff;border-radius:10px;padding:15px;margin-bottom:20px;">
        <h4 style="font-size:15px;margin-bottom:12px;">👤 ${__('بيانات ولي الأمر (أنت)')}</h4>
        <div class="form-group">
          <label>${__('الاسم الكامل')}</label>
          <input id="enroll-guardian-name" type="text" placeholder="${__('محمد أحمد')}" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>${__('البريد الإلكتروني')}</label>
            <input id="enroll-guardian-email" type="email" placeholder="${__('بريد@example.com')}" />
          </div>
          <div class="form-group">
            <label>${__('رقم الجوال')}</label>
            <input id="enroll-guardian-phone" type="tel" placeholder="${__('05XXXXXXX')}" />
          </div>
        </div>
      </div>

      <div style="background:#f0fdf4;border-radius:10px;padding:15px;margin-bottom:20px;">
        <h4 style="font-size:15px;margin-bottom:12px;">🎓 ${__('بيانات الطالب (ابنك)')}</h4>
        <div class="form-group">
          <label>${__('الاسم الكامل للطالب')}</label>
          <input id="enroll-student-name" type="text" placeholder="${__('خالد محمد أحمد')}" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>${__('تاريخ الميلاد')}</label>
            <input id="enroll-student-dob" type="date" />
          </div>
          <div class="form-group">
            <label>${__('المرحلة الدراسية')}</label>
            <select id="enroll-stage" style="width:100%;padding:12px 14px;border:2px solid #e2e8f0;border-radius:8px;font-size:14px;background:white;">
              <option value="">${__('— اختر المرحلة —')}</option>
              <option value="الصف الأول">${__('الصف الأول')}</option>
              <option value="الصف الثاني">${__('الصف الثاني')}</option>
              <option value="الصف الثالث">${__('الصف الثالث')}</option>
              <option value="الصف الرابع">${__('الصف الرابع')}</option>
              <option value="الصف الخامس">${__('الصف الخامس')}</option>
            </select>
          </div>
        </div>
      </div>

      <button class="btn-submit" onclick="submitEnrollment()">${__('إرسال طلب الالتحاق')}</button>
    </div>
  </div>
</div>

`;

components["comp-login"] = `
<style>

.login-overlay {
  position: fixed; inset: 0;
  background-color: rgba(0,0,0,0.7);
  display: none; align-items: center; justify-content: center;
  z-index: 3000;
}
.login-overlay.open { display: flex; }
.login-box {
  background-color: #131a27; border-radius: 18px;
  width: 90%; max-width: 820px; padding: 50px 40px;
  text-align: center; animation: slideUp 0.3s ease;
}
.login-close {
  position: absolute; top: 15px; left: 15px;
  background: none; border: none; color: #aaa; font-size: 22px; cursor: pointer;
}
.login-box h2 { font-size: 36px; font-weight: 900; color: var(--white); margin-bottom: 30px; }
.role-box { background-color: #1a2235; border-radius: 14px; padding: 25px; margin-bottom: 30px; }
.role-box p { color: var(--gray-text); font-size: 14px; margin-bottom: 18px; }
.role-cards { display: flex; gap: 15px; justify-content: center; }
.role-card {
  background-color: #0d1117; border: 2px solid transparent;
  border-radius: 12px; padding: 25px 20px; width: 160px;
  cursor: pointer; text-align: center;
  transition: border-color 0.3s, background 0.3s;
}
.role-card:hover { border-color: var(--blue-main); }
.role-card.selected { border-color: var(--blue-main); background-color: rgba(45,91,227,0.1); }
.role-card .role-icon { font-size: 36px; margin-bottom: 12px; display: block; }
.role-card span { font-size: 16px; font-weight: 700; color: var(--white); }
.login-form { display: none; text-align: right; direction: rtl; }
.login-form.show { display: block; }
.login-form h3 { font-size: 20px; font-weight: 800; color: var(--white); margin-bottom: 20px; text-align: center; }
.login-form .form-group { margin-bottom: 16px; }
.login-form label { display: block; font-size: 13px; font-weight: 700; color: #aaa; margin-bottom: 7px; }
.login-form input {
  width: 100%; padding: 12px 16px;
  border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
  font-family: 'Tajawal', sans-serif; font-size: 14px;
  color: var(--white); background-color: #0d1117;
  text-align: right; direction: rtl; transition: border-color 0.3s;
}
.login-form input:focus { outline: none; border-color: var(--blue-main); }
.btn-enter {
  width: 100%; background-color: var(--blue-main); color: var(--white);
  border: none; padding: 14px; border-radius: 10px;
  font-family: 'Tajawal', sans-serif; font-size: 16px; font-weight: 700;
  cursor: pointer; margin-top: 10px; transition: background 0.3s;
}
.btn-enter:hover { background-color: var(--blue-light); }
.btn-back {
  width: 100%; background: none;
  border: 1px solid rgba(255,255,255,0.15); color: #aaa;
  padding: 11px; border-radius: 10px;
  font-family: 'Tajawal', sans-serif; font-size: 14px;
  cursor: pointer; margin-top: 10px; transition: border-color 0.3s;
}
.btn-back:hover { border-color: var(--white); color: var(--white); }
</style>

<div class="login-overlay" id="loginScreen">
  <div class="login-box" style="position:relative;">
    <button class="login-close" onclick="closeLogin()">✕</button>
    <h2>${__('أهلاً وسهلاً بك')}</h2>
    <div class="role-box" id="roleBox">
      <p>${__('حدد طريقة الدخول')}</p>
      <div class="role-cards">
        <div class="role-card" onclick="selectRole('طالب', this)">
          <span class="role-icon">🎓</span>
          <span>${__('طالب')}</span>
        </div>
        <div class="role-card" onclick="selectRole('ولي أمر', this)">
          <span class="role-icon">👔</span>
          <span>${__('ولي أمر')}</span>
        </div>
        <div class="role-card" onclick="selectRole('معلم', this)">
          <span class="role-icon">👨‍🏫</span>
          <span>${__('معلم')}</span>
        </div>
        <div class="role-card" onclick="selectRole('أدمن', this)">
          <span class="role-icon">⚙️</span>
          <span>${__('أدمن')}</span>
        </div>
      </div>
    </div>
    <div class="login-form" id="loginForm">
      <h3 id="loginTitle">${__('تسجيل دخول كـ')} ${__('طالب')}</h3>
      <div id="login-error" style="display:none; background:#fff0f0; border:1px solid #f5a0a0; color:#c0392b; border-radius:8px; padding:10px 14px; margin-bottom:15px; font-size:14px; text-align:right;"></div>
      <div class="form-group">
        <label>${__('البريد الإلكتروني')}</label>
        <input id="login-email" type="email" placeholder="${__('بريدك@example.com')}" onkeydown="if(event.key==='Enter')submitLogin()" />
      </div>
      <div class="form-group">
        <label>${__('كلمة المرور')}</label>
        <input id="login-password" type="password" placeholder="${__('••••••••')}" onkeydown="if(event.key==='Enter')submitLogin()" />
      </div>
      <button class="btn-enter" onclick="submitLogin()">${__('دخول')}</button>
      <button class="btn-back" onclick="goBack()">← ${__('تغيير نوع المستخدم')}</button>
    </div>
  </div>
</div>

`;


Object.keys(components).forEach(function(id) {
  var el = document.getElementById(id);
  if (el) el.innerHTML = components[id];
});
