@extends('layouts.public')
@section('title', 'آمر تم | Amrtm Platform')
@push('styles')
<style>
@layer base{*{box-sizing:border-box;margin:0;padding:0}}
:root{--pri:#006C35;--pri2:#00843D;--pri3:#00A651}
html,body{background:#000;color:#fff;height:100%;overflow:hidden}
body.ar,body.ar *:not(i):not(span.fa):not(.fa){font-family:'Cairo',sans-serif;direction:rtl}
body.en,body.en *:not(i):not(span.fa):not(.fa){font-family:'Inter',sans-serif;direction:ltr}
.fa,.fas,.far,.fab,.fal,.fad,.fa-solid,.fa-regular,.fa-brands{font-family:'Font Awesome 6 Free'!important;font-style:normal!important;-webkit-font-smoothing:antialiased}

.fs{position:relative;width:100%;height:100vh;height:100dvh;display:flex;flex-direction:column;overflow:hidden}

.fs-slider{position:absolute;inset:0;z-index:0}
.fs-slide{position:absolute;inset:0;opacity:0;transition:opacity 1.4s ease-in-out;background-size:cover;background-position:center}
.fs-slide.active{opacity:1}
.fs-slide::after{content:'';position:absolute;inset:0;background:transparent;pointer-events:none}

.fs-dots{position:absolute;bottom:12px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:8px}
.fs-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3);border:1.5px solid rgba(255,255,255,0.5);cursor:pointer;transition:all 0.35s}
.fs-dot.active{background:#fff;width:26px;border-radius:5px;border-color:#fff}

.fs-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:10;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;cursor:pointer;transition:all 0.3s;backdrop-filter:blur(4px)}
.fs-arrow:hover{background:rgba(255,255,255,0.2)}
.fs-arrow.prev{right:16px}
.fs-arrow.next{left:16px}
body.en .fs-arrow.prev{right:auto;left:16px}
body.en .fs-arrow.next{left:auto;right:16px}

.fs-wrap{position:relative;z-index:5;width:100%;max-width:none;padding:16px 36px;display:flex;flex-direction:column;height:100%;justify-content:center;gap:12px}

