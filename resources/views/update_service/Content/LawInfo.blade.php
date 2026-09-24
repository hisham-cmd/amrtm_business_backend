
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>مكاتب المحاماة  | منصة آمر تم</title>
    <link rel="icon" type="image/png" href="{{ asset('images/new-logo1.png') }}">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
<style>
/*==============================
        VARIABLES
==============================*/

:root{

    --primary:#059669;
    --primary-light:#047857;

    --text:#0f172a;
    --text-light:#6B7280;

    --white:#fff;

    --bg:#ffffff;

    --soft:#f8fafc;

    --border:#e8edf5;

    --shadow:
        0 15px 40px rgba(5,150,105,.08);

    --shadow-hover:
        0 28px 60px rgba(5,150,105,.16);

    --radius:22px;

    --transition:.35s ease;

}

/*==============================
RESET
==============================*/
*{box-sizing:border-box;margin:0;padding:0;}
:root{--pri:#059669;--pri2:#047857;--pri3:#16a34a;--bg:#f8fafc;--sur:#fff;--bc:rgba(5,150,105,.07);--t1:#0f172a;--t2:#334155;--t3:#64748b;--t4:#94a3b8;--pd:rgba(5,150,105,.08);--sh:rgba(5,150,105,.08);--sh2:rgba(5,150,105,.18);}
html,body{background:var(--bg);color:var(--t1);min-height:100vh;overflow-x:hidden;}
body.ar,body.ar *:not(i){font-family:'Cairo',sans-serif;direction:rtl;}
body.en,body.en *:not(i){font-family:'Inter',sans-serif;direction:ltr;}


img{

    width:100%;

    display:block;

}

a{

    text-decoration:none;

}

.container{

    width:min(1180px,92%);

    margin:auto;

}

section{

    padding:90px 0;

}


/*==============================
SECTION TITLE
==============================*/

.section-title{

    text-align:center;

    margin-bottom:55px;

}

.section-title span{

    display:inline-block;

    color:var(--primary);

    font-weight:700;

    margin-bottom:10px;

}

.section-title h2{

    font-size:38px;

    color:var(--primary);

    font-weight:800;

}


/*==============================
BUTTONS
==============================*/

.btn-primary{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    height:55px;

    padding:0 34px;

    border-radius:40px;

    background:var(--primary);

    color:#fff;

    font-weight:700;

    transition:.35s;

}

.btn-primary:hover{

    background:var(--primary-light);

    transform:translateY(-4px);

    box-shadow:
        0 15px 40px rgba(5,150,105,.25);

}

.btn-outline{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    height:55px;

    padding:0 34px;

    border-radius:40px;

    border:2px solid rgba(255,255,255,.5);

    color:#fff;

    transition:.35s;

}

.btn-outline:hover{

    background:#fff;

    color:var(--primary);

}


/*==============================
HERO
==============================*/

.hero{

    position:relative;

    height:88vh;

    display:flex;

    align-items:center;

    overflow:hidden;

}

.hero-bg{

    position:absolute;

    inset:0;

}

.hero-bg img{

    height:100%;

    object-fit:cover;

    filter:blur(3px);

    transform:scale(1.08);

}

.hero-overlay{

    position:absolute;

    inset:0;

    background:

    linear-gradient(

    rgba(5,150,105,.72),

    rgba(5,150,105,.72)

    );

}

.hero-content{

    position:relative;

    color:#fff;

    max-width:720px;

}

.hero-badge{

    display:inline-flex;

    gap:10px;

    align-items:center;

    background:rgba(255,255,255,.12);

    backdrop-filter:blur(12px);

    border:1px solid rgba(255,255,255,.18);

    padding:12px 22px;

    border-radius:50px;

    margin-bottom:25px;

}

.hero h1{

    font-size:58px;

    line-height:1.2;

    margin-bottom:25px;

    font-weight:800;

}

.hero p{

    font-size:20px;

    opacity:.95;

    margin-bottom:35px;

}

.hero-buttons{

    display:flex;

    gap:18px;

    flex-wrap:wrap;

}


/*==============================
ABOUT
==============================*/

.about-grid{

    display:grid;

    grid-template-columns:1.2fr 1fr;

    gap:50px;

    align-items:center;

}

.about-text p{

    margin-bottom:18px;

    color:var(--text-light);

    font-size:17px;

}

.about-image{

    overflow:hidden;

    border-radius:var(--radius);

    box-shadow:var(--shadow);

}

.about-image img{

    transition:.5s;

}

.about-image:hover img{

    transform:scale(1.05);

}
/*====================================
VIDEO
====================================*/

.video-card{

    background:#fff;

    border-radius:26px;

    overflow:hidden;

    border:1px solid var(--border);

    box-shadow:var(--shadow);

    transition:var(--transition);

    padding:18px;

}

.video-card:hover{

    transform:translateY(-8px);

    box-shadow:var(--shadow-hover);

}

.video-card iframe{

    width:100%;

    height:560px;

    border:none;

    border-radius:18px;

}


/*====================================
SERVICES
====================================*/

.services{

    background:var(--soft);

}

.services-grid{

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(270px,1fr));

    gap:28px;

}

