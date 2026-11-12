@extends('layouts.main')

@section('title', 'Bộ đợt tuyển & Quản lý tuyển sinh CLB | UniClub')

@section('content')
    @php
        $activeManagementTab = 'recruitments';
        $clbCode = 'CLB #' . $club->MaCLB;
        $nearestDateFormatted = isset($nearestDeadline) && $nearestDeadline ? \Carbon\Carbon::parse($nearestDeadline)->format('d/m/Y') : '15/01/2027';
    @endphp

    <div class="sync-status"><span></span> Đã đồng bộ dữ liệu thời gian thực <i>·</i> Cập nhật {{ now()->format('H:i') }}</div>

    @if ($managementNavigation)
        @include('caulacbo.management-tabs')
    @endif

    <section class="page-intro">
        <div>
            <div class="eyebrow">UNICLUB <span>/</span> QUẢN LÝ CÂU LẠC BỘ <span>/</span> ĐỢT TUYỂN THÀNH VIÊN</div>
            <h1>Bộ đợt tuyển &amp; Quản lý tuyển sinh CLB</h1>
            <p>Danh sách đợt tuyển quân &amp; tiếp nhận hồ sơ ứng tuyển thuộc {{ $clbCode }} ({{ $club->TenCLB }}). Quản lý thời gian, chỉ tiêu và trạng thái tuyển dụng.</p>
        </div>
        <div class="intro-actions">
            <a class="primary-button event-create-button" href="{{ route('caulacbo.recruitments.create', $club->MaCLB) }}">＋ <span>Tạo đợt tuyển</span></a>
        </div>
    </section>

    @if(session('success'))
        <div class="notice notice-success" role="status">
            <span class="notice-check">✓</span>
            <span>{{ session('success') }}</span>
            <button type="button" class="notice-close" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="notice notice-error" role="alert" style="border-color: #fecdd3; background: #fff1f2; color: #be123c;">
            <span class="notice-check" style="background: #e11d48;">!</span>
            <span>Vui lòng kiểm tra lại thông tin. {{ $errors->first() }}</span>
        </div>
    @endif

    @if ($newNotifications > 0)
        <div class="notice notice-info" role="status" style="border-color: #bfdbfe; background: #eff6ff; color: #1e40af; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; margin-bottom: 12px; border-radius: 9px;">
            <div style="display: flex; align-items: center; gap: 9px;">
                <span style="font-size: 15px;">🔔</span>
                <div>
                    <strong style="font-size: 10px; color: #1e3a8a;">Thông báo hồ sơ ứng tuyển</strong>
                    <div style="font-size: 8px; color: #3b82f6;">Bạn có <b>{{ $newNotifications }} thông báo ứng tuyển mới</b>. {{ $pendingApplicationCount }} hồ sơ đang chờ xét duyệt.</div>
                </div>
            </div>
            @if ($notifications->isNotEmpty())
                <a class="event-action event-action-primary" style="background: #2563eb; color: #fff; border: 0; min-height: 25px; padding: 0 10px; border-radius: 6px; font-size: 8px; font-weight: 700; text-decoration: none;" href="{{ route('caulacbo.recruitments.applications', [$club->MaCLB, $notifications->first()->data['MaDotTuyen']]) }}">
                    Xem đơn mới nhất
                </a>
            @endif
        </div>
    @endif

    <section class="management-layout">
        <aside class="event-overview">
            <div class="overview-heading">
                <h2>Bộ đợt tuyển</h2>
                <span>{{ $campaignCount }}</span>
            </div>
            <nav class="overview-nav" aria-label="Lọc đợt tuyển">
                <a class="overview-link {{ ($statusFilter === 'all' || !$statusFilter) ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.recruitments.index', array_merge(['caulacbo' => $club->MaCLB, 'status' => 'all'], request()->only('q'))) }}">
                    <span>▤</span><strong>Tất cả</strong><b>{{ $campaignCount }}</b>
                </a>
                <a class="overview-link {{ $statusFilter === 'starred' ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.recruitments.index', array_merge(['caulacbo' => $club->MaCLB, 'status' => 'starred'], request()->only('q'))) }}">
                    <span class="overview-star">★</span><strong>Đã đánh dấu</strong><b>0</b>
                </a>
                <a class="overview-link {{ ($statusFilter === 'open' || $statusFilter === 'DANG_TUYEN') ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.recruitments.index', array_merge(['caulacbo' => $club->MaCLB, 'status' => 'open'], request()->only('q'))) }}">
                    <span>▱</span><strong>Đang mở tuyển</strong><b>{{ $openCampaignCount }}</b>
                </a>
                <a class="overview-link {{ ($statusFilter === 'upcoming' || $statusFilter === 'CHUA_TUYEN') ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.recruitments.index', array_merge(['caulacbo' => $club->MaCLB, 'status' => 'upcoming'], request()->only('q'))) }}">
                    <span>▱</span><strong>Chưa mở tuyển</strong><b>{{ $upcomingCampaignCount }}</b>
                </a>
            </nav>

            <div class="recruitment-overview-stats">
                <div class="recruitment-stat-row">
                    <span>👥 Hồ sơ đã tiếp nhận</span>
                    <strong>{{ $applicationCount }}</strong>
                </div>
                <div class="recruitment-stat-row">
                    <span>🕒 Hạn chót gần nhất</span>
                    <strong>{{ $nearestDateFormatted }}</strong>
                </div>
            </div>
        </aside>

        <div class="events-panel">
            <div class="overview-banner">
                <div class="overview-banner-top">
                    <div>
                        <strong>{{ $campaignCount }} <span>đợt tuyển đang quản lý</span></strong>
                        <small>Tổng {{ $campaignCount }} đợt tuyển trong hệ thống tiếp nhận hồ sơ {{ $clbCode }}</small>
                    </div>
                    <div class="overview-legend">
                        <span><i class="legend-internal"></i> Đang mở tuyển: {{ $openCampaignCount }}</span>
                        <span><i class="legend-public"></i> Chưa mở tuyển: {{ $upcomingCampaignCount }}</span>
                    </div>
                </div>
                <div class="overview-progress">
                    <span style="width: {{ $campaignCount > 0 ? max(20, round(($openCampaignCount / $campaignCount) * 100)) : 0 }}%"></span>
                </div>
            </div>

            <div class="list-panel-header">
                <div class="list-title">
                    <h2>Danh sách đợt tuyển</h2>
                    <span>{{ $campaigns->total() }}</span>
                </div>
                <form class="search-form" method="GET" action="{{ route('caulacbo.recruitments.index', $club->MaCLB) }}">
                    @if ($statusFilter && $statusFilter !== 'all')
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên đợt tuyển, mã đợt...">
                    <button type="submit" aria-label="Tìm kiếm">⌕</button>
                </form>
            </div>

            <div class="event-list">
                <div class="recruitment-select-all">
                    <label class="checkbox-label"><input type="checkbox" data-select-all aria-label="Chọn tất cả đợt tuyển trên trang"><span></span></label>
                    <span>Tên đợt tuyển &amp; thông tin</span>
                    <span>Thời gian / Chỉ tiêu</span>
                    <span>Trạng thái</span>
                    <span class="select-actions-heading" style="text-align: right;">Thao tác</span>
                </div>

                @forelse ($campaigns as $campaign)
                    @php
                        $statusEnum = $campaign->TrangThai?->value ?? ($campaign->LaBanNhap ? 'BAN_NHAP' : 'DANG_TUYEN');
                        $isDangTuyen = $statusEnum === 'DANG_TUYEN' && ! $campaign->LaBanNhap;
                        $campaignCode = 'DT-' . ($campaign->created_at ? $campaign->created_at->format('Y') : date('Y')) . '-' . sprintf('%02d', $campaign->MaDotTuyen);
                    @endphp
                    <article class="recruitment-card">
                        <label class="checkbox-label event-select">
                            <input type="checkbox" name="recruitment_ids[]" value="{{ $campaign->MaDotTuyen }}" aria-label="Chọn {{ $campaign->TenDotTuyen }}">
                            <span></span>
                        </label>

                        <span class="event-star" aria-label="Chưa đánh dấu" title="Chưa đánh dấu">☆</span>

                        <div class="event-main">
                            <a class="event-name" href="{{ $isDangTuyen ? route('dot-tuyen.show', $campaign->MaDotTuyen) : route('caulacbo.recruitments.applications', [$club->MaCLB, $campaign->MaDotTuyen]) }}">
                                {{ $campaign->TenDotTuyen }}
                            </a>
                            <div class="event-badges">
                                <span class="meta-badge">Mã: {{ $campaignCode }}</span>
                                <span class="meta-badge">{{ $clbCode }}</span>
                            </div>
                            <div class="event-details">
                                <span style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 280px;">
                                    {{ \Illuminate\Support\Str::limit($campaign->MoTa ?: 'Chưa có mô tả đợt tuyển.', 75) }}
                                </span>
                            </div>
                        </div>

                        <div class="recruitment-time-col">
                            <span>🕒 {{ $campaign->NgayBatDau?->format('d/m/Y') ?? '20/12/2026' }} - {{ $campaign->NgayKetThuc?->format('d/m/Y') ?? '15/01/2027' }}</span>
                            @if ($campaign->application_count > 0)
                                <span style="color: #2563eb; font-weight: 700;">📋 Đã nộp: <b>{{ $campaign->application_count }}</b> / {{ $campaign->target_count }} chỉ tiêu</span>
                            @else
                                <span>👥 Chỉ tiêu: <b>{{ $campaign->target_count }}</b> thành viên</span>
                            @endif
                        </div>

                        @php
                            $badgeLabel = 'Chưa mở tuyển';
                            if ($campaign->LaBanNhap) {
                                $badgeLabel = 'Bản nháp';
                            } elseif ($statusEnum === 'DANG_TUYEN' || $statusEnum === \App\Enums\DotTuyenTrangThai::DANG_TUYEN->value) {
                                $badgeLabel = 'Đang mở tuyển';
                            } elseif ($statusEnum === 'CHUA_TUYEN' || $statusEnum === \App\Enums\DotTuyenTrangThai::CHUA_TUYEN->value) {
                                $badgeLabel = 'Chưa mở tuyển';
                            } elseif ($statusEnum === 'DA_DONG') {
                                $badgeLabel = 'Đã đóng tuyển';
                            }
                        @endphp
                        <div class="recruitment-status-col">
                            <span class="checkin-badge {{ $isDangTuyen ? 'checkin-open' : '' }}" style="{{ $campaign->LaBanNhap ? 'background: #f1f5f9; color: #64748b;' : '' }}">
                                <i></i>{{ $badgeLabel }}
                            </span>
                        </div>

                        <div class="event-actions">
                            <a href="{{ $campaign->LaBanNhap ? route('caulacbo.recruitments.edit', [$club->MaCLB, $campaign->MaDotTuyen]) : route('caulacbo.recruitments.applications', [$club->MaCLB, $campaign->MaDotTuyen]) }}" class="event-action">Chi tiết</a>
                            <a href="{{ route('caulacbo.recruitments.edit', [$club->MaCLB, $campaign->MaDotTuyen]) }}" class="event-action">Chỉnh sửa</a>
                            <form action="{{ route('caulacbo.recruitments.destroy', [$club->MaCLB, $campaign->MaDotTuyen]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đợt tuyển này?');">
                                @csrf
                                @method('DELETE')
                                <button class="event-action event-action-delete" type="submit">Xóa</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <div style="font-size: 26px; line-height: 1; margin-bottom: 2px;">📁</div>
                        <strong>{{ $search || $statusFilter !== 'all' ? 'Không tìm thấy đợt tuyển phù hợp' : 'Chưa có đợt tuyển nào' }}</strong>
                        @if ($search || $statusFilter !== 'all')
                            <span>Hãy đổi từ khóa hoặc bộ lọc để xem kết quả khác.</span>
                        @else
                            <span>Bấm "+ Tạo đợt tuyển" để khởi tạo đợt tuyển sinh mới cho câu lạc bộ.</span>
                            <a class="primary-button event-create-button" style="margin-top: 6px;" href="{{ route('caulacbo.recruitments.create', $club->MaCLB) }}">＋ <span>Tạo đợt tuyển</span></a>
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="panel-bottom">
                <span>Hiển thị {{ $campaigns->firstItem() ?? 0 }}–{{ $campaigns->lastItem() ?? 0 }} trong {{ $campaigns->total() }} đợt tuyển</span>
                @if ($campaigns->hasPages())
                    {{ $campaigns->onEachSide(1)->links('pagination::bootstrap-5') }}
                @endif
            </div>
        </div>
    </section>

    <!-- Compact Sync Notice Card -->
    <div class="recruitment-sync-banner">
        <div class="sync-left">
            <span class="sync-icon">👥</span>
            <div>
                <strong>Đồng bộ tự động cổng đăng ký sinh viên</strong>
                <span>Mọi đợt tuyển có trạng thái Đang mở tuyển sẽ ngay lập tức được xuất bản công khai lên Cổng Đăng ký để ứng viên nộp CV trực tuyến.</span>
            </div>
        </div>
        <a class="event-action" style="padding: 0 10px; font-weight: 700; color: #2563eb; border-color: #bfdbfe; background: #eff6ff; text-decoration: none;" href="{{ route('home') }}">
            👁 Xem giao diện ứng viên
        </a>
    </div>
@endsection
