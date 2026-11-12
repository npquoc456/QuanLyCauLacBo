@extends('layouts.main')

@section('title', 'Cài đặt tài khoản | UniClub')

@section('content')
    <section class="page-intro">
        <div>
            <div class="eyebrow">UNICLUB <span>/</span> TÀI KHOẢN <span>/</span> CÀI ĐẶT</div>
            <h1>Cài đặt tài khoản</h1>
            <p>Cập nhật thông tin cá nhân và ảnh đại diện của bạn.</p>
        </div>
    </section>

    <section class="auth-settings-card">
        <div class="auth-settings-avatar">
            @if ($user->URLAnhDaiDien)
                <img src="{{ $user->URLAnhDaiDien }}" alt="Ảnh đại diện của {{ $user->HoTen }}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-flex';">
                <span class="auth-settings-avatar-fallback" style="display: none;">{{ mb_strtoupper(mb_substr($user->HoTen, 0, 1)) }}</span>
            @else
                <span class="auth-settings-avatar-fallback">{{ mb_strtoupper(mb_substr($user->HoTen, 0, 1)) }}</span>
            @endif
        </div>
        <div class="auth-settings-intro">
            <strong>{{ $user->HoTen }}</strong>
            <span>Chỉnh sửa các thông tin bên dưới để cập nhật hồ sơ của bạn.</span>
        </div>

        @if (session('status'))
            <p class="auth-settings-status" role="status">{{ session('status') }}</p>
        @endif

        <form class="auth-settings-form" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label class="auth-settings-field auth-settings-field-wide">
                <span>Ảnh đại diện</span>
                <input type="file" name="URLAnhDaiDien" accept="image/*">
                @error('URLAnhDaiDien')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Họ và tên</span>
                <input type="text" name="HoTen" value="{{ old('HoTen', $user->HoTen) }}" required maxlength="255">
                @error('HoTen')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Tên tài khoản</span>
                <input type="text" name="TenTaiKhoan" value="{{ old('TenTaiKhoan', $user->TenTaiKhoan) }}" required maxlength="255">
                @error('TenTaiKhoan')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Mã số sinh viên</span>
                <input type="text" name="MSSV" value="{{ old('MSSV', $user->MSSV) }}" required maxlength="20">
                @error('MSSV')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Email</span>
                <input type="email" name="Email" value="{{ old('Email', $user->Email) }}" required maxlength="255">
                @error('Email')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Số điện thoại</span>
                <input type="tel" name="SDT" value="{{ old('SDT', $user->SDT) }}" maxlength="30">
                @error('SDT')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Ngày sinh</span>
                <input type="date" name="NgaySinh" value="{{ old('NgaySinh', $user->NgaySinh?->format('Y-m-d')) }}" required>
                @error('NgaySinh')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Khoa</span>
                <input type="text" name="Khoa" value="{{ old('Khoa', $user->Khoa) }}" maxlength="255">
                @error('Khoa')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Ngành</span>
                <input type="text" name="Nganh" value="{{ old('Nganh', $user->Nganh) }}" maxlength="255">
                @error('Nganh')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Khóa học</span>
                <input type="text" name="KhoaHoc" value="{{ old('KhoaHoc', $user->KhoaHoc) }}" maxlength="50">
                @error('KhoaHoc')<small>{{ $message }}</small>@enderror
            </label>

            <div class="auth-settings-section-title">
                <strong>Đổi mật khẩu</strong>
                <span>Bỏ trống nếu bạn không muốn thay đổi mật khẩu.</span>
            </div>

            <label class="auth-settings-field">
                <span>Mật khẩu hiện tại</span>
                <input type="password" name="current_password" autocomplete="current-password">
                @error('current_password')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Mật khẩu mới</span>
                <input type="password" name="new_password" autocomplete="new-password" minlength="8">
                @error('new_password')<small>{{ $message }}</small>@enderror
            </label>

            <label class="auth-settings-field">
                <span>Xác nhận mật khẩu mới</span>
                <input type="password" name="new_password_confirmation" autocomplete="new-password" minlength="8">
            </label>

            <div class="auth-settings-actions">
                <a class="auth-settings-cancel" href="{{ route('home') }}">Quay lại trang chủ</a>
                <button type="submit">Lưu thay đổi</button>
            </div>
        </form>
    </section>
@endsection
