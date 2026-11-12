<?php

namespace App\Services;

use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Models\NguoiDung;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): NguoiDung
    {
        return NguoiDung::query()->create([
            'TenTaiKhoan' => $data['TenTaiKhoan'],
            'MatKhauHash' => Hash::make($data['password']),
            'MSSV' => $data['MSSV'],
            'HoTen' => $data['HoTen'],
            'SDT' => $data['SDT'] ?? null,
            'Email' => $data['Email'],
            'NgaySinh' => $data['NgaySinh'],
            'Khoa' => $data['Khoa'] ?? null,
            'Nganh' => $data['Nganh'] ?? null,
            'KhoaHoc' => $data['KhoaHoc'] ?? null,
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
    }

    public function login(string $identifier, string $password, bool $remember, ?string $ipAddress): NguoiDung
    {
        $throttleKey = 'auth-login:'.hash('sha256', mb_strtolower($identifier).'|'.($ipAddress ?? 'unknown'));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'identifier' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng chờ một phút rồi thử lại.',
            ]);
        }

        $user = NguoiDung::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('TenTaiKhoan', $identifier)
                    ->orWhere('Email', $identifier)
                    ->orWhere('MSSV', $identifier);
            })
            ->first();

        if (
            $user === null
            || $user->TrangThai !== NguoiDungTrangThai::HOAT_DONG
            || ! Hash::check($password, $user->MatKhauHash)
        ) {
            RateLimiter::hit($throttleKey, 60);
            throw new AuthenticationException('Thông tin đăng nhập không chính xác hoặc tài khoản đã bị khóa.');
        }

        RateLimiter::clear($throttleKey);
        Auth::guard('web')->login($user, $remember);

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
