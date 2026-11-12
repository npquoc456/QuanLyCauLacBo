@extends('layouts.main')

@section('title', 'Chi tiết ứng viên | UniClub')

@section('content')
    <div class="sync-status"><span></span> HỆ THỐNG VẬN HÀNH CLB SINH VIÊN</div>

    @php
        $activeManagementTab = 'recruitments';
    @endphp
    @include('caulacbo.management-tabs')

    @php
        $applicant = $application->nguoiUngTuyen;
    @endphp
    <div class="club-members-breadcrumb">
        <a href="{{ route('caulacbo.recruitments.applications', [$club->MaCLB, $application->viTri->MaDotTuyen]) }}">HỒ SƠ ỨNG TUYỂN</a><span>/</span>
        <strong>CHI TIẾT ỨNG VIÊN</strong>
    </div>
    @if (session('success'))
        <div class="recruitment-alert recruitment-alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="recruitment-page-intro">
        <div>
            <p class="recruitment-eyebrow">{{ $application->viTri->dotTuyen->TenDotTuyen }} / HỒ SƠ #{{ $application->MaDon }}</p>
            <h1>Thông tin ứng viên</h1>
            <p>Kiểm tra thông tin cá nhân, vị trí ứng tuyển và hồ sơ đính kèm.</p>
        </div>
        <a class="recruitment-secondary-button" href="{{ route('caulacbo.recruitments.applications', [$club->MaCLB, $application->viTri->MaDotTuyen]) }}">‹ Danh sách hồ sơ</a>
    </section>

    <div class="recruitment-profile-layout">
        <section class="recruitment-profile-card">
            <div class="recruitment-profile-head">
                <div class="recruitment-applicant-avatar recruitment-profile-avatar">
                    @if ($applicant->URLAnhDaiDien)
                        <img src="{{ $applicant->URLAnhDaiDien }}" alt="Ảnh đại diện {{ $applicant->HoTen }}">
                    @else
                        {{ mb_strtoupper(mb_substr($applicant->HoTen, 0, 1)) }}
                    @endif
                </div>
                <div><h2>{{ $applicant->HoTen }}</h2><span>{{ $applicant->MSSV ?: 'Chưa cập nhật MSSV' }} · {{ $applicant->Email ?: 'Chưa cập nhật email' }}</span></div>
            </div>
            <dl class="recruitment-profile-details">
                <div><dt>Tên tài khoản</dt><dd>{{ $applicant->TenTaiKhoan }}</dd></div>
                <div><dt>Số điện thoại</dt><dd>{{ $applicant->SDT ?: 'Chưa cập nhật' }}</dd></div>
                <div><dt>Ngày sinh</dt><dd>{{ $applicant->NgaySinh?->format('d/m/Y') ?? 'Chưa cập nhật' }}</dd></div>
                <div><dt>Khoa</dt><dd>{{ $applicant->Khoa ?: 'Chưa cập nhật' }}</dd></div>
                <div><dt>Ngành</dt><dd>{{ $applicant->Nganh ?: 'Chưa cập nhật' }}</dd></div>
                <div><dt>Khóa học</dt><dd>{{ $applicant->KhoaHoc ?: 'Chưa cập nhật' }}</dd></div>
            </dl>
        </section>

        <section class="recruitment-profile-card">
            <h2>Hồ sơ ứng tuyển</h2>
            <dl class="recruitment-profile-details recruitment-application-details">
                <div><dt>Vị trí</dt><dd>{{ $application->viTri->TenViTri }}</dd></div>
                <div><dt>Đợt tuyển</dt><dd>{{ $application->viTri->dotTuyen->TenDotTuyen }}</dd></div>
                <div><dt>Ngày nộp</dt><dd>{{ $application->NgayNopDon?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}</dd></div>
                <div><dt>Trạng thái</dt><dd>{{ $application->TrangThai?->label() ?? 'Chờ xử lý' }}</dd></div>
            </dl>
            <div class="recruitment-motivation">
                <strong>Lời giới thiệu / Lý do ứng tuyển</strong>
                <p>{{ $application->NoiDung ?: 'Ứng viên chưa nhập lời giới thiệu.' }}</p>
            </div>
            @if ($application->URLHoSo)
                <a class="recruitment-resume-link" href="{{ route('caulacbo.recruitments.resume', [$club->MaCLB, $application->MaDon]) }}">▣ Tải hồ sơ đính kèm</a>
            @else
                <p class="recruitment-no-resume">Ứng viên chưa đính kèm hồ sơ.</p>
            @endif
        </section>
    </div>

    <section class="recruitment-review-card">
        <div><h2>Cập nhật kết quả xét tuyển</h2><p>Trạng thái này được ghi nhận cho hồ sơ ứng tuyển.</p></div>
        <form method="POST" action="{{ route('caulacbo.recruitments.review', [$club->MaCLB, $application->MaDon]) }}">
            @csrf
            @method('PATCH')
            <label><span>Ghi chú cho ban tuyển</span><textarea name="GhiChu" rows="2" maxlength="1024">{{ old('GhiChu', $application->GhiChu) }}</textarea></label>
            <div>
                <button class="recruitment-secondary-button" type="submit" name="TrangThai" value="DANG_XET_DUYET">Đang xét duyệt</button>
                <button class="recruitment-submit-button" type="submit" name="TrangThai" value="DA_DUYET">Duyệt ứng viên</button>
                <button class="recruitment-reject-button" type="submit" name="TrangThai" value="TU_CHOI">Từ chối</button>
            </div>
        </form>
    </section>
@endsection