.service-card{

    position:relative;

    background:#fff;

    border:1px solid var(--border);

    border-radius:24px;

    padding:35px 28px;

    overflow:hidden;

    transition:all .35s ease;

    cursor:pointer;

}

/* لمعة خفيفة */

.service-card::before{

    content:"";

    position:absolute;

    top:-120px;

    right:-120px;

    width:220px;

    height:220px;

    background:rgba(5,150,105,.05);

    border-radius:50%;

    transition:.45s;

}

/* خط سفلي */

.service-card::after{

    content:"";

    position:absolute;

    bottom:0;

    right:0;

    width:0;

    height:4px;

    background:var(--primary);

    transition:.35s;

}

.service-card i{

    width:74px;

    height:74px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:20px;

    font-size:34px;

    color:var(--primary);

    background:#f0fdf4;

    transition:.35s;

}

.service-card h3{

    margin-top:25px;

    font-size:22px;

    color:var(--text);

    transition:.35s;

}

.service-card p{

    margin-top:12px;

    color:var(--text-light);

}

/* Hover */

.service-card:hover{

    transform:translateY(-12px);

    border-color:#D1FAE5;

    box-shadow:

        0 28px 60px rgba(5,150,105,.14);

}

.service-card:hover::before{

    transform:scale(1.5);

}

.service-card:hover::after{

    width:100%;

}

.service-card:hover i{

    background:var(--primary);

    color:#fff;

    transform:rotate(-8deg) scale(1.08);

}

.service-card:hover h3{

    color:var(--primary);

}


/*====================================
FEATURES
====================================*/

.feature-list{

    display:grid;

    grid-template-columns:repeat(2,1fr);

    gap:20px;

}

.feature-item{

    display:flex;

    align-items:center;

    gap:16px;

    background:#fff;

    padding:22px;

    border-radius:18px;

    border:1px solid var(--border);

    transition:.35s;

}

.feature-item i{

    width:48px;

    height:48px;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

    background:#f0fdf4;

    color:var(--primary);

    font-size:22px;

    transition:.35s;

}

.feature-item:hover{

    transform:translateX(-8px);

    box-shadow:var(--shadow);

}

.feature-item:hover i{

    background:var(--primary);

    color:#fff;

}


/*====================================
VISION
====================================*/

.vision{

    display:grid;

    grid-template-columns:repeat(2,1fr);

    gap:30px;

}

.vision-card{

    background:#fff;

    border:1px solid var(--border);

    border-radius:24px;

    padding:40px;

    transition:.35s;

    box-shadow:0 10px 25px rgba(0,0,0,.03);

}

.vision-card i{

    font-size:42px;

    color:var(--primary);

}

.vision-card h3{

    margin:22px 0 15px;

    color:var(--primary);

    font-size:28px;

}

