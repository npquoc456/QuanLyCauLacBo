@extends('layouts.auth')

@section('title', 'Đăng nhập')
@section('auth-label', 'ĐĂNG NHẬP')

@section('auth-content')
    <div class="auth-form-heading">
        <span class="auth-overline">CHÀO MỪNG BẠN TRỞ LẠI</span>
        <h2>Chào mừng trở lại!</h2>
        <p>Đăng nhập để theo dõi sự kiện, tự quét QR điểm danh và kiểm tra Điểm Rèn Luyện (ĐRL).</p>
    </div>

    @if(request()->boolean('registered'))
        <div class="auth-alert auth-alert-success" role="status">Đăng ký thành công. Hãy đăng nhập bằng tài khoản vừa tạo.</div>
    @endif
    <div class="auth-alert auth-alert-error" data-auth-message role="alert" hidden></div>

    <button class="auth-google-button" type="button" disabled title="Đăng nhập Google chưa được cấu hình">
        <span aria-hidden="true">G</span> Đăng nhập nhanh với Email Sinh viên trường (@edu.vn)
    </button>
    <div class="auth-divider"><span>HOẶC ĐĂNG NHẬP BẰNG TÀI KHOẢN</span></div>

    <form class="auth-form" data-auth-form data-endpoint="{{ url('/api/auth/login') }}" data-form-type="login">
        <label class="auth-field">
            <span>Email, tên tài khoản hoặc MSSV</span>
            <span class="auth-input-wrap"><i aria-hidden="true">♙</i><input name="identifier" type="text" autocomplete="username" required maxlength="255" value="{{ request('identifier') }}" placeholder="Nhập email, tên tài khoản hoặc MSSV"></span>
        </label>
        <label class="auth-field">
            <span>Mật khẩu</span>
            <span class="auth-input-wrap"><i aria-hidden="true">▣</i><input name="password" type="password" autocomplete="current-password" required placeholder="Nhập mật khẩu"><button class="auth-password-toggle" type="button" aria-label="Hiện mật khẩu">◉</button></span>
        </label>
        <div class="auth-form-options">
            <label><input type="checkbox" name="remember" value="1"> Ghi nhớ phiên đăng nhập</label>
        </div>
        <button class="auth-submit" type="submit"><span data-submit-label>Đăng nhập hệ thống</span><span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Chưa có tài khoản UniClub? <a href="{{ route('register') }}">Đăng ký tài khoản mới</a></p>
@endsection
