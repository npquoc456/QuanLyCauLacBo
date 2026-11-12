@extends('layouts.main')

@section('title', 'Điểm danh & QR | ' . $suKien->TenSuKien)

@section('content')
    <section class="attendance-page">
        <div class="attendance-heading">
            <div>
                <div class="eyebrow">UNICLUB <span>/</span> QUẢN LÝ SỰ KIỆN <span>/</span> ĐIỂM DANH</div>
                <h1>Điểm danh &amp; Trình chiếu QR</h1>
                <p>Bảng điều khiển điểm danh cho sự kiện. Thông tin người tham gia sẽ xuất hiện khi được kết nối.</p>
            </div>
            <div class="attendance-heading-actions">
                <span class="attendance-status">
                    <i></i>Trạng thái: {{ str_replace('_', ' ', $suKien->TrangThai->value) }}
                </span>
                <button class="secondary-button" type="button" data-fullscreen-toggle>⛶ Trình chiếu toàn màn hình</button>
                <a class="secondary-button" href="{{ route('sukien.show', $suKien->MaSuKien) }}">Chi tiết sự kiện</a>
            </div>
        </div>

        <article class="attendance-event-banner">
            <span class="attendance-event-icon" aria-hidden="true">⌁</span>
            <div class="attendance-event-copy">
                <span>{{ $suKien->DiaDiem ?: 'Chưa cập nhật địa điểm' }}</span>
                <h2>{{ $suKien->TenSuKien }}</h2>
            </div>
            <div class="attendance-event-state">
                <strong>{{ str_replace('_', ' ', $suKien->TrangThai->value) }}</strong>
                <span>Sức chứa tối đa: {{ number_format($suKien->SoLuongToiDa) }}</span>
            </div>
        </article>

        <section class="attendance-main-grid" aria-label="Mã QR điểm danh">
            <div class="attendance-qr-card">
                <div class="attendance-qr-placeholder" aria-hidden="true">
                    <span class="attendance-qr-symbol">QR</span>
                    <span class="attendance-qr-corner attendance-qr-corner-top-left"></span>
                    <span class="attendance-qr-corner attendance-qr-corner-top-right"></span>
                    <span class="attendance-qr-corner attendance-qr-corner-bottom-left"></span>
                </div>
                <div>
                    <span class="attendance-section-kicker">ĐIỂM DANH SỰ KIỆN</span>
                    <h2>Mã QR chưa khả dụng</h2>
                    <p>Hiện hệ thống chưa có luồng quét và lưu lượt check-in. Mã QR sẽ hiển thị tại đây khi chức năng điểm danh được kết nối.</p>
                    <a class="secondary-button" href="{{ route('sukien.edit', $suKien->MaSuKien) }}">Kiểm tra thông tin sự kiện</a>
                </div>
            </div>
            <aside class="attendance-session-card">
                <span class="attendance-section-kicker">THÔNG TIN SỰ KIỆN</span>
                <h2>{{ $suKien->TenSuKien }}</h2>
                <dl>
                    <div><dt>Thời gian bắt đầu</dt><dd>{{ $suKien->ThoiGianBatDau?->format('d/m/Y H:i') ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Thời gian kết thúc</dt><dd>{{ $suKien->ThoiGianKetThuc?->format('d/m/Y H:i') ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>Trạng thái sự kiện</dt><dd>{{ str_replace('_', ' ', $suKien->TrangThai->value) }}</dd></div>
                </dl>
            </aside>
        </section>

        <section class="attendance-stats" aria-label="Tổng quan lượt điểm danh">
            <article class="attendance-stat-card">
                <span>ĐÃ CHECK-IN</span>
                <strong>—</strong>
                <small>Chưa có dữ liệu điểm danh</small>
            </article>
            <article class="attendance-stat-card">
                <span>TỰ QUÉT QR</span>
                <strong>—</strong>
                <small>Chưa có dữ liệu điểm danh</small>
            </article>
            <article class="attendance-stat-card">
                <span>ĐIỂM DANH THỦ CÔNG</span>
                <strong>—</strong>
                <small>Chưa có dữ liệu điểm danh</small>
            </article>
            <article class="attendance-stat-card">
                <span>SỨC CHỨA</span>
                <strong>{{ number_format($suKien->SoLuongToiDa) }}</strong>
                <small>Số người tham gia tối đa</small>
            </article>
        </section>

        <section class="attendance-roster">
            <div class="attendance-roster-heading">
                <div>
                    <h2>Danh sách điểm danh</h2>
                    <p>Danh sách sẽ được cập nhật khi dữ liệu thành viên được kết nối.</p>
                </div>
                <a class="secondary-button" href="{{ route('sukien.show', $suKien->MaSuKien) }}">← Quay lại chi tiết</a>
            </div>
            <div class="attendance-empty">
                <span class="attendance-empty-icon" aria-hidden="true">✓</span>
                <strong>Chưa có lượt điểm danh</strong>
                <span>Hiện chưa có dữ liệu check-in được lưu cho sự kiện này.</span>
            </div>
        </section>
    </section>
@endsection
