@extends('layouts.auth')

@section('title', 'Đăng ký')
@section('auth-label', 'ĐĂNG KÝ THÀNH VIÊN')
@section('auth-card-class', 'auth-register-card')

@section('auth-content')
    <div class="auth-form-heading auth-register-heading">
        <span class="auth-overline">THAM GIA CỘNG ĐỒNG UNICLUB</span>
        <h2>Tạo tài khoản UniClub!</h2>
        <p>Đăng ký để tham gia hệ thống quản lý câu lạc bộ, quét QR điểm danh và tích lũy Điểm Rèn Luyện (ĐRL).</p>
    </div>

    <div class="auth-alert auth-alert-error" data-auth-message role="alert" hidden></div>
    <button class="auth-google-button" type="button" disabled title="Đăng ký Google chưa được cấu hình">
        <span aria-hidden="true">G</span> Đăng ký nhanh với Email Sinh viên trường (@edu.vn)
    </button>
    <div class="auth-divider"><span>HOẶC ĐĂNG KÝ BẰNG BIỂU MẪU</span></div>

    <form class="auth-form auth-register-form" data-auth-form data-endpoint="{{ url('/api/auth/register') }}" data-form-type="register">
        <label class="auth-field">
            <span>Tên tài khoản</span>
            <span class="auth-input-wrap"><i aria-hidden="true">♙</i><input name="TenTaiKhoan" type="text" autocomplete="username" required maxlength="255" pattern="[A-Za-z0-9_-]+" placeholder="Nhập tên tài khoản"></span>
        </label>
        <label class="auth-field">
            <span>Mật khẩu</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▣</i><input name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required placeholder="Tối thiểu 8 ký tự"><button class="auth-password-toggle" type="button" aria-label="Hiện mật khẩu">◉</button></span>
        </label>
        <label class="auth-field">
            <span>Họ và tên sinh viên</span>
            <span class="auth-input-wrap"><i aria-hidden="true">♙</i><input name="HoTen" type="text" autocomplete="name" required maxlength="255" placeholder="Nguyễn Văn A"></span>
        </label>
        <label class="auth-field">
            <span>Nhập lại mật khẩu</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▣</i><input name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Nhập lại mật khẩu"></span>
        </label>
        <label class="auth-field">
            <span>Mã số sinh viên (MSSV)</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▦</i><input name="MSSV" type="text" required maxlength="20" placeholder="VD: 21084201"></span>
        </label>
        <label class="auth-field">
            <span>Email</span>
            <span class="auth-input-wrap"><i aria-hidden="true">✉</i><input name="Email" type="email" autocomplete="email" required maxlength="255" placeholder="sinhvien@edu.vn"></span>
        </label>
        <label class="auth-field">
            <span>Số điện thoại / Zalo</span>
            <span class="auth-input-wrap"><i aria-hidden="true">⌕</i><input name="SDT" type="tel" autocomplete="tel" maxlength="30" placeholder="0912 345 678"></span>
        </label>
        <label class="auth-field">
            <span>Ngày sinh</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▦</i><input name="NgaySinh" type="date" autocomplete="bday" required max="{{ now()->subDay()->toDateString() }}"></span>
        </label>
        <label class="auth-field">
            <span>Khoa / Viện đào tạo</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▤</i><input name="Khoa" type="text" maxlength="255" placeholder="Khoa hoặc viện"></span>
        </label>
        <label class="auth-field">
            <span>Ngành đào tạo</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▤</i><input name="Nganh" type="text" maxlength="255" placeholder="Ngành học"></span>
        </label>
        <label class="auth-field">
            <span>Khóa học</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▦</i><input name="KhoaHoc" type="text" maxlength="50" placeholder="Ví dụ: K2022"></span>
        </label>
        <div class="auth-role-note"><span>●</span> Tài khoản mới có vai trò Sinh viên. Chức vụ quản lý CLB được phân công theo tư cách thành viên CLB.</div>
        <label class="auth-terms"><input type="checkbox" name="terms" required> Tôi đồng ý với Quy chế hoạt động và cam kết thông tin sinh viên chính xác.</label>
        <button class="auth-submit" type="submit"><span data-submit-label>Tạo tài khoản ngay</span><span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Đã có tài khoản UniClub? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
@endsection
