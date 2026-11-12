@extends('layouts.main')

@section('title', 'Quản lý Câu lạc bộ | UniClub')

@section('content')
    <div class="sync-status"><span></span> HỆ THỐNG VẬN HÀNH CLB SINH VIÊN</div>

    <nav class="club-section-tabs" aria-label="Phân loại câu lạc bộ">
        <a class="club-section-tab {{ $tab === 'managed' ? 'is-active' : '' }}" href="{{ route('caulacbo.index', ['tab' => 'managed']) }}" @if($tab === 'managed') aria-current="page" @endif>
            <span class="club-section-tab-dot"></span>
            <span>Câu lạc bộ đang quản lý</span>
            <b>{{ $tab === 'managed' ? $stats['clubs'] : $managedClubCount }}</b>
        </a>
        <a class="club-section-tab {{ $tab === 'joined' ? 'is-active' : '' }}" href="{{ route('caulacbo.index', ['tab' => 'joined']) }}" @if($tab === 'joined') aria-current="page" @endif>
            <span class="club-section-tab-icon" aria-hidden="true">♧</span>
            <span>Câu lạc bộ đã tham gia</span>
            <b>{{ $joinedClubCount }}</b>
        </a>
    </nav>

    @if($tab === 'joined')
        @include('caulacbo.joined')
    @else
    <section class="page-intro club-page-intro">
        <div>
            <div class="eyebrow">UNICLUB <span>/</span> QUẢN LÝ CÂU LẠC BỘ</div>
            <h1>Quản lý Câu lạc bộ của bạn</h1>
            <p>Tổ chức hoạt động và khởi tạo sự kiện điểm danh tự động cho từng đơn vị trực thuộc campus.</p>
        </div>
        <div class="intro-actions">
            <button class="primary-button" type="button" data-open-club-create>＋ Tạo Câu lạc bộ Mới</button>
        </div>
    </section>

    @if(session('success'))
        <div class="notice notice-success" role="status">
            <span class="notice-check">✓</span>
            <span>{{ session('success') }}</span>
            <button type="button" class="notice-close" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="event-form-errors" role="alert">
            <strong>Vui lòng kiểm tra lại thông tin câu lạc bộ:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="management-layout club-management-layout">
        <aside class="event-overview">
            <div class="overview-heading">
                <h2>Bộ câu lạc bộ</h2>
                <span>{{ $stats['clubs'] }}</span>
            </div>
            <nav class="overview-nav" aria-label="Lọc câu lạc bộ">
                <a class="overview-link {{ $filter === 'all' && !request()->filled('category') ? 'is-active' : '' }}" href="{{ route('caulacbo.index') }}">
                    <span>♧</span><strong>Tất cả</strong><b>{{ $stats['clubs'] }}</b>
                </a>
                <a class="overview-link {{ $filter === 'active' ? 'is-active' : '' }}" href="{{ route('caulacbo.index', ['filter' => 'active']) }}">
                    <span class="overview-star">✓</span><strong>Đang hoạt động</strong><b>{{ $stats['activeClubs'] }}</b>
                </a>
                @foreach($categories as $categoryId)
                    <a class="overview-link {{ (string) request('category') === (string) $categoryId ? 'is-active' : '' }}" href="{{ route('caulacbo.index', ['category' => $categoryId]) }}">
                        <span>▤</span><strong>Danh mục #{{ $categoryId }}</strong><b>{{ $categoryCounts[$categoryId] ?? 0 }}</b>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="events-panel club-events-panel">
            <div class="overview-banner">
                <div class="overview-banner-top">
                    <div>
                        <strong>{{ $stats['clubs'] }} <span>câu lạc bộ đang quản lý</span></strong>
                        <small>CLB ĐANG ĐIỀU HÀNH · {{ $stats['events'] }} sự kiện đang liên kết</small>
                    </div>
                    <div class="overview-legend">
                        <span><i class="legend-internal"></i> Đang hoạt động: {{ $stats['activeClubs'] }}</span>
                        <span><i class="legend-public"></i> Khác: {{ $stats['clubs'] - $stats['activeClubs'] }}</span>
                    </div>
                </div>
                <div class="overview-progress"><span style="width: {{ $stats['clubs'] ? round($stats['activeClubs'] / $stats['clubs'] * 100) : 0 }}%"></span></div>
            </div>

            <div class="list-panel-header">
                <div class="list-title">
                    <h2>{{ request()->filled('category') ? 'Danh mục #'.request('category') : ($filter === 'active' ? 'Đang hoạt động' : 'Danh sách câu lạc bộ') }}</h2>
                    <span>{{ $clubs->count() }}</span>
                </div>
                <form class="search-form" method="GET" action="{{ route('caulacbo.index') }}">
                    @if($filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    @if(request()->filled('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên câu lạc bộ...">
                    <button type="submit" aria-label="Tìm kiếm">⌕</button>
                </form>
            </div>

            <div class="club-list">
                <div class="club-list-heading">
                    <span>Câu lạc bộ</span>
                    <span>Liên hệ / Danh mục</span>
                    <span>Trạng thái</span>
                    <span>Thao tác</span>
                </div>
                @forelse($clubs as $club)
                    @php
                        $editHasErrors = $errors->any()
                            && old('club_form_mode') === 'edit'
                            && (string) old('club_form_id') === (string) $club->MaCLB;
                        $statusLabel = str_replace('_', ' ', $club->TrangThai?->value ?? 'CHUA_CAP_NHAT');
                        $latestEvent = $club->latest_event;
                    @endphp
                    <article class="club-row" id="club-{{ $club->MaCLB }}">
                        <div class="club-row-main">
                            @if($club->URLLogo)
                                <img class="club-logo" src="{{ $club->URLLogo }}" alt="Logo {{ $club->TenCLB }}" loading="lazy">
                            @else
                                <span class="club-logo club-logo-fallback" aria-hidden="true">{{ mb_strtoupper(mb_substr($club->TenCLB, 0, 1)) }}</span>
                            @endif
                            <div class="club-main">
                                <a class="event-name" href="{{ route('caulacbo.members', $club->MaCLB) }}">{{ $club->TenCLB }}</a>
                                <div class="event-badges">
                                    <span class="meta-badge">CLB-{{ str_pad((string) $club->MaCLB, 4, '0', STR_PAD_LEFT) }}</span>
                                    @if($club->MaDanhMucCLB)
                                        <span class="meta-badge">Danh mục #{{ $club->MaDanhMucCLB }}</span>
                                    @endif
                                </div>
                                <div class="event-details">
                                    <span>{{ $latestEvent ? 'SỰ KIỆN LIÊN KẾT GẦN NHẤT · '.$club->events_count.' TỔNG CỘNG' : 'SỰ KIỆN LIÊN KẾT · '.$club->events_count }}</span>
                                    @if($latestEvent)
                                        <span>{{ $latestEvent->TenSuKien }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="club-row-contact">
                            <span>{{ $club->Email ?: 'Chưa có email' }}</span>
                            <span>{{ $club->SDT ?: 'Chưa có số điện thoại' }}</span>
                        </div>
                        <span class="checkin-badge {{ $club->is_active ? 'checkin-open' : '' }}"><span style="display:none;">{{ $statusLabel }}</span><i></i>{{ $club->TrangThai?->label() ?? $statusLabel }}</span>
                        <div class="event-actions club-row-actions">
                            <a class="event-action event-action-primary" href="{{ route('caulacbo.members', $club->MaCLB) }}">Quản lý câu lạc bộ</a>
                            <button class="event-action event-action-edit" type="button" data-open-club-edit="club-edit-{{ $club->MaCLB }}">Sửa</button>
                            <form action="{{ route('caulacbo.destroy', $club->MaCLB) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa câu lạc bộ này?')">
                                @csrf
                                @method('DELETE')
                                <button class="event-action event-action-delete" type="submit">Xóa</button>
                            </form>
                        </div>
                    </article>

                    <dialog class="event-modal club-modal" id="club-edit-{{ $club->MaCLB }}" aria-labelledby="club-edit-title-{{ $club->MaCLB }}" @if($editHasErrors) data-open-on-load="true" @endif>
                        <section class="event-modal-card">
                            <header class="event-modal-header">
                                <span class="event-modal-icon" aria-hidden="true">✎</span>
                                <div>
                                    <h2 id="club-edit-title-{{ $club->MaCLB }}">Chỉnh sửa câu lạc bộ</h2>
                                    <p>Cập nhật hồ sơ {{ $club->TenCLB }}.</p>
                                </div>
                                <button type="button" class="event-modal-close" data-close-club-modal aria-label="Đóng">×</button>
                            </header>
                            <form action="{{ route('caulacbo.update', $club->MaCLB) }}" method="POST" enctype="multipart/form-data" class="event-modal-form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="club_form_mode" value="edit">
                                <input type="hidden" name="club_form_id" value="{{ $club->MaCLB }}">
                                <div class="event-modal-body">
                                    @include('caulacbo._form_fields', ['club' => $club])
                                </div>
                                <footer class="event-modal-footer">
                                    <button type="button" class="secondary-button" data-close-club-modal>Hủy bỏ</button>
                                    <button type="submit" class="primary-button">Lưu thay đổi</button>
                                </footer>
                            </form>
                        </section>
                    </dialog>
                @empty
                    <div class="empty-state">
                        <strong>{{ $search || $filter !== 'all' || request()->filled('category') ? 'Không tìm thấy câu lạc bộ phù hợp' : 'Chưa có câu lạc bộ nào' }}</strong>
                        <span>{{ $search || $filter !== 'all' || request()->filled('category') ? 'Hãy đổi từ khóa hoặc bộ lọc để xem kết quả khác.' : 'Tạo câu lạc bộ đầu tiên để bắt đầu quản lý.' }}</span>
                        @if(! $search && $filter === 'all' && !request()->filled('category'))
                            <button class="primary-button" type="button" data-open-club-create>＋ Tạo câu lạc bộ</button>
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="panel-bottom">
                <span>Hiển thị {{ $clubs->isNotEmpty() ? '1–'.$clubs->count() : '0–0' }} trong {{ $clubs->count() }} câu lạc bộ</span>
            </div>
        </div>
    </section>

    <dialog class="event-modal club-modal" id="club-create-modal" aria-labelledby="club-create-title" @if($errors->any() && old('club_form_mode') === 'create') data-open-on-load="true" @endif>
        <section class="event-modal-card">
            <header class="event-modal-header">
                <span class="event-modal-icon" aria-hidden="true">＋</span>
                <div>
                    <h2 id="club-create-title">Tạo câu lạc bộ mới</h2>
                    <p>Nhập thông tin cơ bản để thêm CLB vào hệ thống.</p>
                </div>
                <button type="button" class="event-modal-close" data-close-club-modal aria-label="Đóng">×</button>
            </header>
            <form action="{{ route('caulacbo.store') }}" method="POST" enctype="multipart/form-data" class="event-modal-form">
                @csrf
                <input type="hidden" name="club_form_mode" value="create">
                <div class="event-modal-body">
                    @include('caulacbo._form_fields', ['club' => null])
                </div>
                <footer class="event-modal-footer">
                    <button type="button" class="secondary-button" data-close-club-modal>Hủy bỏ</button>
                    <button type="submit" class="primary-button">＋ Tạo câu lạc bộ</button>
                </footer>
            </form>
        </section>
    </dialog>
    @endif
@endsection
