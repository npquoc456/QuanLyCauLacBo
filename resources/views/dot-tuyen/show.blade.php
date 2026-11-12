@extends('layouts.main')

@section('title', $campaign->TenDotTuyen.' | UniClub')

@section('content')
    <div class="recruitment-public-page">
        <a class="recruitment-back-link" href="{{ route('home') }}">‹ Quay lại trang chủ</a>
        @if (session('success'))
            <div class="recruitment-alert recruitment-alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="recruitment-alert recruitment-alert-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="recruitment-public-hero">
            @if ($campaign->URLPoster)
                <img src="{{ asset($campaign->URLPoster) }}" alt="Poster {{ $campaign->TenDotTuyen }}" onerror="this.onerror=null;this.src='{{ asset('image/img.png') }}';">
            @else
                <div class="recruitment-public-poster-placeholder"></div>
            @endif
            <div class="recruitment-public-hero-overlay"></div>
            <div class="recruitment-public-hero-copy">
                <span>♧ {{ $campaign->cauLacBo->TenCLB }} · ĐANG MỞ ĐƠN</span>
                <h1>{{ $campaign->TenDotTuyen }}</h1>
                <p>Nhận hồ sơ đến {{ $campaign->NgayKetThuc->format('d/m/Y H:i') }}</p>
            </div>
        </section>

        <div class="recruitment-public-grid">
            <main class="recruitment-public-main">
                <section class="recruitment-public-card">
                    <h2>Thông tin đợt tuyển</h2>
                    <p class="recruitment-public-description">{{ $campaign->MoTa ?: 'Câu lạc bộ đang tuyển thành viên mới. Xem các vị trí và gửi hồ sơ ứng tuyển bên dưới.' }}</p>
                    <div class="recruitment-public-facts">
                        <div><span>ĐỐI TƯỢNG</span><strong>{{ $campaign->DoiTuong ?: 'Sinh viên toàn trường' }}</strong></div>
                        <div><span>CHỈ TIÊU</span><strong>{{ $campaign->target_count }} thành viên</strong></div>
                        <div><span>HẠN ỨNG TUYỂN</span><strong>{{ $campaign->NgayKetThuc->format('d/m/Y H:i') }}</strong></div>
                        <div><span>PHỎNG VẤN</span><strong>{{ $campaign->NgayPhongVan?->format('d/m/Y H:i') ?? 'Sẽ thông báo sau' }}</strong></div>
                        <div><span>ĐỊA ĐIỂM</span><strong>{{ $campaign->DiaDiemPhongVan ?: 'Sẽ cập nhật sau' }}</strong></div>
                        <div><span>CÔNG BỐ KẾT QUẢ</span><strong>{{ $campaign->NgayCongBoKetQua?->format('d/m/Y H:i') ?? 'Sẽ thông báo sau' }}</strong></div>
                    </div>
                </section>

                <section class="recruitment-public-card">
                    <h2>Các vị trí đang tuyển</h2>
                    @foreach ($campaign->viTriTuyens as $position)
                        <article class="recruitment-public-position">
                            <div class="recruitment-public-position-heading">
                                <div><h3>{{ $position->TenViTri }}</h3><span>{{ $position->ban?->TenBan ?: $campaign->cauLacBo->TenCLB }}</span></div>
                                <b>{{ $position->SoLuong }} chỉ tiêu</b>
                            </div>
                            @if ($position->Mota)<p>{{ $position->Mota }}</p>@endif

                            @if (in_array($position->MaViTri, $campaign->applied_position_ids, true))
                                <span class="recruitment-applied-state">✓ Bạn đã gửi hồ sơ cho vị trí này</span>
                            @elseif (auth()->check())
                                <form class="recruitment-apply-form" method="POST" action="{{ route('dot-tuyen.apply', $campaign->MaDotTuyen) }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="MaViTri" value="{{ $position->MaViTri }}">
                                    <label><span>Lời giới thiệu / Lý do ứng tuyển</span><textarea name="NoiDung" rows="3" maxlength="500" placeholder="Giới thiệu ngắn về bản thân và lý do bạn muốn tham gia...">{{ old('NoiDung') }}</textarea></label>
                                    <label><span>Hồ sơ đính kèm (PDF, DOC, DOCX · tối đa 10 MB)</span><input type="file" name="resume" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"></label>
                                    <button class="recruitment-submit-button" type="submit">Gửi hồ sơ ứng tuyển</button>
                                </form>
                            @else
                                <a class="recruitment-submit-button recruitment-login-to-apply" href="{{ route('login') }}">Đăng nhập để ứng tuyển</a>
                            @endif
                        </article>
                    @endforeach
                </section>
            </main>
            <aside class="recruitment-public-club-card">
                @if ($campaign->cauLacBo->URLLogo)
                    <img src="{{ $campaign->cauLacBo->URLLogo }}" alt="Logo {{ $campaign->cauLacBo->TenCLB }}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                    <span class="recruitment-public-club-mark" style="display: none;">♧</span>
                @else
                    <span class="recruitment-public-club-mark">♧</span>
                @endif
                <h2>{{ $campaign->cauLacBo->TenCLB }}</h2>
                <p>{{ $campaign->cauLacBo->MoTa ?: 'Câu lạc bộ đang tìm kiếm những thành viên mới cùng phát triển.' }}</p>
                @if ($campaign->cauLacBo->Email)<a href="mailto:{{ $campaign->cauLacBo->Email }}">{{ $campaign->cauLacBo->Email }}</a>@endif
            </aside>
        </div>
    </div>
@endsection
