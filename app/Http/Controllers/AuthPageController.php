<?php

namespace App\Http\Controllers;

use App\Enums\DotTuyenTrangThai;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CauLacBo;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Models\SuKien;
use App\Services\AuthService;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('home');
        }

        return view('auth.login', $this->getAuthPromoData());
    }

    public function register(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('home');
        }

        return view('auth.register', $this->getAuthPromoData());
    }

    public function settings(): View|RedirectResponse
    {
        if (! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        return view('auth.settings', ['user' => Auth::guard('web')->user()]);
    }

    public function updateSettings(ProfileUpdateRequest $request, ProfileService $profileService): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof NguoiDung, 403);

        $profileService->update($user, $request->validated());

        return redirect()->route('settings')->with('status', 'Thông tin cá nhân đã được cập nhật.');
    }

    public function logout(Request $request, AuthService $authService): RedirectResponse
    {
        $authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Bạn đã đăng xuất.');
    }

    /**
     * @return array{featuredEvent: ?SuKien, featuredRecruitment: ?DotTuyenThanhVien, platformStats: array{clubs: int, users: int, events: int}}
     */
    public function getAuthPromoData(): array
    {
        $now = now();

        // 1. Tìm sự kiện đang diễn ra hoặc sắp diễn ra
        $activeEvent = SuKien::query()
            ->with('cauLacBo')
            ->where('ThoiGianBatDau', '<=', $now)
            ->where('ThoiGianKetThuc', '>=', $now)
            ->orderBy('ThoiGianKetThuc', 'asc')
            ->first();

        if (! $activeEvent) {
            $activeEvent = SuKien::query()
                ->with('cauLacBo')
                ->where('ThoiGianBatDau', '>', $now)
                ->orderBy('ThoiGianBatDau', 'asc')
                ->first();
        }

        if (! $activeEvent) {
            $activeEvent = SuKien::query()
                ->with('cauLacBo')
                ->latest('ThoiGianBatDau')
                ->first();
        }

        // 2. Nếu không có sự kiện, tìm đợt tuyển thành viên đang mở tuyển (hoặc mới nhất)
        $activeRecruitment = null;
        if (! $activeEvent) {
            $activeRecruitment = DotTuyenThanhVien::query()
                ->with('cauLacBo')
                ->where('TrangThai', DotTuyenTrangThai::DANG_TUYEN)
                ->latest('NgayBatDau')
                ->first()
                ?? DotTuyenThanhVien::query()
                    ->with('cauLacBo')
                    ->where('LaBanNhap', false)
                    ->latest('NgayBatDau')
                    ->first();
        }

        return [
            'featuredEvent' => $activeEvent,
            'featuredRecruitment' => $activeRecruitment,
            'platformStats' => [
                'clubs' => CauLacBo::query()->count(),
                'users' => NguoiDung::query()->count(),
                'events' => SuKien::query()->count(),
            ],
        ];
    }
}