.vision-card p{

    color:var(--text-light);

}

.vision-card:hover{

    transform:translateY(-10px);

    box-shadow:var(--shadow-hover);

}


/*====================================
CTA
====================================*/

.cta{

    margin-top:80px;

    background:linear-gradient(

        135deg,

        #059669,

        #047857

    );

    color:#fff;

    text-align:center;

    border-radius:28px;

    padding:70px 20px;

}

.cta h2{

    font-size:42px;

    margin-bottom:30px;

}

.cta .btn-primary{

    background:#fff;

    color:var(--primary);

}

.cta .btn-primary:hover{

    background:#f0fdf4;

}


/*====================================
REVEAL
====================================*/

.reveal{

    opacity:0;

    transform:translateY(60px);

    transition:

        opacity .8s ease,

        transform .8s ease;

}

.reveal.active{

    opacity:1;

    transform:none;

}


/*====================================
RESPONSIVE
====================================*/

@media(max-width:992px){

.hero{

    height:auto;

    padding:120px 0 80px;

}

.hero h1{

    font-size:42px;

}

.about-grid{

    grid-template-columns:1fr;

}

.feature-list{

    grid-template-columns:1fr;

}

.vision{

    grid-template-columns:1fr;

}

.video-card iframe{

    height:350px;

}

}


@media(max-width:768px){

.section-title h2{

    font-size:30px;

}

.hero h1{

    font-size:34px;

}

.hero p{

    font-size:17px;

}

.hero-buttons{

    flex-direction:column;

}

.btn-primary,

.btn-outline{

    width:100%;

}

.services-grid{

    grid-template-columns:1fr;

}

}
</style>
</head>
<body class="ar">


<div class="law-page">

    <!-- ================= HERO ================= -->

    <section class="hero">

        <div class="hero-bg">

            <img src="{{ asset('images/law2.jpg') }}" alt="">

        </div>

        <div class="hero-overlay"></div>

        <div class="container hero-content">

            <div class="hero-badge">

                <i class="ti ti-scale"></i>

                منصة الخدمات القانونية

            </div>

            <h1>

                منصة المحامين والخدمات القانونية

            </h1>

           <div class="hero-buttons">

    <a href="#services" class="btn-primary">
        استعرض الخدمات القانونية
    </a>

    <a href="#about" class="btn-outline">
        تعرف على المنصة
    </a>

</div>

        </div>

    </section>



    <!-- ================= ABOUT ================= -->

    <section id="about" class="about container reveal">

    <div class="section-title">
        <span>نبذة</span>
        <h2>عن المنصة</h2>
    </div>

    <div class="about-grid">

        <div class="about-text">

            <p>

                منصة إلكترونية متخصصة تعمل كحلقة وصل بين العملاء ومكاتب
                المحاماة والمحامين المرخصين، بهدف تقديم جميع الخدمات
                القانونية والاستشارية عبر نافذة رقمية موحدة، مع إتاحة
                اختيار المكتب أو المحامي الأنسب وفقًا للتخصص وسرعة
                التنفيذ وتقييمات العملاء والموقع الجغرافي.

            </p>

            <p>

                تهدف المنصة إلى تسهيل الوصول إلى الخدمات القانونية،
                ورفع جودة الأداء، وتنظيم العلاقة بين مقدم الخدمة
                والمستفيد، مع توفير بيئة إلكترونية آمنة لإدارة
                الطلبات والعقود والمدفوعات ومتابعة سير الأعمال
                حتى الإنجاز.

            </p>

        </div>

        <div class="about-image">
            <img src="{{ asset('images/law1.jpg') }}">
        </div>

    </div>

</section>


    <!-- ================= VIDEO ================= -->

    <section id="video" class="video-section reveal">

        <div class="container">

            <div class="section-title">

                <span>تعرف علينا</span>

                <h2>الفيديو التعريفي</h2>

            </div>

            <div class="video-card">

                <iframe
                    src=""
                    allowfullscreen>
                </iframe>

            </div>

        </div>

    </section>



    <!-- ================= SERVICES ================= -->
