<?php

namespace Tests\Feature;

use App\Enums\CauLacBoTrangThai;
use App\Enums\DonUngTrangThai;
use App\Enums\DotTuyenTrangThai;
use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Enums\TrangThaiDangKy;
use App\Models\CauLacBo;
use App\Models\DangKy;
use App\Models\DonUngTuyen;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Models\SuKien;
use App\Models\ThanhVienCLB;
use App\Models\ViTriTuyen;
use App\Services\CauLacBoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_current_user_activity_and_only_open_recruitments(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $managedClub = app(CauLacBoService::class)->create([
            'TenCLB' => 'CLB do người dùng tạo',
            'MoTa' => 'Thông tin quản lý',
            'TrangThai' => CauLacBoTrangThai::DANG_HOAT_DONG,
        ]);
        $joinedClub = $this->createClub('CLB người dùng tham gia');
        $sharedRoleClub = $this->createClub('CLB có nhiều chức vụ');
        $pendingClub = $this->createClub('CLB đang chờ duyệt');
        $this->createMembership($user, $joinedClub, 'THÀNH VIÊN', ThanhVienTrangThai::DANG_HOAT_DONG);
        $this->createMembership($user, $sharedRoleClub, 'THÀNH VIÊN', ThanhVienTrangThai::DANG_HOAT_DONG);
        $this->createMembership($user, $sharedRoleClub, 'PHÓ CHỦ NHIỆM', ThanhVienTrangThai::DANG_HOAT_DONG);
        $this->createMembership($user, $pendingClub, 'THÀNH VIÊN', ThanhVienTrangThai::CHO_PHE_DUYET);

        $attendedEvent = SuKien::query()->create([
            'TenSuKien' => 'Sự kiện đã check-in',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => now()->subDays(3),
            'ThoiGianDongDangKy' => now()->subDays(2),
            'ThoiGianBatDau' => now()->subDay(),
            'ThoiGianKetThuc' => now()->subDay()->addHour(),
            'MaCLB' => $managedClub->MaCLB,
        ]);
        $registeredEvent = SuKien::query()->create([
            'TenSuKien' => 'Sự kiện chưa check-in',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => now()->subDays(3),
            'ThoiGianDongDangKy' => now()->subDays(2),
            'ThoiGianBatDau' => now()->subDay(),
            'ThoiGianKetThuc' => now()->subDay()->addHour(),
            'MaCLB' => $joinedClub->MaCLB,
        ]);
        $this->createRegistration($user, $attendedEvent, TrangThaiDangKy::DA_CHECK_IN);
        $this->createRegistration($user, $registeredEvent, TrangThaiDangKy::DA_XAC_NHAN);

        $openRecruitment = $this->createRecruitment(
            $joinedClub,
            'Đợt tuyển đang mở',
            now()->subDay(),
            now()->addDays(4),
        );
        $position = ViTriTuyen::query()->create([
            'TenViTri' => 'Thành viên Ban Nội dung',
            'Mota' => 'Tham gia hoạt động của câu lạc bộ.',
            'SoLuong' => 4,
            'MaDotTuyen' => $openRecruitment->MaDotTuyen,
        ]);
        DonUngTuyen::query()->create([
            'MaViTri' => $position->MaViTri,
            'MaNguoiUngTuyen' => $user->MaNguoiDung,
            'TrangThai' => DonUngTrangThai::CHO_XY_LY,
            'NgayNopDon' => now(),
            'NgayCapNhat' => now(),
        ]);
        $this->createRecruitment($managedClub, 'Đợt tuyển chưa mở', now()->addDay(), now()->addDays(3));
        $this->createRecruitment($managedClub, 'Đợt tuyển đã đóng', now()->subDays(3), now()->subDay());

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Nguyễn Minh')
            ->assertSee('<strong>2 CLB</strong>', false)
            ->assertSee('<strong>1 CLB</strong>', false)
            ->assertSee('1 sự kiện')
            ->assertSee('CLB do người dùng tạo')
            ->assertSee('CLB người dùng tham gia')
            ->assertSee('CLB có nhiều chức vụ')
            ->assertDontSee('CLB đang chờ duyệt')
            ->assertSee('Đợt tuyển đang mở')
            ->assertSee('Hồ sơ hợp lệ')
            ->assertDontSee('Đợt tuyển chưa mở')
            ->assertDontSee('Đợt tuyển đã đóng')
            ->assertDontSee('CodeStorm');
    }

    public function test_dashboard_leaves_recruitment_cards_empty_when_there_are_no_open_campaigns(): void
    {
        $user = $this->createUser();
        $club = $this->createClub('CLB chưa tuyển');
        $this->createRecruitment($club, 'Đợt tuyển tương lai', now()->addDay(), now()->addDays(3));

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('0 CLB')
            ->assertSee('0 sự kiện')
            ->assertDontSee('home-recruitment-card', false)
            ->assertDontSee('Đợt tuyển tương lai');
    }

    public function test_guest_dashboard_displays_numeric_zero_for_attended_events(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<strong>0 sự kiện</strong>', false);
    }

    private function createUser(): NguoiDung
    {
        return NguoiDung::query()->create([
            'TenTaiKhoan' => 'dashboard-user',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '24001001',
            'HoTen' => 'Nguyễn Minh',
            'Email' => 'dashboard@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
    }

    private function createClub(string $name): CauLacBo
    {
        return CauLacBo::query()->create([
            'TenCLB' => $name,
            'TrangThai' => CauLacBoTrangThai::DANG_HOAT_DONG,
        ]);
    }

    private function createMembership(
        NguoiDung $user,
        CauLacBo $club,
        string $role,
        ThanhVienTrangThai $status,
    ): ThanhVienCLB {
        return ThanhVienCLB::query()->create([
            'MaNguoiDung' => $user->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => $role,
            'NgayThamGia' => now(),
            'TrangThai' => $status,
        ]);
    }

    private function createRegistration(
        NguoiDung $user,
        SuKien $event,
        TrangThaiDangKy $status,
    ): DangKy {
        return DangKy::query()->create([
            'MaNguoiDung' => $user->MaNguoiDung,
            'MaSuKien' => $event->MaSuKien,
            'TrangThai' => $status,
            'ThoiGianCheckIn' => now(),
        ]);
    }

    private function createRecruitment(
        CauLacBo $club,
        string $name,
        Carbon $startsAt,
        Carbon $endsAt,
    ): DotTuyenThanhVien {
        return DotTuyenThanhVien::query()->create([
            'TenDotTuyen' => $name,
            'NgayBatDau' => $startsAt,
            'NgayKetThuc' => $endsAt,
            'TrangThai' => DotTuyenTrangThai::DANG_TUYEN,
            'MaCLB' => $club->MaCLB,
        ]);
    }
}
