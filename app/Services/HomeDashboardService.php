<?php

namespace App\Services;

use App\Enums\DonUngTrangThai;
use App\Enums\DotTuyenTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Enums\TrangThaiDangKy;
use App\Models\CauLacBo;
use App\Models\DangKy;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Models\SuKien;
use App\Models\ThanhVienCLB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeDashboardService
{
    /**
     * @return array{
     *     profile: ?NguoiDung,
     *     stats: array{managed_clubs: int, joined_clubs: int, attended_events: int, training_points: int},
     *     member_role: string,
     *     managed_clubs: Collection<int, ThanhVienCLB>,
     *     joined_clubs: Collection<int, ThanhVienCLB>,
     *     recruitments: EloquentCollection<int, DotTuyenThanhVien>,
     *     platform_stats: array{clubs: int, users: int, events: int}
     * }
     */
    public function getDashboardData(?NguoiDung $user): array
    {
        $memberships = $user === null
            ? collect()
            : ThanhVienCLB::query()
                ->with('cauLacBo')
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->where('TrangThai', ThanhVienTrangThai::DANG_HOAT_DONG->value)
                ->get()
                ->filter(fn (ThanhVienCLB $membership): bool => $membership->cauLacBo !== null)
                ->groupBy('MaCLB')
                ->map(fn (Collection $clubMemberships): ThanhVienCLB => $clubMemberships->first(
                    fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu),
                ) ?? $clubMemberships->first())
                ->values();

        $managedClubs = $memberships
            ->filter(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu))
            ->values();
        $joinedClubs = $memberships
            ->reject(fn (ThanhVienCLB $membership): bool => $this->isManagementRole($membership->ChucVu))
            ->values();

        $attendedEvents = $user === null
            ? 0
            : DangKy::query()
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->where('TrangThai', TrangThaiDangKy::DA_CHECK_IN->value)
                ->distinct('MaSuKien')
                ->count('MaSuKien');

        $recruitments = $this->getOpenRecruitments();

        return [
            'profile' => $user,
            'stats' => [
                'managed_clubs' => $managedClubs->count(),
                'joined_clubs' => $joinedClubs->count(),
                'attended_events' => $attendedEvents,
                'training_points' => 0,
            ],
            'member_role' => $this->memberRoleLabel($user, $managedClubs->isNotEmpty()),
            'managed_clubs' => $managedClubs,
            'joined_clubs' => $joinedClubs,
            'recruitments' => $recruitments,
            'platform_stats' => [
                'clubs' => CauLacBo::query()->count(),
                'users' => NguoiDung::query()->count(),
                'events' => SuKien::query()->count(),
            ],
        ];
    }

    /**
     * @return EloquentCollection<int, DotTuyenThanhVien>
     */
    private function getOpenRecruitments(): EloquentCollection
    {
        $now = now();
        $recruitments = DotTuyenThanhVien::query()
            ->with('cauLacBo')
            ->where('LaBanNhap', false)
            ->where('NgayBatDau', '<=', $now)
            ->where('NgayKetThuc', '>', $now)
            ->whereHas('cauLacBo')
            ->orderBy('NgayKetThuc')
            ->get();

        if ($recruitments->isEmpty()) {
            return $recruitments;
        }

        $campaignIds = $recruitments->pluck('MaDotTuyen');
        $applicationCounts = DB::table('don_ung_tuyens')
            ->join('vi_tri_tuyens', 'don_ung_tuyens.MaViTri', '=', 'vi_tri_tuyens.MaViTri')
            ->whereIn('vi_tri_tuyens.MaDotTuyen', $campaignIds)
            ->where(function ($query): void {
                $query->whereNull('don_ung_tuyens.TrangThai')
                    ->orWhereNotIn('don_ung_tuyens.TrangThai', [
                        DonUngTrangThai::DA_HUY->value,
                        DonUngTrangThai::TU_CHOI->value,
                    ]);
            })
            ->groupBy('vi_tri_tuyens.MaDotTuyen')
            ->selectRaw('vi_tri_tuyens.MaDotTuyen, COUNT(*) AS application_count')
            ->pluck('application_count', 'MaDotTuyen');
        $targetCounts = DB::table('vi_tri_tuyens')
            ->whereIn('MaDotTuyen', $campaignIds)
            ->groupBy('MaDotTuyen')
            ->selectRaw('MaDotTuyen, SUM(SoLuong) AS target_count')
            ->pluck('target_count', 'MaDotTuyen');

        foreach ($recruitments as $recruitment) {
            $recruitment->setAttribute('application_count', (int) ($applicationCounts[$recruitment->MaDotTuyen] ?? 0));
            $recruitment->setAttribute('target_count', (int) ($targetCounts[$recruitment->MaDotTuyen] ?? 0));
        }

        return $recruitments;
    }

    private function isManagementRole(?string $role): bool
    {
        if ($role === null || trim($role) === '') {
            return false;
        }

        $normalizedRole = preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii($role))) ?? '';

        return Str::contains($normalizedRole, [
            'CHU NHIEM',
            'CHU TICH',
            'TRUONG CLB',
            'PHO CLB',
            'TRUONG BAN',
            'PHO BAN',
            'DONG SANG LAP',
            'QUAN LY',
            'MANAGER',
            'PRESIDENT',
            'ADMIN',
        ]);
    }

    private function memberRoleLabel(?NguoiDung $user, bool $managesClubs): string
    {
        if ($user === null) {
            return 'Khách';
        }

        if ($user->VaiTro === NguoiDungVaiTro::ADMIN) {
            return 'Quản trị viên';
        }

        return $managesClubs ? 'Quản lý CLB & Sinh viên' : 'Sinh viên';
    }
}
