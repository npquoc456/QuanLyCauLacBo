<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | UniClub</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        (function() {
            var theme = localStorage.getItem('uniclub-theme');
            if (theme === 'dark' || theme === 'light') {
                document.documentElement.dataset.theme = theme;
            }
            var size = localStorage.getItem('uniclub-font-size');
            if (size === 'small' || size === 'medium' || size === 'large') {
                document.documentElement.dataset.fontSize = size;
            }
        })();
    </script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/auth.js') }}" defer></script>
</head>
<body class="auth-body">
    <header class="auth-topbar">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 9.5 12 4l9 5.5-9 5.5-9-5.5Z"></path><path d="M6.5 12v4.5c3.3 2.7 7.7 2.7 11 0V12M21 10v6"></path></svg>
            </span>
            <span class="brand-copy"><strong>UniClub</strong><small>TỔ CHỨC &amp; QUẢN LÝ SỰ KIỆN CLB</small></span>
        </a>
        <nav aria-label="Điều hướng xác thực">
            <a class="{{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}">Đăng nhập</a>
            <a class="{{ request()->routeIs('register') ? 'is-active' : '' }}" href="{{ route('register') }}">Đăng ký</a>
            <a href="{{ route('home') }}">Trang chủ</a>
        </nav>
    </header>

    <main class="auth-shell">
        <div class="auth-breadcrumb"><span></span> UNICLUB <i>/</i> XÁC THỰC TÀI KHOẢN <i>/</i> @yield('auth-label')</div>
        <section class="auth-card @yield('auth-card-class')">
            <aside class="auth-promo">
                <span class="auth-promo-tag"><i></i> NỀN TẢNG SỐ HÓA HOẠT ĐỘNG CLB</span>
                <h1>Học tập &amp; Hoạt động cùng UniClub, bứt phá chặng đường.</h1>
                <p>Nền tảng vận hành tập trung: quản lý sự kiện khoa học, điểm danh thông minh qua QR, tích lũy và ghi nhận Điểm Rèn Luyện (ĐRL) tự động.</p>

                @php
                    $displayEvent = $featuredEvent ?? (
                        \App\Models\SuKien::query()->with('cauLacBo')->where('ThoiGianBatDau', '<=', now())->where('ThoiGianKetThuc', '>=', now())->first()
                        ?? \App\Models\SuKien::query()->with('cauLacBo')->where('ThoiGianBatDau', '>', now())->orderBy('ThoiGianBatDau')->first()
                        ?? \App\Models\SuKien::query()->with('cauLacBo')->latest('ThoiGianBatDau')->first()
                    );

                    $displayRecruitment = $featuredRecruitment ?? ($displayEvent ? null : (
                        \App\Models\DotTuyenThanhVien::query()->with('cauLacBo')->where('TrangThai', \App\Enums\DotTuyenTrangThai::DANG_TUYEN)->latest('NgayBatDau')->first()
                        ?? \App\Models\DotTuyenThanhVien::query()->with('cauLacBo')->where('LaBanNhap', false)->latest('NgayBatDau')->first()
                    ));

                    $stats = $platformStats ?? [
                        'clubs' => \App\Models\CauLacBo::count(),
                        'users' => \App\Models\NguoiDung::count(),
                        'events' => \App\Models\SuKien::count(),
                    ];
                @endphp

                @if ($displayEvent)
                    @php
                        $now = now();
                        $isOngoing = $displayEvent->ThoiGianBatDau && $displayEvent->ThoiGianKetThuc && $now->between($displayEvent->ThoiGianBatDau, $displayEvent->ThoiGianKetThuc);
                        $statusLabel = $isOngoing ? 'ĐANG MỞ ĐIỂM DANH HÔM NAY' : ($displayEvent->ThoiGianBatDau && $displayEvent->ThoiGianBatDau->gt($now) ? 'SẮP DIỄN RA' : 'SỰ KIỆN NỔI BẬT');
                        $location = $displayEvent->DiaDiem ?: ($displayEvent->cauLacBo?->TenCLB ?? 'Hội trường trực tuyến');
                        $eventImg = $displayEvent->URLAnhNen ?: ($displayEvent->cauLacBo?->URLLogo ?: asset('image/img.png'));
                    @endphp
                    <div class="auth-feature">
                        <div class="auth-feature-media">
                            <img src="{{ $eventImg }}" alt="{{ $displayEvent->TenSuKien }}" class="auth-feature-img" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
                        </div>
                        <div>
                            <small><i></i> {{ $statusLabel }}</small>
                            <strong>{{ $displayEvent->TenSuKien }}</strong>
                            <span>{{ $location }}</span>
                        </div>
                        <b>+5 ĐRL<small>Cộng trực tiếp</small></b>
                    </div>
                @elseif ($displayRecruitment)
                    @php
                        $clubName = $displayRecruitment->cauLacBo?->TenCLB ?? 'Tuyển thành viên CLB';
                        $deadlineText = $displayRecruitment->NgayKetThuc ? 'Hạn nộp: '.$displayRecruitment->NgayKetThuc->format('d/m/Y') : 'Đang mở nhận hồ sơ';
                        $recImg = $displayRecruitment->URLPoster ? asset($displayRecruitment->URLPoster) : ($displayRecruitment->cauLacBo?->URLAnhNen ?: ($displayRecruitment->cauLacBo?->URLLogo ?: asset('image/img.png')));
                    @endphp
                    <div class="auth-feature">
                        <div class="auth-feature-media">
                            <img src="{{ $recImg }}" alt="{{ $displayRecruitment->TenDotTuyen }}" class="auth-feature-img" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
                        </div>
                        <div>
                            <small><i></i> ĐANG MỞ ĐỢT TUYỂN THÀNH VIÊN</small>
                            <strong>{{ $displayRecruitment->TenDotTuyen }}</strong>
                            <span>{{ $clubName }} · {{ $deadlineText }}</span>
                        </div>
                        <b>Tuyển sinh<small>Ứng tuyển ngay</small></b>
                    </div>
                @else
                    <div class="auth-feature">
                        <div class="auth-feature-media">
                            <img src="{{ asset('image/img.png') }}" alt="Sự kiện" class="auth-feature-img">
                        </div>
                        <div>
                            <small><i></i> ĐANG MỞ ĐIỂM DANH HÔM NAY</small>
                            <strong>Workshop: AI &amp; Khởi nghiệp 2025</strong>
                            <span>Hội trường E12, Trường Đại học Công nghệ thông tin</span>
                        </div>
                        <b>+5 ĐRL<small>Cộng trực tiếp</small></b>
                    </div>
                @endif

                <div class="auth-stats">
                    <div>
                        <strong>{{ $stats['clubs'] }}</strong>
                        <span>Câu lạc bộ</span>
                    </div>
                    <div>
                        <strong class="stat-highlight">{{ $stats['users'] }}</strong>
                        <span>Tài khoản</span>
                    </div>
                    <div>
                        <strong>{{ $stats['events'] }}</strong>
                        <span>Sự kiện</span>
                    </div>
                </div>
                <div class="auth-trust"><span>♧ Tin cậy bởi 40+ CLB &amp; Liên chi Đoàn</span><span>▣ SSL 256-BIT ENCRYPTION</span></div>
            </aside>
            <section class="auth-form-panel">
                @yield('auth-content')
                <p class="auth-security"><span>▣</span> Hệ thống bảo vệ dữ liệu &amp; xác thực định danh sinh viên</p>
            </section>
        </section>
        <div class="auth-support"><span>Cần hỗ trợ kỹ thuật? <a href="mailto:support@uniclub.edu.vn">Hotline hỗ trợ sinh viên</a></span><span>Phiên bản v2.4.2-stable <i>•</i> Server: VNU Data Center HCM</span></div>
    </main>

    <footer class="auth-footer">
        <div><strong>♧ UniClub Platform</strong><span>© 2025 UniClub. Cộng đồng và đăng ký sự kiện phong trào Sinh viên </span></div>
        <nav><a href="{{ route('home') }}">Trung tâm trợ giúp</a><a href="{{ route('home') }}">Quy chế sinh viên</a><a href="{{ route('home') }}">Chính sách bảo mật</a><a href="mailto:support@uniclub.edu.vn">Liên hệ Ban Quản lý</a></nav>
    </footer>
</body>
</html>