<section id="services">
      <div class="section-title">

                

                <h2>الخدمات التي تقدم عبر المنصة</h2>

            </div>
    <div class="services-grid" >

    <div class="service-card">
    <i class="ti ti-building-bank"></i>
    <h3>تأسيس الشركات والمؤسسات</h3>
</div>

<div class="service-card">
    <i class="ti ti-license"></i>
    <h3>إصدار وتجديد التراخيص التجارية والمهنية</h3>
</div>

<div class="service-card">
    <i class="ti ti-certificate"></i>
    <h3>تسجيل العلامات التجارية وحقوق الملكية الفكرية</h3>
</div>

<div class="service-card">
    <i class="ti ti-file-text"></i>
    <h3>إعداد وصياغة ومراجعة العقود والاتفاقيات</h3>
</div>

<div class="service-card">
    <i class="ti ti-message"></i>
    <h3>الاستشارات القانونية (حضورية، هاتفية، مرئية)</h3>
</div>

<div class="service-card">
    <i class="ti ti-gavel"></i>
    <h3>الترافع والتمثيل أمام الجهات القضائية</h3>
</div>

<div class="service-card">
    <i class="ti ti-scale"></i>
    <h3>التحكيم والوساطة وتسوية النزاعات</h3>
</div>

<div class="service-card">
    <i class="ti ti-hammer"></i>
    <h3>تنفيذ الأحكام ومتابعة إجراءات التنفيذ</h3>
</div>

<div class="service-card">
    <i class="ti ti-shield-check"></i>
    <h3>الامتثال والحوكمة وإدارة المخاطر القانونية</h3>
</div>

<div class="service-card">
    <i class="ti ti-building-skyscraper"></i>
    <h3>تصفية الشركات والإفلاس وإعادة الهيكلة</h3>
</div>

<div class="service-card">
    <i class="ti ti-writing"></i>
    <h3>خدمات التوثيق والإقرارات والوكالات</h3>
</div>

<div class="service-card">
    <i class="ti ti-briefcase"></i>
    <h3>خدمات القضايا العمالية، والتجارية، والعقارية، والأحوال الشخصية، والجنائية، والإدارية</h3>
</div>
</div>
</section>

    <!-- ================= FEATURES ================= -->

    <section class="features reveal">

        <div class="container">

            <div class="section-title">

                <span>المزايا</span>

                <h2>لماذا منصتنا؟</h2>

            </div>

           <div class="feature-list">
<div class="feature-item">
    <i class="ti ti-check"></i>
    ربط المستفيد بالمحامي أو المكتب المناسب حسب التخصص.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    مقارنة الأسعار ومدة الإنجاز وتقييمات العملاء.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    تقديم عروض أسعار من أكثر من مكتب.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    إدارة الطلبات والعقود إلكترونيًا.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    الدفع الإلكتروني مع حفظ حقوق الأطراف.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    متابعة مراحل تنفيذ الطلب لحظة بلحظة.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    إشعارات فورية ورسائل تنبيه.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    أرشفة إلكترونية لجميع المستندات.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    لوحة تحكم للمحامين والمكاتب والعملاء.
</div>

<div class="feature-item">
    <i class="ti ti-check"></i>
    تقارير وإحصاءات لقياس الأداء وجودة الخدمات.
</div>
</div>

        </div>

    </section>



    <section class="target-section reveal">

    <div class="container">

        <div class="section-title">
            <span>الفئات</span>
            <h2>الفئات المستهدفة</h2>
        </div>

        <div class="services-grid">

            <div class="service-card"><i class="ti ti-user"></i><h3>الأفراد</h3></div>

            <div class="service-card"><i class="ti ti-bulb"></i><h3>رواد الأعمال </h3></div>

            <div class="service-card"><i class="ti ti-building"></i><h3>الشركات والمؤسسات</h3></div>

            <div class="service-card"><i class="ti ti-world"></i><h3>المستثمرون المحلييون والاجانب</h3></div>

            <div class="service-card"><i class="ti ti-heart-handshake"></i><h3>الجهات غير الربحية</h3></div>

            <div class="service-card"><i class="ti ti-building-bank"></i><h3>الجهات الحكومية وشبه الحكومية</h3></div>

            <div class="service-card"><i class="ti ti-scale"></i><h3>مكاتب المحاماة والمحامون المرخصون</h3></div>

        </div>

    </div>

