@extends('layouts.main')

@section('title', 'Bộ sự kiện & Điểm danh CLB | UniClub')

@section('content')
    <div class="sync-status"><span></span> Đã đồng bộ dữ liệu thời gian thực <i>·</i> Cập nhật {{ now()->format('H:i') }}</div>

    @if ($managementNavigation)
        @php
            $activeManagementTab = 'events';
        @endphp
        @include('caulacbo.management-tabs')
    @endif

    <section class="page-intro">
        <div>
            <div class="eyebrow">UNICLUB <span>/</span> QUẢN LÝ SỰ KIỆN</div>
            <h1>Bộ sự kiện &amp; Điểm danh CLB</h1>
            <p>
                @if(request()->filled('ma_clb'))
                    Danh sách sự kiện thuộc CLB #{{ request('ma_clb') }}.
                @else
                    Tổng hợp {{ $counts['all'] }} sự kiện đang triển khai và quản lý trong nhiệm kỳ CLB.
                @endif
            </p>
        </div>
        <div class="intro-actions">
            @if(request()->filled('ma_clb'))
                <a class="primary-button event-create-button" href="{{ route('sukien.create', ['ma_clb' => request('ma_clb')]) }}">＋ <span>Tạo sự kiện</span></a>
            @else
                <a class="secondary-button" href="{{ route('caulacbo.index') }}">Câu lạc bộ</a>
            @endif
        </div>
    </section>

    @if(session('success'))
        <div class="notice notice-success" role="status">
            <span class="notice-check">✓</span>
            <span>{{ session('success') }}</span>
            <button type="button" class="notice-close" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    <section class="management-layout">
        <aside class="event-overview">
            <div class="overview-heading">
                <h2>Bộ sự kiện</h2>
                <span>{{ $counts['all'] }}</span>
            </div>
            <nav class="overview-nav" aria-label="Lọc sự kiện">
                <a class="overview-link {{ $filter === null && ! $starredOnly ? 'is-active' : '' }}" href="{{ route('sukien.index', request()->only('ma_clb')) }}">
                    <span>▤</span><strong>Tất cả</strong><b>{{ $counts['all'] }}</b>
                </a>
                <a class="overview-link {{ $starredOnly ? 'is-active' : '' }}" href="{{ route('sukien.index', array_merge(request()->only('ma_clb'), ['marked' => 1])) }}">
                    <span class="overview-star">★</span><strong>Đã đánh dấu</strong><b>{{ $counts['starred'] }}</b>
                </a>
                @foreach($statuses as $status)
                    @php
                        $statusEnum = \App\Enums\SuKienTrangThai::tryFrom($status);
                        $statusLabel = $statusEnum ? $statusEnum->label() : str_replace('_', ' ', $status);
                    @endphp
                    <a class="overview-link {{ $filter === $status ? 'is-active' : '' }}" href="{{ route('sukien.index', array_merge(request()->only('ma_clb'), ['trang_thai' => $status])) }}">
                        <span>▱</span><strong>{{ $statusLabel }}</strong><b>{{ $counts['statuses'][$status] ?? 0 }}</b>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="events-panel">
            <div class="overview-banner">
                <div class="overview-banner-top">
                    <div>
                        <strong>{{ $counts['all'] }} <span>sự kiện đang quản lý</span></strong>
                        <small>Tổng {{ $counts['all'] }} sự kiện trong hệ thống</small>
                    </div>
                    <div class="overview-legend">
                        @foreach(array_slice($statuses, 0, 2) as $statusIndex => $status)
                            @php
                                $legendEnum = \App\Enums\SuKienTrangThai::tryFrom($status);
                                $legendLabel = $legendEnum ? $legendEnum->label() : str_replace('_', ' ', $status);
                            @endphp
                            <span><i class="{{ $statusIndex === 0 ? 'legend-internal' : 'legend-public' }}"></i> {{ $legendLabel }}: {{ $counts['statuses'][$status] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="overview-progress"><span style="width: {{ $counts['all'] && $statuses ? round(($counts['statuses'][$statuses[0]] ?? 0) / $counts['all'] * 100) : 0 }}%"></span></div>
            </div>

            <div class="list-panel-header">
                <div class="list-title">
                    @php
                        $filterEnum = $filter ? \App\Enums\SuKienTrangThai::tryFrom($filter) : null;
                        $filterTitle = $filterEnum ? $filterEnum->label() : str_replace('_', ' ', $filter ?? '');
                    @endphp
                    <h2>{{ $starredOnly ? 'Đã đánh dấu' : ($filter === null ? 'Danh sách sự kiện' : $filterTitle) }}</h2>
                    <span>{{ $suKiens->total() }}</span>
                </div>
                <form class="search-form" method="GET" action="{{ route('sukien.index') }}">
                    @if(request()->filled('ma_clb'))
                        <input type="hidden" name="ma_clb" value="{{ request('ma_clb') }}">
                    @endif
                    @if($starredOnly)
                        <input type="hidden" name="marked" value="1">
                    @elseif($filter !== null)
                        <input type="hidden" name="trang_thai" value="{{ $filter }}">
                    @endif
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên sự kiện...">
                    <button type="submit" aria-label="Tìm kiếm">⌕</button>
                </form>
            </div>

            <form id="bulk-event-form" class="bulk-toolbar" method="POST" action="{{ route('sukien.bulk') }}" data-confirm-delete="Bạn có chắc muốn xóa các sự kiện đã chọn không?">
                @csrf
                @foreach(request()->only('trang_thai', 'marked', 'q', 'page', 'ma_clb') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <span class="selected-count" data-selected-count>Đã chọn 0</span>
                <button type="submit" name="action" value="mark" class="bulk-action">★ Đánh dấu</button>
                <button type="submit" name="action" value="unmark" class="bulk-action">Bỏ đánh dấu</button>
                <button type="submit" name="action" value="delete" class="bulk-action bulk-delete" data-confirm="true">Xóa đã chọn</button>
            </form>

            <div class="event-list">
                <div class="select-all-row">
                    <label class="checkbox-label"><input type="checkbox" data-select-all aria-label="Chọn tất cả sự kiện trên trang"><span></span></label>
                    <span>Tên sự kiện</span>
                    <span>Địa điểm / Thời gian</span>
                    <span>Trạng thái</span>
                    <span class="select-actions-heading">Thao tác</span>
                </div>
                @forelse($suKiens as $item)
                    <article class="event-card">
                        <label class="checkbox-label event-select">
                            <input type="checkbox" name="event_ids[]" value="{{ $item->MaSuKien }}" form="bulk-event-form" data-event-checkbox aria-label="Chọn {{ $item->TenSuKien }}">
                            <span></span>
                        </label>

                        <span class="event-star {{ $item->DaDanhDau ? 'is-marked' : '' }}" aria-label="{{ $item->DaDanhDau ? 'Đã đánh dấu' : 'Chưa đánh dấu' }}" title="{{ $item->DaDanhDau ? 'Đã đánh dấu' : 'Chưa đánh dấu' }}">{{ $item->DaDanhDau ? '★' : '☆' }}</span>

                        <div class="event-main">
                            <a class="event-name" href="{{ route('sukien.edit', array_merge([$item->MaSuKien], request()->only('ma_clb'))) }}">{{ $item->TenSuKien }}</a>
                            <div class="event-badges">
                                @if($item->MaCLB)
                                    <span class="meta-badge">CLB #{{ $item->MaCLB }}</span>
                                @endif
                                @if($item->MaDanhMucSuKien)
                                    <span class="meta-badge">Danh mục #{{ $item->MaDanhMucSuKien }}</span>
                                @endif
                            </div>
                            <div class="event-details">
                                <span>⌖ {{ $item->DiaDiem ?: 'Chưa cập nhật địa điểm' }}</span>
                                <span>◷ {{ $item->ThoiGianBatDau?->format('d/m · H:i') }}@if($item->ThoiGianKetThuc) – {{ $item->ThoiGianKetThuc->format('H:i') }}@endif</span>
                                <span>Đăng ký: {{ $item->ThoiGianMoDangKy?->format('d/m') }} – {{ $item->ThoiGianDongDangKy?->format('d/m') }}</span>
                                <span class="event-capacity">♧ {{ number_format($item->SoLuongToiDa) }} chỗ</span>
                            </div>
                        </div>

                        <span class="checkin-badge checkin-open">
                            <i></i>{{ $item->TrangThai instanceof \App\Enums\SuKienTrangThai ? $item->TrangThai->label() : (\App\Enums\SuKienTrangThai::tryFrom((string)$item->TrangThai)?->label() ?? str_replace('_', ' ', (string)$item->TrangThai)) }}
                        </span>

                        <div class="event-actions">
                            <a href="{{ route('sukien.show', array_merge([$item->MaSuKien], request()->only('ma_clb'))) }}" class="event-action event-action-detail">Chi tiết</a>
                            <a href="{{ route('sukien.edit', array_merge([$item->MaSuKien], request()->only('ma_clb'))) }}" class="event-action event-action-edit">Chỉnh sửa</a>
                            <form action="{{ route('sukien.destroy', array_merge([$item->MaSuKien], request()->only('ma_clb'))) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sự kiện này?')">
                                @csrf
                                @method('DELETE')
                                <button class="event-action event-action-delete" type="submit">Xóa</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <strong>{{ $search || $filter !== null || $starredOnly ? 'Không tìm thấy sự kiện phù hợp' : 'Chưa có sự kiện nào' }}</strong>
                        @if($search || $filter !== null || $starredOnly)
                            <span>Hãy đổi từ khóa hoặc bộ lọc để xem kết quả khác.</span>
                        @else
                            <span>Tạo sự kiện từ trang quản lý câu lạc bộ.</span>
                            <a class="primary-button" href="{{ route('caulacbo.index') }}">Đi đến quản lý câu lạc bộ</a>
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="panel-bottom">
                <span>Hiển thị {{ $suKiens->firstItem() ?? 0 }}–{{ $suKiens->lastItem() ?? 0 }} trong {{ $suKiens->total() }} sự kiện</span>
                @if($suKiens->hasPages())
                    {{ $suKiens->onEachSide(1)->links('pagination::bootstrap-5') }}
                @endif
            </div>
        </div>
    </section>

@endsection
