<section class="club-joined-page">
    <div class="eyebrow">UNICLUB <span>/</span> CÂU LẠC BỘ ĐÃ THAM GIA</div>
    <section class="club-joined-intro">
        <div>
            <h1>Câu lạc bộ bạn đang tham gia</h1>
            <p>Theo dõi các câu lạc bộ, ban chuyên môn và hoạt động bạn đã gia nhập với vai trò thành viên hoặc cộng tác viên.</p>
        </div>
    </section>

    <section class="club-joined-summary" aria-label="Tổng quan câu lạc bộ đã tham gia">
        <div class="club-joined-summary-top">
            <div>
                <strong>{{ $joinedClubCount }} <span>câu lạc bộ đang tham gia</span></strong>
                <small>THÀNH VIÊN CHÍNH THỨC: {{ $officialCount }} <span>·</span> CỘNG TÁC VIÊN: {{ $collaboratorCount }}</small>
            </div>
            <div class="overview-legend">
                <span><i class="legend-internal"></i> Đang hoạt động: {{ $activeClubCount }}</span>
                <span><i class="legend-public"></i> Khác: {{ $otherClubCount }}</span>
            </div>
        </div>
        <div class="overview-progress"><span style="width: {{ $joinedClubCount > 0 ? round($activeClubCount / $joinedClubCount * 100) : 0 }}%"></span></div>
    </section>

    <section class="club-joined-list-panel">
        <div class="list-panel-header">
            <div class="list-title">
                <h2>Danh sách câu lạc bộ tham gia</h2>
                <span>{{ count($joinedClubs) }}</span>
            </div>
            <form class="search-form" method="GET" action="{{ route('caulacbo.index') }}">
                <input type="hidden" name="tab" value="joined">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Tìm tên câu lạc bộ...">
                <button type="submit" aria-label="Tìm kiếm">⌕</button>
            </form>
        </div>

        <div class="club-joined-table">
            <div class="club-joined-heading">
                <span>Câu lạc bộ</span>
                <span>Vai trò / Liên hệ</span>
                <span>Trạng thái</span>
                <span>Thao tác</span>
            </div>
            @forelse($joinedClubs as $club)
                @php
                    $cauLacBo = $club->cauLacBo;
                    $latestEvent = $cauLacBo->latest_event;
                    $status = $club->TrangThai?->value ?? 'CHUA_CAP_NHAT';
                    $statusLabel = str_replace('_', ' ', $status);
                    $clubCode = 'CLB-'.str_pad((string) $club->MaCLB, 4, '0', STR_PAD_LEFT);
                @endphp
                <article class="club-joined-row">
                    <div class="club-joined-main">
                        @if ($cauLacBo->URLLogo)
                            <img class="club-joined-avatar" src="{{ $cauLacBo->URLLogo }}" alt="Logo {{ $cauLacBo->TenCLB }}" loading="lazy">
                        @else
                            <span class="club-joined-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($cauLacBo->TenCLB, 0, 1)) }}</span>
                        @endif
                        <div>
                            <strong>{{ $cauLacBo->TenCLB }}</strong>
                            <span class="meta-badge">{{ $clubCode }}</span>
                            <small>{{ $latestEvent ? 'SỰ KIỆN LIÊN KẾT GẦN NHẤT · '.$latestEvent->TenSuKien : 'CHƯA CÓ SỰ KIỆN LIÊN KẾT' }}</small>
                        </div>
                    </div>
                    <div class="club-joined-contact">
                        <strong>{{ $club->ChucVu }}</strong>
                        <span>{{ $cauLacBo->Email ?: 'Chưa có email' }}</span>
                    </div>
                    <span class="checkin-badge {{ $status === \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->value ? 'checkin-open' : '' }}"><i></i>{{ $statusLabel }}</span>
                    <div class="club-joined-actions">
                        <a class="event-action" href="{{ route('sukien.index', ['ma_clb' => $cauLacBo->MaCLB]) }}">Xem sự kiện</a>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <strong>{{ $search ? 'Không tìm thấy câu lạc bộ phù hợp' : 'Chưa có câu lạc bộ nào' }}</strong>
                    <span>{{ $search ? 'Hãy thử tên câu lạc bộ, mã CLB hoặc vai trò khác.' : 'Các câu lạc bộ bạn tham gia sẽ hiển thị tại đây.' }}</span>
                </div>
            @endforelse
        </div>
        <div class="panel-bottom">
            <span>Hiển thị {{ $joinedClubs->count() ? '1–'.$joinedClubs->count() : '0–0' }} trong {{ $joinedClubCount }} câu lạc bộ</span>
        </div>
    </section>

</section>
