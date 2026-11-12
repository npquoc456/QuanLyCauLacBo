@extends('layouts.main')

@section('title', 'Trang chủ | UniClub')
@section('hideDefaultFooter', 'true')

@section('content')
    <div class="home-page">
        <div class="home-sync"><span></span> Số liệu được cập nhật từ hệ thống</div>

        <section class="home-hero">
            <div class="home-hero-copy">
                <span class="home-term">UNICLUB · DỮ LIỆU THỰC TẾ</span>
                <h1>Học tập &amp; Hoạt động cùng UniClub, <em>bứt phá</em> từng chặng đường.</h1>
                <p>Nền tảng kết nối câu lạc bộ, hoạt động và các đợt tuyển thành viên đang diễn ra.</p>
                <div class="home-hero-stats">
                    <div><strong>{{ $platform_stats['clubs'] }}</strong><span>Câu lạc bộ</span></div>
                    <div><strong>{{ $platform_stats['users'] }}</strong><span>Tài khoản</span></div>
                    <div><strong>{{ $platform_stats['events'] }}</strong><span>Sự kiện</span></div>
                </div>
            </div>
            <div class="home-hero-art">
                <div class="home-art-tags"><span>✦ AI &amp; Tech</span><span>♡ Tình nguyện</span></div>
                <img src="{{ asset('image/img.png') }}" alt="Các thành viên UniClub cùng học tập và sáng tạo">
                <div class="home-art-caption"><span>✦ Marketing</span><span>✓ Kỹ năng mềm</span></div>
            </div>
            <span class="home-hero-orbit home-hero-orbit-one"></span>
            <span class="home-hero-orbit home-hero-orbit-two"></span>
        </section>

        <section class="home-profile-section">
            <div class="home-profile-heading">
                <div>
                    <h2><span>◎</span> Hồ sơ sinh viên &amp; Hoạt động CLB</h2>
                    <p>
                        @if ($profile)
                            {{ $profile->HoTen }} · MSSV: {{ $profile->MSSV }} · {{ $profile->Khoa ?: 'Chưa cập nhật khoa' }} · {{ $profile->KhoaHoc ?: 'Chưa cập nhật khóa học' }}
                        @else
                            Đăng nhập để xem thông tin cá nhân và hoạt động của bạn.
                        @endif
                    </p>
                </div>
                <span class="home-member-pill"><i></i>
                    @if (! $profile)
                        CHƯA ĐĂNG NHẬP
                    @elseif ($profile->TrangThai?->value === 'HOAT_DONG')
                        TÀI KHOẢN ĐANG HOẠT ĐỘNG
                    @else
                        TÀI KHOẢN KHÔNG HOẠT ĐỘNG
                    @endif
                </span>
            </div>

            <div class="home-profile-grid">
                <div class="home-activity-column">
                    <div class="home-quick-stats">
                        <article><span>◉ ĐANG QUẢN LÝ</span><strong>{{ $stats['managed_clubs'] }} CLB</strong><small>Chức vụ quản lý đang hoạt động</small></article>
                        <article><span>♣ ĐANG THAM GIA</span><strong>{{ $stats['joined_clubs'] }} CLB</strong><small>Thành viên đang hoạt động</small></article>
                        <article><span>✦ ĐIỂM RÈN LUYỆN</span><strong>{{ $stats['training_points'] }} ĐRL</strong><small>Chưa có dữ liệu điểm rèn luyện</small></article>
                        <article><span>▣ SỰ KIỆN CHECK-IN</span><strong>{{ $stats['attended_events'] }} sự kiện</strong><small>Sự kiện đã check-in</small></article>
                    </div>

                    <section class="home-club-group">
                        <div class="home-group-title"><strong>◎ CLB ĐANG ĐIỀU HÀNH &amp; QUẢN LÝ</strong><span>{{ $managed_clubs->count() }} tổ chức</span></div>
                        <div class="home-managed-clubs">
                            @forelse ($managed_clubs as $membership)
                                <article>
                                    <div>
                                        <h3>{{ $membership->cauLacBo->TenCLB }}</h3>
                                        <span>{{ $membership->ChucVu }}</span>
                                    </div>
                                    <p>{{ $membership->cauLacBo->MoTa ?: 'Chưa có mô tả câu lạc bộ.' }}</p>
                                    <footer><i></i> {{ $membership->cauLacBo->TrangThai?->value ?? 'Chưa cập nhật trạng thái' }} <small>Mã CLB: {{ $membership->MaCLB }}</small></footer>
                                </article>
                            @empty
                                <p class="home-empty-state">Bạn chưa quản lý câu lạc bộ nào.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="home-club-group home-joined-group">
                        <div class="home-group-title"><strong>♣ CLB &amp; ĐỘI NHÓM ĐANG THAM GIA</strong><span>{{ $joined_clubs->count() }} tổ chức</span></div>
                        <div class="home-joined-clubs">
                            @forelse ($joined_clubs as $membership)
                                <article>
                                    <strong>{{ $membership->cauLacBo->TenCLB }}</strong>
                                    <span>{{ $membership->ChucVu }}</span>
                                </article>
                            @empty
                                <p class="home-empty-state">Bạn chưa tham gia câu lạc bộ nào.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="home-student-card">
                    <span class="home-verified"><i></i> {{ $profile ? 'Thông tin tài khoản' : 'Chưa đăng nhập' }} <span>⚙</span></span>
                    <div class="home-student-avatar">
                        @if ($profile?->URLAnhDaiDien)
                            <img src="{{ $profile->URLAnhDaiDien }}" alt="Ảnh đại diện của {{ $profile->HoTen }}">
                        @else
                            <span>{{ $profile ? mb_strtoupper(mb_substr($profile->HoTen, 0, 1)) : '♙' }}</span>
                        @endif
                    </div>
                    <h3>{{ $profile?->HoTen ?? 'Khách UniClub' }}</h3>
                    <span class="home-student-role">♙ {{ $member_role }}</span>
                    @if ($profile?->Email)
                        <a class="home-student-email" href="mailto:{{ $profile->Email }}">{{ $profile->Email }}</a>
                    @else
                        <span class="home-student-email">Chưa cập nhật email</span>
                    @endif
                    <dl>
                        <div><dt>Mã số sinh viên:</dt><dd>{{ $profile?->MSSV ?: 'Chưa cập nhật' }}</dd></div>
                        <div><dt>Khoa:</dt><dd>{{ $profile?->Khoa ?: 'Chưa cập nhật' }}</dd></div>
                        <div><dt>Ngành:</dt><dd>{{ $profile?->Nganh ?: 'Chưa cập nhật' }}</dd></div>
                        <div><dt>Khóa học:</dt><dd>{{ $profile?->KhoaHoc ?: 'Chưa cập nhật' }}</dd></div>
                        <div><dt>Số điện thoại:</dt><dd>{{ $profile?->SDT ?: 'Chưa cập nhật' }}</dd></div>
                    </dl>
                    @if ($profile)
                        <a class="home-profile-action" href="{{ route('settings') }}">▧ &nbsp; Chỉnh sửa hồ sơ cá nhân</a>
                    @else
                        <a class="home-profile-action" href="{{ route('login') }}">Đăng nhập để xem hồ sơ</a>
                    @endif
                </aside>
            </div>
        </section>

        <section class="home-open-clubs">
            <div class="home-open-heading">
                <div>
                    <h2><span>♧</span> CLB đang mở đơn đăng ký</h2>
                    <p>Các đợt tuyển có thời gian bắt đầu và kết thúc đang diễn ra.</p>
                </div>
            </div>

            <div class="home-recruitment-grid">
                @foreach ($recruitments as $recruitment)
                    <article class="home-recruitment-card">
                        <div class="home-recruitment-art">
                            @if ($recruitment->URLPoster)
                                <img src="{{ asset($recruitment->URLPoster) }}" alt="Poster {{ $recruitment->TenDotTuyen }}" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
                            @elseif ($recruitment->cauLacBo->URLAnhNen)
                                <img src="{{ $recruitment->cauLacBo->URLAnhNen }}" alt="Ảnh bìa {{ $recruitment->cauLacBo->TenCLB }}" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
                            @elseif ($recruitment->cauLacBo->URLLogo)
                                <img src="{{ $recruitment->cauLacBo->URLLogo }}" alt="Logo {{ $recruitment->cauLacBo->TenCLB }}" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
                            @else
                                <div class="home-recruitment-placeholder" aria-hidden="true"></div>
                            @endif
                            <span class="home-recruit-status"><i></i> Đang mở đơn</span>
                        </div>
                        <div class="home-recruitment-body">
                            <div class="home-recruitment-meta"><span>ĐỢT TUYỂN</span><small>Đến {{ $recruitment->NgayKetThuc->format('d/m/Y') }}</small></div>
                            <h3>{{ $recruitment->cauLacBo->TenCLB }}</h3>
                            <p>{{ $recruitment->MoTa ?: $recruitment->cauLacBo->MoTa ?: 'Câu lạc bộ đang tuyển thành viên.' }}</p>
                            <div class="home-recruitment-info">
                                <span>Hồ sơ hợp lệ<strong>{{ $recruitment->application_count }}</strong></span>
                                <span>Chỉ tiêu<strong>{{ $recruitment->target_count }}</strong></span>
                            </div>
                            <footer>
                                <span>{{ $recruitment->TenDotTuyen }}</span>
                                <a href="{{ route('dot-tuyen.show', $recruitment->MaDotTuyen) }}">Xem &amp; ứng tuyển</a>
                            </footer>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <footer class="home-journey">
            <div>
                <h2>Bắt đầu hành trình phát triển<br> bản thân với UniClub</h2>
                <p>Kết nối cùng {{ $platform_stats['users'] }} tài khoản trong mạng lưới câu lạc bộ.</p>
            </div>
            <div class="home-journey-mark"><span>UniClub OS</span><strong>♡</strong><small>2026 Ready</small></div>
            <div class="home-journey-links">
                <nav><a href="{{ route('home') }}">Trang chủ</a><a href="{{ route('caulacbo.index') }}">Câu lạc bộ</a><a href="{{ route('sukien.index') }}">Sự kiện</a></nav>
                <strong>UNICLUB CENTER</strong>
                <small>© 2026 UniClub</small>
            </div>
        </footer>
    </div>
@endsection
