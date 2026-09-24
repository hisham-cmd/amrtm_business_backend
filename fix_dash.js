import fs from 'fs';

const replacement = `<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-list-check"></i></div>
        <div class="stat-info">
            <h3>{{ $services->count() }}</h3>
            <p>خدماتي</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-images"></i></div>
        <div class="stat-info">
            <h3>{{ $media->count() }}</h3>
            <p>صور المعرض</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $profile?->status === 'active' ? 'green' : ($profile?->status === 'rejected' ? 'red' : 'gold') }}">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $profile ? match($profile->status) { 'active' => 'نشط', 'rejected' => 'مرفوض', default => 'قيد المراجعة' } : '—' }}</h3>
            <p>حالة الحساب</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-building"></i></div>
        <div class="stat-info">
            <h3>{{ $profile?->category?->name ?? '—' }}</h3>
            <p>التصنيف</p>
        </div>
    </div>
</div>

@if($profile)
<!-- Profile preview card -->
<div class="card" style="margin-top:20px;">
    <div class="card-header" style="justify-content: space-between;">
        <span class="card-title"><i class="fas fa-id-card" style="color:var(--green); margin-left:8px;"></i> بروفايلي</span>
        <a href="{{ route('partner.profile-setup') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-edit"></i> تعديل
        </a>
    </div>
    <div class="card-body">
        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
            @if($profile->logo_url)
            <img src="{{ $profile->logo_url }}" alt="logo" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #eef2ef;">
            @else
            <div style="width:80px; height:80px; border-radius:50%; background:#e8f5ee; display:flex; align-items:center; justify-content:center; font-size:2rem; color:#1a5c38;">
                <i class="fas fa-user-circle"></i>
            </div>
            @endif
            <div style="flex:1; min-width:0;">
                <div style="font-size:1.2rem; font-weight:800; color:#0f3d24;">{{ $profile->company_name }}</div>
                <div style="font-size:0.9rem; color:#6c7a72; margin-top:4px;">{{ $profile->category?->name ?? 'بدون تصنيف' }} &mdash; {{ $profile->city ?? '' }}</div>
                @if($profile->description)
                <p style="font-size:0.9rem; color:#4a5568; margin-top:8px; line-height:1.5;">{{ Str::limit($profile->description, 100) }}</p>
                @endif
                <div style="display:flex; gap:12px; margin-top:12px; flex-wrap:wrap; font-size:0.9rem;">
                    @if($profile->phone)
                    <span style="color:#6c7a72;"><i class="fas fa-phone" style="color:var(--green); margin-left:4px;"></i> {{ $profile->phone }}</span>
                    @endif
                    @if($profile->whatsapp)
                    <span style="color:#6c7a72;"><i class="fab fa-whatsapp" style="color:var(--green); margin-left:4px;"></i> {{ $profile->whatsapp }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services preview -->
@if($services->isNotEmpty())
<div class="card" style="margin-top:20px;">
    <div class="card-header" style="justify-content: space-between;">
        <span class="card-title"><i class="fas fa-list-check" style="color:var(--green); margin-left:8px;"></i> خدماتي</span>
        <a href="{{ route('partner.services') }}" class="btn btn-outline btn-sm">إدارة الخدمات</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>الخدمة</th><th>السعر</th></tr></thead>
            <tbody>
                @foreach($services->take(5) as $svc)
                <tr>
                    <td style="font-weight:600;">{{ $svc->title }}</td>
                    <td>{{ $svc->price ? number_format($svc->price) . ' ريال' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endif

@endsection
`;

const file = 'resources/views/partner/dashboard.blade.php';
let content = fs.readFileSync(file, 'utf8');

const splitMarker = '<!-- Stats -->';
if (content.includes(splitMarker)) {
    const firstPart = content.split(splitMarker)[0];
    fs.writeFileSync(file, firstPart + replacement);
}
