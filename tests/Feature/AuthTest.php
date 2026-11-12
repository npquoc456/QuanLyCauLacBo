<?php

namespace Tests\Feature;

use App\Enums\DotTuyenTrangThai;
use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Models\CauLacBo;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Models\SuKien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_are_available_and_link_to_each_other(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Đăng nhập hệ thống')
            ->assertSee('api/auth/login');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Tạo tài khoản ngay')
            ->assertSee('api/auth/register');
    }

    public function test_registration_creates_a_user_then_login_authenticates_and_redirects_home(): void
    {
        $registration = $this->postJson('/api/auth/register', [
            'TenTaiKhoan' => 'minhnguyen',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'HoTen' => 'Nguyễn Minh',
            'MSSV' => '21084201',
            'Email' => 'minh@example.test',
            'SDT' => '0912345678',
            'NgaySinh' => '2004-05-18',
            'Khoa' => 'Khoa Công nghệ thông tin',
            'Nganh' => 'Khoa học máy tính',
            'KhoaHoc' => 'K2022',
            'VaiTro' => 'ADMIN',
        ]);

        $registration->assertCreated()
            ->assertJsonPath('redirect', route('login'))
            ->assertJsonPath('user.VaiTro', NguoiDungVaiTro::USER->value)
            ->assertJsonMissingPath('user.MatKhauHash');

        $user = NguoiDung::query()->where('TenTaiKhoan', 'minhnguyen')->firstOrFail();
        $this->assertSame('MaNguoiDung', $user->getKeyName());
        $this->assertNotSame('StrongPass123', $user->MatKhauHash);
        $this->assertTrue(Hash::check('StrongPass123', $user->MatKhauHash));
        $this->assertSame(NguoiDungTrangThai::HOAT_DONG, $user->TrangThai);

        $this->get(route('login', ['registered' => 1, 'identifier' => $user->TenTaiKhoan]))
            ->assertOk()
            ->assertSee('Đăng ký thành công')
            ->assertSee('value="minhnguyen"', false);

        $this->postJson('/api/auth/login', [
            'identifier' => 'minh@example.test',
            'password' => 'StrongPass123',
            'remember' => true,
        ])->assertOk()
            ->assertJsonPath('redirect', route('home'))
            ->assertJsonPath('user.TenTaiKhoan', 'minhnguyen');

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.Email', 'minh@example.test');
    }

    public function test_registration_validates_duplicates_and_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', [
            'TenTaiKhoan' => 'new-user',
            'password' => 'weak',
            'password_confirmation' => 'different',
            'HoTen' => 'Nguyễn Minh',
            'MSSV' => '21084201',
            'Email' => 'invalid',
            'NgaySinh' => '2030-01-01',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password', 'Email', 'NgaySinh']);

        $this->assertDatabaseCount('nguoi_dungs', 0);
    }

    public function test_inactive_accounts_cannot_log_in_and_logout_ends_the_session(): void
    {
        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'inactive',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084202',
            'HoTen' => 'Nguyễn Minh',
            'Email' => 'inactive@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::KHONG_HOAT_DONG,
        ]);

        $this->postJson('/api/auth/login', [
            'identifier' => $user->TenTaiKhoan,
            'password' => 'StrongPass123',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Thông tin đăng nhập không chính xác hoặc tài khoản đã bị khóa.');

        $activeUser = NguoiDung::query()->create([
            'TenTaiKhoan' => 'active',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084203',
            'HoTen' => 'Lê An',
            'Email' => 'active@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);

        $this->actingAs($activeUser)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Đã đăng xuất.');

        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_account_avatar_menu_contains_settings_and_logout(): void
    {
        $this->get(route('settings'))->assertRedirect(route('login'));

        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'profile-user',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084204',
            'HoTen' => 'Trần Minh',
            'Email' => 'profile@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-profile-menu-trigger', false)
            ->assertSee('data-profile-menu-panel hidden', false)
            ->assertSee('Cài đặt')
            ->assertSee('Đăng xuất')
            ->assertSee(route('settings'), false);

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk()
            ->assertSee('Cài đặt tài khoản')
            ->assertSee('profile@example.test')
            ->assertSee('name="URLAnhDaiDien"', false)
            ->assertSee('action="'.route('settings.update').'"', false)
            ->assertSee('name="KhoaHoc"', false)
            ->assertSee('name="current_password"', false)
            ->assertSee('name="new_password"', false)
            ->assertSee('name="new_password_confirmation"', false);
    }

    public function test_user_can_update_profile_and_replace_their_avatar(): void
    {
        Storage::fake('public');

        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'editable-profile',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084205',
            'HoTen' => 'Trần Minh',
            'Email' => 'editable@example.test',
            'NgaySinh' => '2004-05-18',
            'URLAnhDaiDien' => Storage::disk('public')->url('avatars/old-avatar.jpg'),
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old-avatar');

        $this->actingAs($user)
            ->put(route('settings.update'), [
                'TenTaiKhoan' => 'editable-profile',
                'MSSV' => '21084205',
                'HoTen' => 'Trần Minh Updated',
                'Email' => 'editable@example.test',
                'SDT' => '0912345678',
                'NgaySinh' => '2004-05-18',
                'Khoa' => 'Công nghệ thông tin',
                'Nganh' => 'Khoa học máy tính',
                'KhoaHoc' => 'K2022',
                'URLAnhDaiDien' => UploadedFile::fake()->createWithContent(
                    'avatar.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='),
                ),
                'MaNguoiDung' => 999,
                'VaiTro' => NguoiDungVaiTro::ADMIN->value,
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHas('status');

        $user->refresh();
        $avatarPath = Str::after(parse_url($user->URLAnhDaiDien, PHP_URL_PATH), '/storage/');

        $this->assertSame('Trần Minh Updated', $user->HoTen);
        $this->assertSame('Khoa học máy tính', $user->Nganh);
        $this->assertSame(NguoiDungVaiTro::USER, $user->VaiTro);
        $this->assertDatabaseMissing('nguoi_dungs', ['MaNguoiDung' => 999]);
        Storage::disk('public')->assertExists($avatarPath);
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($user->URLAnhDaiDien, false);
    }

    public function test_profile_update_rejects_duplicate_identity_fields_and_invalid_avatars(): void
    {
        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'profile-owner',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084206',
            'HoTen' => 'Chủ tài khoản',
            'Email' => 'owner@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
        NguoiDung::query()->create([
            'TenTaiKhoan' => 'other-profile',
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => '21084207',
            'HoTen' => 'Người khác',
            'Email' => 'other@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);

        $this->actingAs($user)
            ->from(route('settings'))
            ->put(route('settings.update'), [
                'TenTaiKhoan' => 'other-profile',
                'MSSV' => '21084206',
                'HoTen' => 'Chủ tài khoản',
                'Email' => 'owner@example.test',
                'NgaySinh' => '2004-05-18',
                'URLAnhDaiDien' => UploadedFile::fake()->create('avatar.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHasErrors(['TenTaiKhoan', 'URLAnhDaiDien']);

        $this->assertSame('profile-owner', $user->fresh()->TenTaiKhoan);
    }

    public function test_user_can_change_password_with_their_current_password(): void
    {
        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'password-owner',
            'MatKhauHash' => Hash::make('CurrentPass123'),
            'MSSV' => '21084208',
            'HoTen' => 'Chủ tài khoản',
            'Email' => 'password-owner@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
        $originalHash = $user->MatKhauHash;

        $this->actingAs($user)
            ->put(route('settings.update'), [
                'TenTaiKhoan' => $user->TenTaiKhoan,
                'MSSV' => $user->MSSV,
                'HoTen' => $user->HoTen,
                'Email' => $user->Email,
                'NgaySinh' => '2004-05-18',
                'current_password' => 'CurrentPass123',
                'new_password' => 'NewStrongPass456',
                'new_password_confirmation' => 'NewStrongPass456',
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertNotSame($originalHash, $user->MatKhauHash);
        $this->assertTrue(Hash::check('NewStrongPass456', $user->MatKhauHash));
        $this->assertAuthenticatedAs($user);

        $this->postJson('/api/auth/login', [
            'identifier' => $user->TenTaiKhoan,
            'password' => 'NewStrongPass456',
        ])->assertOk();
    }

    public function test_user_cannot_change_password_without_the_correct_current_password_or_confirmation(): void
    {
        $user = NguoiDung::query()->create([
            'TenTaiKhoan' => 'password-guard',
            'MatKhauHash' => Hash::make('CurrentPass123'),
            'MSSV' => '21084209',
            'HoTen' => 'Chủ tài khoản',
            'Email' => 'password-guard@example.test',
            'NgaySinh' => '2004-05-18',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
        $originalHash = $user->MatKhauHash;
        $profile = [
            'TenTaiKhoan' => $user->TenTaiKhoan,
            'MSSV' => $user->MSSV,
            'HoTen' => $user->HoTen,
            'Email' => $user->Email,
            'NgaySinh' => '2004-05-18',
        ];

        $this->actingAs($user)
            ->from(route('settings'))
            ->put(route('settings.update'), $profile + [
                'current_password' => 'incorrect',
                'new_password' => 'NewStrongPass456',
                'new_password_confirmation' => 'NewStrongPass456',
            ])
            ->assertRedirect(route('settings'))
            ->assertSessionHasErrors('current_password');

        $this->put(route('settings.update'), $profile + [
            'current_password' => 'CurrentPass123',
            'new_password' => 'NewStrongPass456',
            'new_password_confirmation' => 'does-not-match',
        ])->assertRedirect(route('settings'))
            ->assertSessionHasErrors('new_password');

        $this->assertSame($originalHash, $user->fresh()->MatKhauHash);
    }

    public function test_auth_pages_display_real_events_or_recruitments_and_real_platform_stats(): void
    {
        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB Trí tuệ nhân tạo',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);

        $event = SuKien::query()->create([
            'TenSuKien' => 'Workshop AI & Khởi nghiệp tương lai',
            'DiaDiem' => 'Hội trường E12',
            'SoLuongToiDa' => 100,
            'ThoiGianMoDangKy' => now()->subDay(),
            'ThoiGianDongDangKy' => now()->addDays(2),
            'ThoiGianBatDau' => now()->addDay(),
            'ThoiGianKetThuc' => now()->addDays(2),
            'MaCLB' => $club->MaCLB,
        ]);

        // When event exists, login/register shows the real event, its image media, and real stats (1 CLB, 0 or X accounts, 1 event)
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Workshop AI &amp; Khởi nghiệp tương lai', false)
            ->assertSee('auth-feature-media', false)
            ->assertSee('auth-feature-img', false)
            ->assertDontSee('auth-feature-icon', false)
            ->assertSee('Hội trường E12')
            ->assertSee('Câu lạc bộ')
            ->assertSee('Tài khoản')
            ->assertSee('Sự kiện')
            ->assertSee('1')
            ->assertSee('+5 ĐRL');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('auth-feature-media', false)
            ->assertSee('auth-feature-img', false)
            ->assertSee('Workshop AI &amp; Khởi nghiệp tương lai', false);

        // Delete event, now there are no events -> create a recruitment campaign
        $event->delete();

        $recruitment = DotTuyenThanhVien::query()->create([
            'TenDotTuyen' => 'Đợt tuyển thành viên Gen 6',
            'TrangThai' => DotTuyenTrangThai::DANG_TUYEN,
            'NgayBatDau' => now()->subDay(),
            'NgayKetThuc' => now()->addMonth(),
            'MaCLB' => $club->MaCLB,
            'LaBanNhap' => false,
        ]);

        // When no event exists, it displays the active club recruitment
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ĐANG MỞ ĐỢT TUYỂN THÀNH VIÊN')
            ->assertSee('Đợt tuyển thành viên Gen 6')
            ->assertSee('CLB Trí tuệ nhân tạo')
            ->assertSee('0') // 0 events
            ->assertSee('1'); // 1 club
    }
}
