<?php

namespace Tests\Feature;

use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Models\CauLacBo;
use App\Models\NguoiDung;
use App\Models\SuKien;
use App\Models\ThanhVienCLB;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CauLacBoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected NguoiDung $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = NguoiDung::create([
            'TenTaiKhoan' => 'testclbadmin',
            'MatKhauHash' => Hash::make('password123'),
            'MSSV' => 'SVADMIN01',
            'HoTen' => 'CLB Admin Tester',
            'Email' => 'clbadmin@test.com',
            'NgaySinh' => '2000-01-01',
            'VaiTro' => NguoiDungVaiTro::ADMIN,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
        $this->actingAs($this->adminUser, 'web');
    }

    public function test_unauthenticated_guests_cannot_access_club_management(): void
    {
        Auth::logout();
        $this->get(route('caulacbo.index'))->assertRedirect(route('login'));
        $this->get('/caulacbo/1/thanh-vien')->assertRedirect(route('login'));
    }

    public function test_management_page_shows_club_summary_and_creates_a_club(): void
    {
        $this->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee('Quản lý Câu lạc bộ của bạn')
            ->assertSee('Tạo Câu lạc bộ Mới')
            ->assertSee('Bộ câu lạc bộ')
            ->assertDontSee('Quản lý Sự kiện')
            ->assertSee('Danh sách câu lạc bộ')
            ->assertSee('CLB ĐANG ĐIỀU HÀNH')
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('type="file" name="URLLogo"', false)
            ->assertSee('type="file" name="URLAnhNen"', false)
            ->assertSee('Chọn logo từ thiết bị')
            ->assertSee('Chọn ảnh nền từ thiết bị')
            ->assertDontSee('URL logo')
            ->assertDontSee('URL ảnh nền')
            ->assertSee('Cùng UniClub tạo nên những sự kiện đáng nhớ')
            ->assertSee('Quản lý câu lạc bộ')
            ->assertDontSee('Danh sách sự kiện</a>', false)
            ->assertDontSee('Hệ thống đang hoạt động')
            ->assertSee('Chưa có câu lạc bộ nào');

        $response = $this->post(route('caulacbo.store'), [
            'TenCLB' => 'Câu lạc bộ Tình nguyện',
            'MoTa' => 'Hoạt động cộng đồng',
            'TrangThai' => 'DANG_HOAT_DONG',
            'MaDanhMucCLB' => 3,
        ])->assertRedirect(route('caulacbo.index').'#club-1');

        $this->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee('Câu lạc bộ Tình nguyện')
            ->assertSee('id="club-1"', false)
            ->assertSee('Danh mục #3')
            ->assertSee('DANG HOAT DONG')
            ->assertSee('Hiển thị 1–1 trong 1 câu lạc bộ')
            ->assertSee('href="'.route('caulacbo.members', 1).'"', false)
            ->assertSee('>Quản lý câu lạc bộ</a>', false)
            ->assertDontSee('＋ Sự kiện')
            ->assertDontSee('>Danh sách</a>', false);
    }

    public function test_joined_clubs_tab_uses_the_authenticated_users_memberships_and_supports_search(): void
    {
        $user = $this->createUser('joined-user', 'joined@example.test', '24001001');
        $otherUser = $this->createUser('other-user', 'other@example.test', '24001002');
        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB dữ liệu thật',
            'Email' => 'club@example.test',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $otherClub = CauLacBo::query()->create([
            'TenCLB' => 'CLB của người dùng khác',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $managedClub = CauLacBo::query()->create([
            'TenCLB' => 'CLB người dùng quản lý',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $user->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Thành viên Ban R&D',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $otherUser->MaNguoiDung,
            'MaCLB' => $otherClub->MaCLB,
            'ChucVu' => 'Cộng tác viên Kỹ thuật',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $user->MaNguoiDung,
            'MaCLB' => $managedClub->MaCLB,
            'ChucVu' => 'CHU_NHIEM',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        $this->actingAs($user)->get(route('caulacbo.index', ['tab' => 'joined']))
            ->assertOk()
            ->assertSee('Câu lạc bộ đang quản lý')
            ->assertSee('Câu lạc bộ đã tham gia')
            ->assertSee('Câu lạc bộ bạn đang tham gia')
            ->assertSee('CLB dữ liệu thật')
            ->assertSee('Thành viên Ban R&amp;D', false)
            ->assertSee('1 <span>câu lạc bộ đang tham gia</span>', false)
            ->assertSee('Đang hoạt động: 1')
            ->assertSee('CỘNG TÁC VIÊN: 0', false)
            ->assertDontSee('CLB của người dùng khác')
            ->assertDontSee('CLB người dùng quản lý')
            ->assertDontSee('giao diện minh họa')
            ->assertDontSee('title="Chưa kết nối dữ liệu"', false);

        $this->actingAs($user)->get(route('caulacbo.index', ['tab' => 'joined', 'q' => 'thật']))
            ->assertOk()
            ->assertSee('CLB dữ liệu thật')
            ->assertDontSee('CLB của người dùng khác');
    }

    public function test_joined_clubs_tab_shows_zero_when_the_user_has_no_memberships(): void
    {
        $user = $this->createUser('empty-user', 'empty@example.test', '24001003');

        $this->actingAs($user)
            ->get(route('caulacbo.index', ['tab' => 'joined']))
            ->assertOk()
            ->assertSee('0 <span>câu lạc bộ đang tham gia</span>', false)
            ->assertSee('Đang hoạt động: 0')
            ->assertSee('Khác: 0')
            ->assertSee('Chưa có câu lạc bộ nào')
            ->assertSee('Hiển thị 0–0 trong 0 câu lạc bộ')
            ->assertDontSee('Câu lạc bộ Vệ tinh nhân tạo');
    }

    public function test_club_manager_tabs_show_real_member_and_event_counts_and_members(): void
    {
        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB Quản lý',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $member = $this->createUser('club-member', 'member@example.test', '24001004');
        $pendingMember = $this->createUser('pending-member', 'pending@example.test', '24001005');
        $departedMember = $this->createUser('departed-member', 'departed@example.test', '24001006');

        foreach ([
            [$member, 'Trưởng ban Truyền thông', ThanhVienTrangThai::DANG_HOAT_DONG],
            [$pendingMember, 'Thành viên', ThanhVienTrangThai::CHO_PHE_DUYET],
            [$departedMember, 'Cộng tác viên', ThanhVienTrangThai::DA_ROI_CLB],
        ] as [$user, $role, $status]) {
            ThanhVienCLB::query()->create([
                'MaNguoiDung' => $user->MaNguoiDung,
                'MaCLB' => $club->MaCLB,
                'ChucVu' => $role,
                'NgayThamGia' => now(),
                'TrangThai' => $status,
            ]);
        }

        SuKien::query()->create([
            'TenSuKien' => 'Sự kiện CLB Quản lý',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => now()->addDay(),
            'ThoiGianDongDangKy' => now()->addDays(2),
            'ThoiGianBatDau' => now()->addDays(3),
            'ThoiGianKetThuc' => now()->addDays(3)->addHour(),
            'MaCLB' => $club->MaCLB,
        ]);

        $this->get(route('sukien.index', ['ma_clb' => $club->MaCLB]))
            ->assertOk()
            ->assertSee('Thành viên')
            ->assertSee('<b>2</b>', false)
            ->assertSee('Sự kiện')
            ->assertSee('Mở đợt tuyển')
            ->assertSee(route('caulacbo.recruitments.index', $club->MaCLB), false)
            ->assertSee(route('caulacbo.members', $club->MaCLB), false)
            ->assertSee('Sự kiện CLB Quản lý');

        $this->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('Thành viên &amp; Điểm danh CLB', false)
            ->assertSee('2 <span>thành viên đang quản lý</span>', false)
            ->assertSee('Đang hoạt động: 1')
            ->assertSee('1 thành viên chờ phê duyệt')
            ->assertSee('Thành viên thử nghiệm')
            ->assertSee('Trưởng ban Truyền thông')
            ->assertSee('pending@example.test')
            ->assertDontSee('departed@example.test')
            ->assertSee('Chưa có dữ liệu');

        $this->get(route('caulacbo.members', ['caulacbo' => $club->MaCLB, 'status' => ThanhVienTrangThai::CHO_PHE_DUYET->value]))
            ->assertOk()
            ->assertSee('pending@example.test')
            ->assertDontSee('member@example.test');

        $this->get(route('caulacbo.members', ['caulacbo' => $club->MaCLB, 'q' => 'pending@example.test']))
            ->assertOk()
            ->assertSee('pending@example.test')
            ->assertDontSee('member@example.test');
    }

    public function test_club_members_page_defaults_to_zero_when_no_members_exist(): void
    {
        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB chưa có thành viên',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);

        $this->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('<b>0</b>', false)
            ->assertSee('0 <span>thành viên đang quản lý</span>', false)
            ->assertSee('Chưa có thành viên nào')
            ->assertSee('Hiển thị 0–0 trong 0 thành viên');
    }

    private function createUser(string $username, string $email, string $studentNumber): NguoiDung
    {
        return NguoiDung::query()->create([
            'TenTaiKhoan' => $username,
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => $studentNumber,
            'HoTen' => 'Thành viên thử nghiệm',
            'Email' => $email,
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
    }

    public function test_club_logo_and_cover_can_be_uploaded_and_replaced(): void
    {
        Storage::fake('public');

        $this->post(route('caulacbo.store'), [
            'TenCLB' => 'CLB có ảnh',
            'URLLogo' => UploadedFile::fake()->create('logo.png', 100, 'image/png'),
            'URLAnhNen' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('caulacbo.index').'#club-1');

        $club = CauLacBo::query()->findOrFail(1);
        $firstLogoPath = ltrim(substr(parse_url($club->URLLogo, PHP_URL_PATH), strlen('/storage/')), '/');
        $firstCoverPath = ltrim(substr(parse_url($club->URLAnhNen, PHP_URL_PATH), strlen('/storage/')), '/');
        Storage::disk('public')->assertExists($firstLogoPath);
        Storage::disk('public')->assertExists($firstCoverPath);

        $this->put(route('caulacbo.update', $club->MaCLB), [
            'TenCLB' => $club->TenCLB,
            'URLLogo' => UploadedFile::fake()->create('replacement.png', 100, 'image/png'),
        ])->assertRedirect(route('caulacbo.index'));

        $club->refresh();
        $secondLogoPath = ltrim(substr(parse_url($club->URLLogo, PHP_URL_PATH), strlen('/storage/')), '/');
        $coverPath = ltrim(substr(parse_url($club->URLAnhNen, PHP_URL_PATH), strlen('/storage/')), '/');

        $this->assertNotSame($firstLogoPath, $secondLogoPath);
        Storage::disk('public')->assertExists($secondLogoPath);
        Storage::disk('public')->assertMissing($firstLogoPath);
        Storage::disk('public')->assertExists($coverPath);
        $this->assertSame($firstCoverPath, $coverPath);
    }

    public function test_club_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $this->from(route('caulacbo.index'))->post(route('caulacbo.store'), [
            'TenCLB' => 'CLB ảnh lỗi',
            'URLLogo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('caulacbo.index'))
            ->assertSessionHasErrors('URLLogo');

        $this->assertDatabaseMissing('cau_lac_bos', ['TenCLB' => 'CLB ảnh lỗi']);
        Storage::disk('public')->assertDirectoryEmpty('clubs/logos');
    }

    public function test_club_page_search_filter_update_and_delete_work(): void
    {
        $club = CauLacBo::create([
            'TenCLB' => 'Câu lạc bộ Công nghệ',
            'TrangThai' => 'DANG_HOAT_DONG',
            'MaDanhMucCLB' => 5,
        ]);
        $event = SuKien::create([
            'TenSuKien' => 'Sự kiện của CLB Công nghệ',
            'SoLuongToiDa' => 35,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 11:00:00',
            'MaCLB' => $club->MaCLB,
        ]);
        CauLacBo::create([
            'TenCLB' => 'Câu lạc bộ Âm nhạc',
            'TrangThai' => 'TAM_DUNG',
            'MaDanhMucCLB' => 8,
        ]);

        $this->get(route('caulacbo.index', ['q' => 'Công nghệ']))
            ->assertOk()
            ->assertSee('Câu lạc bộ Công nghệ')
            ->assertDontSee('Câu lạc bộ Âm nhạc');

        $this->get(route('caulacbo.index', ['filter' => 'active']))
            ->assertOk()
            ->assertSee('Câu lạc bộ Công nghệ')
            ->assertDontSee('Câu lạc bộ Âm nhạc');

        $this->get(route('caulacbo.index', ['category' => 8]))
            ->assertOk()
            ->assertSee('Câu lạc bộ Âm nhạc')
            ->assertDontSee('Câu lạc bộ Công nghệ');

        $this->put(route('caulacbo.update', $club->MaCLB), [
            'TenCLB' => 'Câu lạc bộ Công nghệ mới',
            'TrangThai' => 'TAM_DUNG',
        ])->assertRedirect(route('caulacbo.index'));

        $this->assertDatabaseHas('cau_lac_bos', [
            'MaCLB' => $club->MaCLB,
            'TenCLB' => 'Câu lạc bộ Công nghệ mới',
        ]);

        $this->delete(route('caulacbo.destroy', $club->MaCLB))
            ->assertRedirect(route('caulacbo.index'));

        $this->assertDatabaseMissing('cau_lac_bos', ['MaCLB' => $club->MaCLB]);
        $this->assertDatabaseHas('su_kiens', [
            'MaSuKien' => $event->MaSuKien,
            'MaCLB' => null,
        ]);
    }

    public function test_club_card_shows_real_related_events_and_event_list_can_be_filtered_by_club(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 09:30:00'));

        $club = CauLacBo::create([
            'TenCLB' => 'Câu lạc bộ Khoa học',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $otherClub = CauLacBo::create([
            'TenCLB' => 'Câu lạc bộ Ngoại ngữ',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $event = SuKien::create([
            'TenSuKien' => 'Workshop nghiên cứu',
            'SoLuongToiDa' => 35,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 11:00:00',
            'MaCLB' => $club->MaCLB,
        ]);
        SuKien::create([
            'TenSuKien' => 'Buổi sinh hoạt ngoại ngữ',
            'SoLuongToiDa' => 25,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-07 09:00:00',
            'ThoiGianKetThuc' => '2026-11-07 11:00:00',
            'MaCLB' => $otherClub->MaCLB,
        ]);

        $this->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee('Workshop nghiên cứu')
            ->assertSee('1 TỔNG CỘNG')
            ->assertSee(route('caulacbo.members', $club->MaCLB), false);

        $this->get(route('sukien.index', ['ma_clb' => $club->MaCLB]))
            ->assertOk()
            ->assertSee('Danh sách sự kiện thuộc CLB #'.$club->MaCLB)
            ->assertSee('<strong>1 <span>sự kiện đang quản lý</span></strong>', false)
            ->assertSee('Tổng 1 sự kiện trong hệ thống')
            ->assertSee('Workshop nghiên cứu')
            ->assertDontSee('Buổi sinh hoạt ngoại ngữ')
            ->assertSee('ma_clb='.$club->MaCLB.'&amp;trang_thai=SAP_DIEN_RA', false)
            ->assertSee(route('sukien.create', ['ma_clb' => $club->MaCLB]), false)
            ->assertSee('data-select-all', false)
            ->assertSee('Chỉnh sửa')
            ->assertSee('Xóa');
    }

    public function test_events_can_be_created_updated_and_deleted_from_a_clubs_event_list(): void
    {
        $club = CauLacBo::create([
            'TenCLB' => 'Câu lạc bộ Thiết kế',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        $listUrl = route('sukien.index', ['ma_clb' => $club->MaCLB]);

        $this->post(route('sukien.store'), [
            'TenSuKien' => 'Workshop Thiết kế',
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-09 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-09 18:00:00',
            'ThoiGianBatDau' => '2026-11-10 09:00:00',
            'ThoiGianKetThuc' => '2026-11-10 11:00:00',
            'MaCLB' => $club->MaCLB,
        ])->assertRedirect($listUrl);

        $event = SuKien::query()->where('TenSuKien', 'Workshop Thiết kế')->firstOrFail();
        $this->assertDatabaseHas('su_kiens', [
            'MaSuKien' => $event->MaSuKien,
            'MaCLB' => $club->MaCLB,
        ]);

        $this->put(route('sukien.update', $event->MaSuKien), [
            'TenSuKien' => 'Workshop Thiết kế cập nhật',
            'SoLuongToiDa' => 45,
            'ThoiGianMoDangKy' => '2026-11-09 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-09 18:00:00',
            'ThoiGianBatDau' => '2026-11-10 09:00:00',
            'ThoiGianKetThuc' => '2026-11-10 11:00:00',
            'MaCLB' => $club->MaCLB,
        ])->assertRedirect($listUrl);

        $this->assertDatabaseHas('su_kiens', [
            'MaSuKien' => $event->MaSuKien,
            'TenSuKien' => 'Workshop Thiết kế cập nhật',
        ]);

        $this->delete(route('sukien.destroy', $event->MaSuKien))
            ->assertRedirect($listUrl);
        $this->assertDatabaseMissing('su_kiens', ['MaSuKien' => $event->MaSuKien]);
    }

    public function test_user_only_sees_and_manages_their_own_club_and_cannot_manage_other_users_clubs(): void
    {
        $userA = $this->createUser('user-a', 'usera@example.test', '24001010');
        $userB = $this->createUser('user-b', 'userb@example.test', '24001020');

        $clubA = CauLacBo::query()->create([
            'TenCLB' => 'CLB của Người Dùng A',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $userA->MaNguoiDung,
            'MaCLB' => $clubA->MaCLB,
            'ChucVu' => 'CHU_NHIEM',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        $clubB = CauLacBo::query()->create([
            'TenCLB' => 'CLB của Người Dùng B',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $userB->MaNguoiDung,
            'MaCLB' => $clubB->MaCLB,
            'ChucVu' => 'CHU_NHIEM',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        // User B visits /caulacbo: only sees CLB B, does NOT see CLB A
        $this->actingAs($userB)
            ->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee('CLB của Người Dùng B')
            ->assertDontSee('CLB của Người Dùng A');

        // User B cannot access Club A members
        $this->actingAs($userB)
            ->get(route('caulacbo.members', $clubA->MaCLB))
            ->assertStatus(403);

        // User B cannot update Club A
        $this->actingAs($userB)
            ->put(route('caulacbo.update', $clubA->MaCLB), [
                'TenCLB' => 'CLB A bị User B sửa',
                'TrangThai' => 'DANG_HOAT_DONG',
            ])
            ->assertStatus(403);

        // User B cannot delete Club A
        $this->actingAs($userB)
            ->delete(route('caulacbo.destroy', $clubA->MaCLB))
            ->assertStatus(403);

        // User B cannot manage events of Club A
        $this->actingAs($userB)
            ->get(route('sukien.index', ['ma_clb' => $clubA->MaCLB]))
            ->assertStatus(403);

        $this->actingAs($userB)
            ->post(route('sukien.store'), [
                'TenSuKien' => 'Sự kiện trái phép',
                'SoLuongToiDa' => 20,
                'ThoiGianMoDangKy' => '2026-11-15 08:00:00',
                'ThoiGianDongDangKy' => '2026-11-15 18:00:00',
                'ThoiGianBatDau' => '2026-11-16 08:00:00',
                'ThoiGianKetThuc' => '2026-11-16 12:00:00',
                'MaCLB' => $clubA->MaCLB,
            ])
            ->assertStatus(403);
    }

    public function test_club_owner_can_grant_management_role_to_member_and_granted_member_can_manage_club(): void
    {
        $owner = $this->createUser('owner-a', 'owner@example.test', '24001030');
        $member = $this->createUser('member-b', 'member@example.test', '24001040');

        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB Công Nghệ AI',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $owner->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Chủ nhiệm',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);
        $memberRecord = ThanhVienCLB::query()->create([
            'MaNguoiDung' => $member->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Thành viên',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        // Initially, member cannot manage the club
        $this->actingAs($member)
            ->get(route('caulacbo.index'))
            ->assertOk()
            ->assertDontSee('CLB Công Nghệ AI');

        $this->actingAs($member)
            ->get(route('caulacbo.members', $club->MaCLB))
            ->assertStatus(403);

        // Owner grants management permission to member (promoted to Phó chủ nhiệm)
        $this->actingAs($owner)
            ->patch(route('caulacbo.members.update', ['caulacbo' => $club->MaCLB, 'thanhVien' => $memberRecord->MaThanhVien]), [
                'ChucVu' => 'Phó chủ nhiệm',
                'TrangThai' => 'DANG_HOAT_DONG',
            ])
            ->assertRedirect(route('caulacbo.members', $club->MaCLB))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('thanh_vien_c_l_b_s', [
            'MaThanhVien' => $memberRecord->MaThanhVien,
            'ChucVu' => 'Phó chủ nhiệm',
        ]);

        // Now member CAN see and manage the club
        $this->actingAs($member)
            ->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee('CLB Công Nghệ AI');

        $this->actingAs($member)
            ->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('Thành viên &amp; Điểm danh CLB', false)
            ->assertSee('Phó chủ nhiệm')
            ->assertSee('Quyền quản lý');

        $this->actingAs($member)
            ->get(route('sukien.index', ['ma_clb' => $club->MaCLB]))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertOk();
    }

    public function test_club_management_ui_vietnamese_labels_navigation_and_clean_button_icons(): void
    {
        $user = $this->createUser('quantri', 'quantri@example.test', '20268888');

        $club = CauLacBo::create([
            'TenCLB' => 'CLB Robot & AI',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);

        ThanhVienCLB::create([
            'MaNguoiDung' => $user->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'CHU_NHIEM',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        // 1. Club index links to caulacbo.members
        $this->actingAs($user)
            ->get(route('caulacbo.index'))
            ->assertOk()
            ->assertSee(route('caulacbo.members', $club->MaCLB), false)
            ->assertSee('>Quản lý câu lạc bộ</a>', false);

        // 2. Members page has standardized Vietnamese and clean button/badge without gear/shield icons
        $this->actingAs($user)
            ->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('Chủ nhiệm')
            ->assertSee('Đang hoạt động')
            ->assertSee('Cấp quyền')
            ->assertDontSee('⚙️ Cấp quyền')
            ->assertSee('Quyền quản lý')
            ->assertDontSee('🛡️ Quyền quản lý');

        // 3. SuKien index has standardized Vietnamese statuses in sidebar
        $this->actingAs($user)
            ->get(route('sukien.index', ['ma_clb' => $club->MaCLB]))
            ->assertOk()
            ->assertSee('Chưa mở đăng ký')
            ->assertSee('Đang mở đăng ký')
            ->assertSee('Đã đóng đăng ký')
            ->assertSee('Sắp diễn ra');

        // 4. DotTuyen index has standardized Vietnamese statuses in sidebar
        $this->actingAs($user)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertOk()
            ->assertSee('Đang mở tuyển')
            ->assertSee('Chưa mở tuyển');
    }

    public function test_all_enums_have_vietnamese_labels(): void
    {
        $this->assertSame('Đang hoạt động', \App\Enums\CauLacBoTrangThai::DANG_HOAT_DONG->label());
        $this->assertSame('Chờ xử lý', \App\Enums\DonUngTrangThai::CHO_XY_LY->label());
        $this->assertSame('Đang mở tuyển', \App\Enums\DotTuyenTrangThai::DANG_TUYEN->label());
        $this->assertSame('Đang hoạt động', \App\Enums\NguoiDungTrangThai::HOAT_DONG->label());
        $this->assertSame('Người dùng', \App\Enums\NguoiDungVaiTro::USER->label());
        $this->assertSame('Quản trị viên', \App\Enums\NguoiDungVaiTro::ADMIN->label());
        $this->assertSame('Nội bộ', \App\Enums\SuKienLoai::NOI_BO->label());
        $this->assertSame('Sinh hoạt định kỳ', \App\Enums\SuKienPhanLoaiNoiBo::SINH_HOAT_DINH_KY->label());
        $this->assertSame('Đang mở đăng ký', \App\Enums\SuKienTrangThai::DANG_MO_DANG_KY->label());
        $this->assertSame('Đang hoạt động', \App\Enums\ThanhVienTrangThai::DANG_HOAT_DONG->label());
        $this->assertSame('Chờ duyệt rời', \App\Enums\ThanhVienTrangThai::CHO_DUYET_ROI->label());
        $this->assertSame('Đã điểm danh', \App\Enums\TrangThaiDangKy::DA_CHECK_IN->label());
    }

    public function test_owner_can_delete_member_and_member_can_request_leave_and_be_approved(): void
    {
        $owner = $this->createUser('owner_del', 'owner_del@example.test', '20269901');
        $member = $this->createUser('member_del', 'member_del@example.test', '20269902');
        $manager = $this->createUser('manager_sub', 'manager_sub@example.test', '20269903');

        $club = CauLacBo::create([
            'TenCLB' => 'CLB Khởi Nghiệp',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);

        $ownerMembership = ThanhVienCLB::create([
            'MaNguoiDung' => $owner->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Chủ nhiệm',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        $managerMembership = ThanhVienCLB::create([
            'MaNguoiDung' => $manager->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Phó chủ nhiệm',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        $memberMembership = ThanhVienCLB::create([
            'MaNguoiDung' => $member->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'Thành viên',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        // 1. Owner views member list: sees "Chi tiết" for all, "Xóa" for other members, and "Rời CLB" for themselves
        $this->actingAs($owner)
            ->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('Chi tiết')
            ->assertSee('Xóa')
            ->assertSee('Rời CLB');

        // 2. Member (or Phó chủ nhiệm) requests to leave club -> status becomes CHO_DUYET_ROI
        $this->actingAs($manager)
            ->post(route('caulacbo.members.requestLeave', [$club->MaCLB, $managerMembership->MaThanhVien]))
            ->assertRedirect(route('caulacbo.members', $club->MaCLB))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('thanh_vien_c_l_b_s', [
            'MaThanhVien' => $managerMembership->MaThanhVien,
            'TrangThai' => ThanhVienTrangThai::CHO_DUYET_ROI->value,
        ]);

        // 3. Owner sees "Chờ duyệt rời" and buttons "Duyệt rời" / "Giữ lại"
        $this->actingAs($owner)
            ->get(route('caulacbo.members', $club->MaCLB))
            ->assertOk()
            ->assertSee('Chờ duyệt rời')
            ->assertSee('Duyệt rời')
            ->assertSee('Giữ lại');

        // 4. Owner approves leave -> manager is removed from club
        $this->actingAs($owner)
            ->post(route('caulacbo.members.approveLeave', [$club->MaCLB, $managerMembership->MaThanhVien]))
            ->assertRedirect(route('caulacbo.members', $club->MaCLB))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('thanh_vien_c_l_b_s', [
            'MaThanhVien' => $managerMembership->MaThanhVien,
        ]);

        // 5. Owner deletes regular member directly
        $this->actingAs($owner)
            ->delete(route('caulacbo.members.destroy', [$club->MaCLB, $memberMembership->MaThanhVien]))
            ->assertRedirect(route('caulacbo.members', $club->MaCLB))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('thanh_vien_c_l_b_s', [
            'MaThanhVien' => $memberMembership->MaThanhVien,
        ]);
    }
}
