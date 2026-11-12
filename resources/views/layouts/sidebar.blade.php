<aside class="sidebar">
    <a class="brand" href="{{ route('sukien.index') }}">
        <span class="brand-logo" aria-label="Logo UniClub">
            <span class="brand-logo-fallback">U</span>
        </span>
        <span class="brand-copy"><strong>UniClub</strong><small>MANAGER &amp; OPERATIONS</small></span>
    </a>

    <div class="sidebar-section-label">Không gian làm việc</div>
    <nav class="sidebar-nav" aria-label="Điều hướng chính">
        <a class="sidebar-link" href="{{ route('caulacbo.index') }}">
            <span class="nav-icon">⌂</span><span>Club Manager</span><span class="nav-chevron">›</span>
        </a>
        <a class="sidebar-link" href="{{ route('sukien.index') }}">
            <span class="nav-icon">◷</span><span>Danh sách sự kiện</span>
        </a>
    </nav>

    <div class="sidebar-section-label">Điều hành &amp; quản trị</div>
    <nav class="sidebar-nav" aria-label="Quản trị">
        <a class="sidebar-link" href="{{ route('sukien.index') }}">
            <span class="nav-icon">▦</span><span>Quản lý sự kiện</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <div class="sidebar-help-mark">?</div>
        <div><strong>Trung tâm trợ giúp</strong><small>UniClub Manager</small></div>
    </div>
</aside>
