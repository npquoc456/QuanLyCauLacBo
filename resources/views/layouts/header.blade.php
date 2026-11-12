<header class="site-header">
    <a class="brand" href="{{ route('home') }}" aria-label="UniClub - Trang chủ">
        <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 9.5 12 4l9 5.5-9 5.5-9-5.5Z"></path><path d="M6.5 12v4.5c3.3 2.7 7.7 2.7 11 0V12M21 10v6"></path></svg>
        </span>
        <span class="brand-copy">
            <strong>UniClub</strong>
            <small>TỔ CHỨC &amp; QUẢN LÝ SỰ KIỆN CLB</small>
        </span>
    </a>

    <nav class="main-nav" aria-label="Các phân hệ UniClub">
        <a class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
            <span class="nav-item-icon">⌂</span><span>Trang chủ</span>
        </a>
        <a class="nav-link {{ request()->routeIs('caulacbo.*') ? 'is-active' : '' }}" href="{{ route('caulacbo.index') }}">
            <span class="nav-item-icon">♧</span><span>Quản lý Câu lạc bộ</span>
        </a>
        <span class="nav-link nav-link-disabled" aria-disabled="true"><span class="nav-item-icon">♙</span><span>Cổng Đăng ký</span></span>
        <span class="nav-link nav-link-disabled" aria-disabled="true"><span class="nav-item-icon">⌗</span><span>Tự Quét QR</span></span>
    </nav>

    <div class="header-tools">
        <div class="font-size-control" role="group" aria-label="Cỡ chữ">
            <button type="button" data-font-size="small" aria-label="Cỡ chữ nhỏ">S</button>
            <button type="button" data-font-size="medium" class="is-selected" aria-label="Cỡ chữ vừa">M</button>
            <button type="button" data-font-size="large" aria-label="Cỡ chữ lớn">L</button>
            <script>
                (function() {
                    var s = localStorage.getItem('uniclub-font-size');
                    if (s === 'small' || s === 'medium' || s === 'large') {
                        var btns = document.currentScript.parentElement.querySelectorAll('[data-font-size]');
                        btns.forEach(function(b) {
                            if (b.getAttribute('data-font-size') === s) {
                                b.classList.add('is-selected');
                            } else {
                                b.classList.remove('is-selected');
                            }
                        });
                    }
                })();
            </script>
        </div>
        <button class="icon-button theme-toggle" type="button" aria-label="Bật giao diện tối" title="Đổi giao diện sáng/tối">
            <svg class="sun-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path></svg>
            <svg class="moon-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8 8.7 8.7 0 1 0 20.2 15.3Z"></path></svg>
        </button>
        <div class="language-control" aria-label="Ngôn ngữ">
            <span class="is-selected">VI</span><span>EN</span>
        </div>
        @auth
            <div class="profile-menu" data-profile-menu>
                <button class="profile-menu-trigger" type="button" aria-expanded="false" aria-haspopup="menu" aria-label="Mở menu tài khoản" data-profile-menu-trigger>
                    <span class="profile-copy"><strong>{{ auth()->user()->HoTen }}</strong><small>{{ auth()->user()->TenTaiKhoan }}</small></span>
                    <span class="profile-avatar" aria-hidden="true">
                        @if (auth()->user()->URLAnhDaiDien)
                            <img src="{{ auth()->user()->URLAnhDaiDien }}" alt="">
                        @else
                            {{ mb_strtoupper(mb_substr(auth()->user()->HoTen, 0, 1)) }}
                        @endif
                    </span>
                </button>
                <div class="profile-menu-panel" role="menu" aria-label="Menu tài khoản" data-profile-menu-panel hidden>
                    <a role="menuitem" href="{{ route('settings') }}"><span aria-hidden="true">⚙</span> Cài đặt</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button role="menuitem" type="submit"><span aria-hidden="true">↪</span> Đăng xuất</button>
                    </form>
                </div>
            </div>
        @else
            <a class="auth-header-link" href="{{ route('login') }}">Đăng nhập</a>
            <a class="auth-header-link auth-header-link-primary" href="{{ route('register') }}">Đăng ký</a>
        @endauth
    </div>
</header>
