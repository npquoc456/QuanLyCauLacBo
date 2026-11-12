@extends('layouts.main')

@section('title', 'Hồ sơ ứng tuyển | UniClub')

@section('content')
    <div class="sync-status"><span></span> HỆ THỐNG VẬN HÀNH CLB SINH VIÊN</div>

    @php
        $activeManagementTab = 'recruitments';
    @endphp
    @include('caulacbo.management-tabs')

    <div class="club-members-breadcrumb">
        <a href="{{ route('caulacbo.recruitments.index', $club->MaCLB) }}">ĐỢT TUYỂN</a><span>/</span>
        <strong>HỒ SƠ ỨNG TUYỂN</strong>
    </div>
    @if (session('success'))
        <div class="recruitment-alert recruitment-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="recruitment-page-intro">
        <div>
            <p class="recruitment-eyebrow">{{ $club->TenCLB }} / HỒ SƠ ỨNG TUYỂN</p>
            <h1>Ứng viên — {{ $campaign->TenDotTuyen }}</h1>
            <p>Xem hồ sơ, thông tin liên hệ và cập nhật trạng thái xét tuyển.</p>
        </div>
        <a class="recruitment-secondary-button" href="{{ route('caulacbo.recruitments.index', $club->MaCLB) }}">‹ Danh sách đợt tuyển</a>
    </section>

    <section class="recruitment-application-summary">
        <span><strong>{{ $applications->total() }}</strong> hồ sơ</span>
        <span><strong>{{ $pendingCount }}</strong> đang chờ xử lý</span>
        <nav aria-label="Lọc hồ sơ">
            <a class="{{ $statusFilter === '' ? 'is-active' : '' }}" href="{{ route('caulacbo.recruitments.applications', [$club->MaCLB, $campaign->MaDotTuyen]) }}">Tất cả</a>
            @foreach (\App\Enums\DonUngTrangThai::cases() as $applicationStatus)
                <a class="{{ $statusFilter === $applicationStatus->value ? 'is-active' : '' }}"
                   href="{{ route('caulacbo.recruitments.applications', ['caulacbo' => $club->MaCLB, 'dotTuyen' => $campaign->MaDotTuyen, 'status' => $applicationStatus->value]) }}">
                    {{ $applicationStatus->label() }}
                </a>
            @endforeach
        </nav>
    </section>

    <section class="recruitment-applicant-list">
        @forelse ($applications as $application)
            @php
                $applicant = $application->nguoiUngTuyen;
            @endphp
            <article class="recruitment-applicant-row">
                <div class="recruitment-applicant-avatar">
                    @if ($applicant->URLAnhDaiDien)
                        <img src="{{ $applicant->URLAnhDaiDien }}" alt="">
                    @else
                        {{ mb_strtoupper(mb_substr($applicant->HoTen, 0, 1)) }}
                    @endif
                </div>
                <div class="recruitment-applicant-info">
                    <a href="{{ route('caulacbo.recruitments.application', [$club->MaCLB, $application->MaDon]) }}">{{ $applicant->HoTen }}</a>
                    <span>{{ $applicant->MSSV ?: 'Chưa cập nhật MSSV' }} · {{ $applicant->Email }}</span>
                    <small>Ứng tuyển {{ $application->viTri->TenViTri }} · {{ $application->NgayNopDon?->format('d/m/Y H:i') }}</small>
                </div>
                <span class="recruitment-application-status status-{{ strtolower($application->TrangThai?->value ?? 'cho_xu_ly') }}">{{ $application->TrangThai?->label() ?? 'Chờ xử lý' }}</span>
                <a class="recruitment-action-detail" href="{{ route('caulacbo.recruitments.application', [$club->MaCLB, $application->MaDon]) }}">Xem hồ sơ</a>
            </article>
        @empty
            <div class="recruitment-empty-state">
                <span>♙</span><strong>Chưa có hồ sơ ứng tuyển</strong>
                <p>Hồ sơ từ sinh viên sẽ xuất hiện tại đây sau khi ứng tuyển.</p>
            </div>
        @endforelse
        <footer class="recruitment-list-footer">
            <span>Hiển thị {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }} trong {{ $applications->total() }} hồ sơ</span>
            {{ $applications->links() }}
        </footer>
    </section>
@endsection