.fs-top{display:flex;align-items:center;gap:36px;flex:0 0 auto}
.fs-text{flex:1}
.fs-title{font-size:42px;font-weight:900;color:#fff;line-height:1.15;margin-bottom:6px;text-shadow:0 3px 20px rgba(0,0,0,0.4)}
.fs-title span{background:linear-gradient(90deg,#F5D98A,#C5A253);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.fs-desc{font-size:16px;color:rgba(255,255,255,0.85);line-height:1.55;margin-bottom:12px;max-width:640px;text-shadow:0 2px 12px rgba(0,0,0,0.35)}
.fs-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.fs-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:12px;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.3s;text-decoration:none;border:none}
.fs-btn-primary{background:linear-gradient(135deg,#006C35,#00A651);color:#fff;box-shadow:0 4px 18px rgba(0,108,53,0.35)}
.fs-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,108,53,0.5)}
.fs-btn-outline{background:rgba(255,255,255,0.08);color:#fff;border:1px solid rgba(255,255,255,0.25);backdrop-filter:blur(4px)}
.fs-btn-outline:hover{background:rgba(255,255,255,0.18)}

.fs-video{width:320px;flex-shrink:0;position:relative;display:flex;flex-direction:column}
.fs-video-box{position:relative;width:100%;aspect-ratio:16/10;border-radius:16px;overflow:hidden;cursor:pointer;box-shadow:0 16px 40px rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.12);transition:all 0.35s}
.fs-video-box:hover{transform:translateY(-4px) scale(1.02);box-shadow:0 24px 50px rgba(0,0,0,0.5)}
.fs-video-box img{width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.5s}
.fs-video-box:hover img{transform:scale(1.05)}
.fs-video-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,20,10,0.15) 0%,rgba(0,50,25,0.8) 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px}
.fs-play-btn{width:54px;height:54px;border-radius:50%;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.45);display:flex;align-items:center;justify-content:center;transition:all 0.3s;backdrop-filter:blur(4px)}
.fs-video-box:hover .fs-play-btn{background:rgba(255,255,255,0.35);transform:scale(1.1)}
.fs-play-btn i{font-size:20px;color:#fff;margin-right:-2px}
.fs-video-label{font-size:12px;font-weight:700;color:rgba(255,255,255,0.85)}

.fs-contract{display:inline-flex;align-items:center;justify-content:center;gap:9px;width:100%;margin-top:12px;padding:13px 16px;border-radius:13px;background:linear-gradient(135deg,#006C35,#00843D 55%,#00A651);color:#fff;font-family:inherit;font-size:14px;font-weight:800;text-decoration:none;text-align:center;white-space:nowrap;border:1px solid rgba(255,255,255,0.22);box-shadow:0 6px 22px rgba(0,108,53,0.45);transition:all 0.3s}
.fs-contract:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(0,108,53,0.6);border-color:rgba(255,255,255,0.45)}
.fs-contract i{font-size:16px}
body.en .fs-contract{direction:ltr}

.fs-tagline{display:flex;align-items:center;justify-content:center;gap:18px;padding:6px 0;flex:0 0 auto}
.fs-tagline-line{flex:1;max-width:220px;height:1px;background:linear-gradient(90deg,transparent,rgba(0,166,81,0.35))}
.fs-tagline-line:last-child{background:linear-gradient(270deg,transparent,rgba(0,166,81,0.35))}
.fs-tagline-text{font-size:17px;font-weight:800;color:rgba(255,255,255,0.92);white-space:nowrap;text-shadow:0 2px 10px rgba(0,0,0,0.35)}

.fs-cats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;flex:1}
.fs-cat{display:flex;flex-direction:column;min-height:0;background:rgba(255,255,255,0.06);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.07);border-radius:16px;overflow:hidden;text-decoration:none;transition:all 0.3s}
.fs-cat:hover{transform:translateY(-5px);background:rgba(255,255,255,0.12);border-color:var(--cc,#00A651);box-shadow:0 12px 30px rgba(0,0,0,0.35),0 0 20px color-mix(in srgb,var(--cc,#00A651) 18%,transparent)}
.fs-cat-body{flex:1;padding:18px 12px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.fs-cat-name{font-size:clamp(28px,3.4vw,52px);font-weight:900;color:#fff;line-height:1.25;text-shadow:0 2px 14px rgba(0,0,0,0.4)}
.fs-cat-foot{height:42px;display:flex;justify-content:space-between;align-items:center;padding:0 14px;background:rgba(255,255,255,0.03);border-top:1px solid rgba(255,255,255,0.05)}
.fs-cat-tag{font-size:13px;font-weight:700;color:rgba(255,255,255,0.6)}
.fs-cat-arr{font-size:14px;color:rgba(255,255,255,0.4);transition:all 0.25s}
.fs-cat:hover .fs-cat-arr{color:var(--cc,#00A651);transform:translateX(-3px)}
body.en .fs-cat:hover .fs-cat-arr{transform:translateX(3px)}

.fs-offs{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;flex:0 0 auto}
.fs-off{display:flex;align-items:center;gap:12px;padding:14px 16px;background:rgba(255,255,255,0.05);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.06);border-radius:14px;text-decoration:none;transition:all 0.3s}
.fs-off:hover{transform:translateY(-3px);background:rgba(255,255,255,0.1);border-color:rgba(0,166,81,0.35);box-shadow:0 8px 24px rgba(0,0,0,0.3)}
.fs-off-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;color:#fff}
.fs-off-name{font-size:clamp(15px,1.9vw,25px);font-weight:800;color:rgba(255,255,255,0.92);line-height:1.3;text-shadow:0 2px 10px rgba(0,0,0,0.35)}

.vm{display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.85);backdrop-filter:blur(10px);align-items:center;justify-content:center;padding:2rem}
.vm.open{display:flex;animation:vmIn 0.3s ease}
@keyframes vmIn{from{opacity:0}to{opacity:1}}
.vm-in{width:100%;max-width:880px;border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 40px 100px rgba(0,0,0,0.6)}
.vm-x{position:absolute;top:10px;right:10px;z-index:10;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,0.6);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;cursor:pointer}
body.en .vm-x{right:auto;left:10px}
.vm-x:hover{background:rgba(198,40,40,0.7)}
#fsVideo{width:100%;aspect-ratio:16/9;border:none;display:block}

@media(max-width:1200px){.fs-wrap{padding:12px 24px}.fs-title{font-size:34px}.fs-video{width:280px}.fs-cat-name{font-size:clamp(22px,3vw,36px)}.fs-off-name{font-size:18px}.fs-offs{grid-template-columns:repeat(3,1fr)}}
@media(max-width:900px){.fs-top{flex-direction:column;text-align:center;gap:14px}.fs-text{display:flex;flex-direction:column;align-items:center}.fs-desc{text-align:center}.fs-video{width:100%;max-width:360px}.fs-title{font-size:30px}.fs-cats{grid-template-columns:repeat(3,1fr)}.fs-cat-name{font-size:28px}.fs-off-name{font-size:20px}.fs-offs{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.fs-wrap{padding:10px 14px;gap:10px}.fs-title{font-size:22px}.fs-desc{font-size:13px}.fs-btn{padding:9px 15px;font-size:12px;gap:6px}.fs-video{max-width:300px}.fs-contract{font-size:12px;padding:11px 10px}.fs-play-btn{width:44px;height:44px}.fs-play-btn i{font-size:17px}.fs-tagline-text{font-size:13px}.fs-cats{grid-template-columns:repeat(3,1fr);gap:8px}.fs-cat-body{padding:12px 8px}.fs-cat-name{font-size:22px}.fs-cat-foot{height:32px;padding:0 10px}.fs-cat-tag{font-size:11px}.fs-cat-arr{font-size:12px}.fs-offs{grid-template-columns:repeat(2,1fr);gap:8px}.fs-off{padding:10px 12px;gap:9px}.fs-off-icon{width:36px;height:36px;font-size:16px}.fs-off-name{font-size:16px}.fs-arrow{width:34px;height:34px;font-size:14px}}
</style>
@endpush
@section('content')
<section class="fs">
    <div class="fs-slider" id="fsSlider">
        @forelse($homepageSlides as $slide)
            <div class="fs-slide {{ $loop->first ? 'active' : '' }}" style="background-image:url('{{ $slide['image_url'] }}')"></div>
        @empty
            <div class="fs-slide active" style="background-image:url('{{ asset('images/slide-riyadh-business.jpg') }}')"></div>
            <div class="fs-slide" style="background-image:url('{{ asset('images/slide-kafd.jpg') }}')"></div>
            <div class="fs-slide" style="background-image:url('{{ asset('images/slide-port-jeddah.jpg') }}')"></div>
            <div class="fs-slide" style="background-image:url('{{ asset('images/slide-kingdom.jpg') }}')"></div>
        @endforelse
    </div>
    <div class="fs-dots" id="fsDots">
        @forelse($homepageSlides as $slide)
            <div class="fs-dot {{ $loop->first ? 'active' : '' }}" data-index="{{ $loop->index }}"></div>
        @empty
            <div class="fs-dot active" data-index="0"></div>
            <div class="fs-dot" data-index="1"></div>
            <div class="fs-dot" data-index="2"></div>
            <div class="fs-dot" data-index="3"></div>
        @endforelse
    </div>
    <div class="fs-arrow prev" onclick="fsPrev()"><i class="fa fa-chevron-right"></i></div>
    <div class="fs-arrow next" onclick="fsNext()"><i class="fa fa-chevron-left"></i></div>
    <div class="fs-wrap">
        <div class="fs-top">
            <div class="fs-text">
                <h1 class="fs-title">{{ $homepageSettings['site_title'] ?? 'منصة آمر تم لخدمات قطاع الأعمال' }}</h1>
                <p class="fs-desc">{{ $homepageSettings['site_subtitle'] ?? 'منصة تعمل وفق مفهوم النافذة الواحدة لاستقبال طلبات العملاء وإنجاز معاملاتهم عبر شبكة من الشركاء والمتخصصين.' }}</p>
                <div class="fs-actions">
                    <a href="{{ route('amrtm.index') }}" class="fs-btn fs-btn-primary"><i class="fa fa-rocket"></i> ابدأ الآن</a>
                    <button class="fs-btn fs-btn-outline" onclick="fsOpenVideo()"><i class="fa fa-circle-play"></i> فيديو تعريفي</button>
                </div>
            </div>
            <div class="fs-video">
                <div class="fs-video-box" onclick="fsOpenVideo()">
                    <img src="{{ asset($homepageSettings['video_poster'] ?? 'images/logo2.jpg') }}" alt="فيديو تعريفي" loading="lazy">
                    <div class="fs-video-overlay">
                        <div class="fs-play-btn"><i class="fa fa-play"></i></div>
                        <span class="fs-video-label">فيديو تعريفي عن الخدمات</span>
                    </div>
                </div>
                <a href="{{ route('amrtm.create-contract') }}" class="fs-contract"><i class="fa fa-file-signature"></i>{{ $homepageSettings['contract_button_text'] ?? 'عقود نظامية متاحة حسب النشاط' }}</a>
            </div>
        </div>
        <div class="fs-tagline">
            <span class="fs-tagline-line"></span>
            <span class="fs-tagline-text">{{ $homepageSettings['site_tagline'] ?? 'أختر الخدمة المطلوبة من خلال الجهات التالية' }}</span>
            <span class="fs-tagline-line"></span>
        </div>
        @php
            $catColorMap = ['ministries'=>'#3B82F6','authorities'=>'#A855F7','companies'=>'#22C55E','embassies'=>'#06B6D4','consultants'=>'#F97316'];
        @endphp
        <div class="fs-cats">
            @forelse($categories as $cat)
                @php $catKey = $cat['key'] ?? ''; $catColor = $catColorMap[$catKey] ?? ($cat['color'] ?? '#00A651'); @endphp
                <a href="{{ route('amrtm.catalog.category', $catKey) }}" class="fs-cat" style="--cc:{{ $catColor }}">
                    <div class="fs-cat-body"><div class="fs-cat-name">{{ $cat['name_ar'] }}</div></div>
                    <div class="fs-cat-foot"><span class="fs-cat-tag">{{ $cat['entities_count'] ?? 0 }} جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div>
                </a>
            @empty
                <a href="{{ route('amrtm.catalog.category', 'ministries') }}" class="fs-cat" style="--cc:#3B82F6"><div class="fs-cat-body"><div class="fs-cat-name">الــوزارات</div></div><div class="fs-cat-foot"><span class="fs-cat-tag">0 جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div></a>
                <a href="{{ route('amrtm.catalog.category', 'authorities') }}" class="fs-cat" style="--cc:#A855F7"><div class="fs-cat-body"><div class="fs-cat-name">الهيئات</div></div><div class="fs-cat-foot"><span class="fs-cat-tag">0 جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div></a>
                <a href="{{ route('amrtm.catalog.category', 'companies') }}" class="fs-cat" style="--cc:#22C55E"><div class="fs-cat-body"><div class="fs-cat-name">الشركات الحكومية</div></div><div class="fs-cat-foot"><span class="fs-cat-tag">0 جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div></a>
                <a href="{{ route('amrtm.catalog.category', 'embassies') }}" class="fs-cat" style="--cc:#06B6D4"><div class="fs-cat-body"><div class="fs-cat-name">السفارات والقنصليات والمنظمات</div></div><div class="fs-cat-foot"><span class="fs-cat-tag">0 جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div></a>
            @endforelse
            <a href="{{ route('amrtm.consultants.directory') }}" class="fs-cat" style="--cc:#F97316"><div class="fs-cat-body"><div class="fs-cat-name">المستشارين</div></div><div class="fs-cat-foot"><span class="fs-cat-tag">{{ $officeCounts['consultants'] ?? 0 }} جهة</span><i class="fa fa-arrow-left fs-cat-arr"></i></div></a>
        </div>
        <div class="fs-offs">
            <a href="{{ route('amrtm.offices.directory', 'law') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#006C35,#00843D)"><i class="fa fa-scale-balanced"></i></div><div class="fs-off-name">مكاتب المحاماة</div></a>
            <a href="{{ route('amrtm.offices.directory', 'services') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#bd15c0,#c71ee5)"><i class="fa fa-briefcase"></i></div><div class="fs-off-name">مكاتب الخدمات والتعقيب</div></a>
            <a href="{{ route('amrtm.offices.directory', 'customs') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#2182f0,#0688eb)"><i class="fa fa-address-book"></i></div><div class="fs-off-name">شركات التخليص الجمركي</div></a>
            <a href="{{ route('amrtm.offices.directory', 'accounting') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#2207a7,#0b05d1)"><i class="fa fa-calculator"></i></div><div class="fs-off-name">الاستشارات المالية والضريبية</div></a>
            <a href="{{ route('amrtm.offices.directory', 'engineering') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#f69d03,#e9a403)"><i class="fa fa-building"></i></div><div class="fs-off-name">الاستشارات الهندسية</div></a>
            <a href="{{ route('amrtm.offices.directory', 'freelance') }}" class="fs-off"><div class="fs-off-icon" style="background:linear-gradient(135deg,#00695C,#00897B)"><i class="fa fa-user"></i></div><div class="fs-off-name">أصحاب المهن الحرة</div></a>
        </div>
    </div>
</section>
<div class="vm" id="fsVm" onclick="if(event.target===this)fsCloseVideo()">
    <div class="vm-in">
        <div class="vm-x" onclick="fsCloseVideo()"><i class="fa fa-xmark"></i></div>
        <video id="fsVideo" controls playsinline preload="auto" style="width:100%;height:auto;max-height:80vh;display:block;background:#000;">
            <source src="{{ asset($homepageMedia['video_file'] ?? 'videos/0829.mp4') }}" type="video/mp4">
            المتصفح لا يدعم تشغيل هذا الفيديو.
        </video>
    </div>
</div>
@endsection
@push('scripts')
<script>
let fsCurrent=0,fsTotal=0,fsInterval=null;const fsSlides=[],fsDots=[];
function fsInit(){document.querySelectorAll('#fsSlider .fs-slide').forEach(s=>fsSlides.push(s));document.querySelectorAll('#fsDots .fs-dot').forEach(d=>{fsDots.push(d);d.addEventListener('click',()=>fsGoTo(+d.dataset.index))});fsTotal=fsSlides.length;if(fsTotal>1)fsInterval=setInterval(fsNext,5000)}
function fsGoTo(i){if(i<0||i>=fsTotal||i===fsCurrent)return;fsSlides[fsCurrent].classList.remove('active');fsDots[fsCurrent].classList.remove('active');fsCurrent=i;fsSlides[fsCurrent].classList.add('active');fsDots[fsCurrent].classList.add('active');clearInterval(fsInterval);if(fsTotal>1)fsInterval=setInterval(fsNext,5000)}
function fsNext(){fsGoTo((fsCurrent+1)%fsTotal)}
function fsPrev(){fsGoTo((fsCurrent-1+fsTotal)%fsTotal)}
(function(){let x=0;const h=document.querySelector('.fs');if(!h)return;h.addEventListener('touchstart',e=>{x=e.touches[0].clientX},{passive:true});h.addEventListener('touchend',e=>{const d=x-e.changedTouches[0].clientX;if(Math.abs(d)>50){d>0?fsNext():fsPrev()}},{passive:true})})();
function fsOpenVideo(){document.getElementById('fsVm').classList.add('open');document.getElementById('fsVideo').play()}
function fsCloseVideo(){const v=document.getElementById('fsVideo');v.pause();v.currentTime=0;document.getElementById('fsVm').classList.remove('open')}
document.addEventListener('keydown',function(e){if(e.key==='Escape')fsCloseVideo();if(e.key==='ArrowLeft')fsNext();if(e.key==='ArrowRight')fsPrev()});
document.addEventListener('DOMContentLoaded',fsInit);
</script>
@endpush