</section>

<section class="features reveal">

    <div class="container">

        <div class="section-title">
            <span>الإيرادات</span>
            <h2>نموذج الإيرادات</h2>
        </div>

        <div class="feature-list">

            <div class="feature-item"><i class="ti ti-check"></i>اشتراك سنوي لمكاتب المحاماة.</div>

            <div class="feature-item"><i class="ti ti-check"></i>عمولة على الخدمات المنفذة.</div>

            <div class="feature-item"><i class="ti ti-check"></i>رسوم إبراز وتسويق للمكاتب.</div>

            <div class="feature-item"><i class="ti ti-check"></i>خدمات Premium.</div>

            <div class="feature-item"><i class="ti ti-check"></i>الإعلانات القانونية.</div>

            <div class="feature-item"><i class="ti ti-check"></i>اشتراكات مؤسسية للشركات.</div>

            <div class="feature-item"><i class="ti ti-check"></i>برامج الولاء والعقود السنوية.</div>

        </div>

    </div>

</section>
    <!-- ================= VISION ================= -->

    <section class="vision container reveal">

        <div class="vision-card">

            <i class="ti ti-target-arrow"></i>

            <h3>الرؤية</h3>

           <p>
أن تكون المنصة الرقمية الأولى في المملكة لربط العملاء
بمكاتب المحاماة والمحامين المرخصين، وتقديم الخدمات
القانونية باحترافية وموثوقية عبر تجربة إلكترونية متكاملة.
</p>

        </div>

        <div class="vision-card">

            <i class="ti ti-rocket"></i>

            <h3>الرسالة</h3>

           <p>
تمكين الأفراد وقطاع الأعمال من الوصول إلى الخدمات
القانونية المتخصصة بسهولة وسرعة وشفافية، من خلال
منصة رقمية تجمع نخبة مكاتب المحاماة والمحامين،
وتوفر حلولًا قانونية متكاملة وفق أعلى معايير الجودة والامتثال.
</p>

        </div>

    </section>



    <!-- ================= CTA ================= -->

    <section class="cta">

        <div class="container">

            <h2>

                ابحث الآن عن مكتب المحاماة المناسب لك

            </h2>


<a href="{{ route('amrtm.consultants.directory') }}" class="btn-primary">
    استعرض المكاتب
</a>

        </div>

    </section>

</div>


<script>
/*======================================
  Reveal Animation
======================================*/

const reveals = document.querySelectorAll(".reveal");

const observer = new IntersectionObserver((entries)=>{

    entries.forEach(entry=>{

        if(entry.isIntersecting){

            entry.target.classList.add("active");

        }

    });

},{
    threshold:.15
});

reveals.forEach(el=>observer.observe(el));


/*======================================
  Hero Parallax
======================================*/

const heroImage = document.querySelector(".hero-bg img");

window.addEventListener("scroll",()=>{

    if(heroImage){

        let y = window.scrollY;

        heroImage.style.transform =
        `scale(1.08) translateY(${y*0.18}px)`;

    }

});


/*======================================
  Smooth Hover (Cards)
======================================*/

document.querySelectorAll(".service-card").forEach(card=>{

    card.addEventListener("mousemove",(e)=>{

        const rect = card.getBoundingClientRect();

        const x = e.clientX - rect.left;

        const y = e.clientY - rect.top;

        card.style.setProperty("--x",x+"px");
        card.style.setProperty("--y",y+"px");

    });

});
</script>
</body>
</html>
