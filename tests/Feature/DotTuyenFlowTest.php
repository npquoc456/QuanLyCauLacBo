<?php

namespace Tests\Feature;

use App\Enums\DonUngTrangThai;
use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Enums\ThanhVienTrangThai;
use App\Models\Ban;
use App\Models\CauLacBo;
use App\Models\DonUngTuyen;
use App\Models\DotTuyenThanhVien;
use App\Models\NguoiDung;
use App\Models\ThanhVienCLB;
use App\Models\ViTriTuyen;
use App\Notifications\RecruitmentApplicationReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DotTuyenFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_publish_recruitment_with_positions_on_home(): void
    {
        $manager = $this->createUser('manager', 'manager@example.test', '24002001');
        $club = $this->createManagedClub($manager);
        $department = Ban::query()->create([
            'TenBan' => 'Ban Truyền thông',
            'MaCLB' => $club->MaCLB,
        ]);
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertOk()
            ->assertSee('Bộ đợt tuyển &amp; Quản lý tuyển sinh CLB', false)
            ->assertSee('Tạo đợt tuyển');

        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.create', $club->MaCLB))
            ->assertOk()
            ->assertSee('Tạo đợt tuyển mới')
            ->assertSee('Thời gian mở nhận hồ sơ')
            ->assertSee('Chỉ tiêu vị trí tuyển theo Ban')
            ->assertSee('Lưu bản nháp');

        $this->actingAs($manager)
            ->post(route('caulacbo.recruitments.store', $club->MaCLB), $this->campaignPayload($department->MaBan))
            ->assertRedirect(route('caulacbo.recruitments.index', $club->MaCLB));

        $campaign = DotTuyenThanhVien::query()->where('TenDotTuyen', 'Tuyển thành viên Gen mới')->firstOrFail();
        $position = ViTriTuyen::query()->where('MaDotTuyen', $campaign->MaDotTuyen)->firstOrFail();
        $this->assertDatabaseHas('dot_tuyen_thanh_viens', [
            'MaDotTuyen' => $campaign->MaDotTuyen,
            'MaCLB' => $club->MaCLB,
            'LaBanNhap' => false,
            'DoiTuong' => 'Sinh viên toàn trường',
            'DiaDiemPhongVan' => 'Phòng CLB',
        ]);
        $this->assertDatabaseHas('vi_tri_tuyens', [
            'MaViTri' => $position->MaViTri,
            'TenViTri' => 'Cộng tác viên Nội dung',
            'SoLuong' => 6,
            'MaBan' => $department->MaBan,
        ]);

        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.edit', [$club->MaCLB, $campaign->MaDotTuyen]))
            ->assertOk()
            ->assertSee('Chỉnh sửa đợt tuyển')
            ->assertSee('value="Tuyển thành viên Gen mới"', false);
        $updatePayload = $this->campaignPayload($department->MaBan);
        $updatePayload['TenDotTuyen'] = 'Tuyển thành viên Gen mới 2026';
        $updatePayload['positions'][0]['MaViTri'] = $position->MaViTri;
        $updatePayload['positions'][0]['SoLuong'] = 8;
        $this->actingAs($manager)
            ->put(route('caulacbo.recruitments.update', [$club->MaCLB, $campaign->MaDotTuyen]), $updatePayload)
            ->assertRedirect(route('caulacbo.recruitments.index', $club->MaCLB));
        $this->assertDatabaseHas('dot_tuyen_thanh_viens', [
            'MaDotTuyen' => $campaign->MaDotTuyen,
            'TenDotTuyen' => 'Tuyển thành viên Gen mới 2026',
        ]);
        $this->assertDatabaseHas('vi_tri_tuyens', [
            'MaViTri' => $position->MaViTri,
            'SoLuong' => 8,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tuyển thành viên Gen mới 2026')
            ->assertSee('Xem &amp; ứng tuyển', false)
            ->assertSee(route('dot-tuyen.show', $campaign->MaDotTuyen), false);
        $this->get(route('dot-tuyen.show', $campaign->MaDotTuyen))
            ->assertOk()
            ->assertSee('Cộng tác viên Nội dung');
    }

    public function test_applicant_submission_notifies_manager_and_manager_can_review_profile(): void
    {
        Storage::fake('local');
        $manager = $this->createUser('manager2', 'manager2@example.test', '24002002');
        $applicant = $this->createUser('applicant', 'applicant@example.test', '24002003');
        $club = $this->createManagedClub($manager);
        $campaign = $this->createOpenCampaign($club);
        $position = $campaign->viTriTuyens()->create([
            'TenViTri' => 'Thành viên Ban Sự kiện',
            'Mota' => 'Hỗ trợ tổ chức chương trình.',
            'SoLuong' => 4,
        ]);

        $this->actingAs($applicant)
            ->post(route('dot-tuyen.apply', $campaign->MaDotTuyen), [
                'MaViTri' => $position->MaViTri,
                'NoiDung' => 'Tôi muốn tham gia và đóng góp cho CLB.',
                'resume' => UploadedFile::fake()->create('ho-so.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('dot-tuyen.show', $campaign->MaDotTuyen));

        $application = DonUngTuyen::query()->firstOrFail();
        $this->assertDatabaseHas('don_ung_tuyens', [
            'MaDon' => $application->MaDon,
            'MaNguoiUngTuyen' => $applicant->MaNguoiDung,
            'TrangThai' => DonUngTrangThai::CHO_XY_LY->value,
        ]);
        Storage::disk('local')->assertExists($application->URLHoSo);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $manager->MaNguoiDung,
            'type' => RecruitmentApplicationReceived::class,
        ]);

        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertOk()
            ->assertSee('Thông báo hồ sơ ứng tuyển')
            ->assertSee('1 thông báo ứng tuyển mới');
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.applications', [$club->MaCLB, $campaign->MaDotTuyen]))
            ->assertOk()
            ->assertSee('Thành viên thử nghiệm')
            ->assertSee('Thành viên Ban Sự kiện');
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.application', [$club->MaCLB, $application->MaDon]))
            ->assertOk()
            ->assertSee('applicant@example.test')
            ->assertSee('24002003')
            ->assertSee('Tôi muốn tham gia và đóng góp cho CLB.');
        $this->assertNotNull($manager->notifications()->firstOrFail()->read_at);
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.resume', [$club->MaCLB, $application->MaDon]))
            ->assertDownload('ho-so.pdf');

        $this->actingAs($manager)
            ->patch(route('caulacbo.recruitments.review', [$club->MaCLB, $application->MaDon]), [
                'TrangThai' => DonUngTrangThai::DA_DUYET->value,
                'GhiChu' => 'Hồ sơ phù hợp.',
            ])
            ->assertRedirect(route('caulacbo.recruitments.application', [$club->MaCLB, $application->MaDon]));

        $this->assertDatabaseHas('don_ung_tuyens', [
            'MaDon' => $application->MaDon,
            'TrangThai' => DonUngTrangThai::DA_DUYET->value,
            'MaNguoiXetDuyet' => $manager->MaNguoiDung,
            'GhiChu' => 'Hồ sơ phù hợp.',
        ]);
    }

    public function test_draft_recruitment_is_not_visible_to_applicants_and_other_users_cannot_manage_club(): void
    {
        $manager = $this->createUser('manager3', 'manager3@example.test', '24002004');
        $otherUser = $this->createUser('visitor', 'visitor@example.test', '24002005');
        $club = $this->createManagedClub($manager);
        $this->actingAs($otherUser)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertForbidden();

        $payload = $this->campaignPayload();
        $payload['action'] = 'draft';
        $this->actingAs($manager)
            ->post(route('caulacbo.recruitments.store', $club->MaCLB), $payload)
            ->assertRedirect(route('caulacbo.recruitments.index', $club->MaCLB));

        $draft = DotTuyenThanhVien::query()->where('TenDotTuyen', 'Tuyển thành viên Gen mới')->firstOrFail();
        $this->assertTrue($draft->LaBanNhap);
        $this->get(route('home'))->assertDontSee('Tuyển thành viên Gen mới');
        $this->get(route('dot-tuyen.show', $draft->MaDotTuyen))->assertNotFound();

        $this->actingAs($manager)
            ->delete(route('caulacbo.recruitments.destroy', [$club->MaCLB, $draft->MaDotTuyen]))
            ->assertRedirect(route('caulacbo.recruitments.index', $club->MaCLB));
        $this->assertDatabaseMissing('dot_tuyen_thanh_viens', ['MaDotTuyen' => $draft->MaDotTuyen]);
    }

    public function test_recruitment_created_from_redesigned_form_appears_on_home_and_notifies_manager(): void
    {
        Storage::fake('local');
        $manager = $this->createUser('manager_flow', 'manager_flow@example.test', '24003001');
        $applicant = $this->createUser('applicant_flow', 'applicant_flow@example.test', '24003002');
        $club = $this->createManagedClub($manager);

        // 1. Manager opens create page, sees form matching Image 2
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.create', $club->MaCLB))
            ->assertOk()
            ->assertSee('Tạo đợt tuyển mới')
            ->assertSee('Thời gian mở nhận hồ sơ')
            ->assertSee('Chỉ tiêu vị trí ứng tuyển theo Ban');

        // 2. Manager creates recruitment with separate date/time fields and TrangThai
        $createPayload = [
            'action' => 'publish',
            'TenDotTuyen' => 'Tuyển Thành viên & CTV Gen 6 - Kỳ Xuân 2026',
            'MoTa' => 'Tuyển nhân sự Ban Kỹ thuật AI, Ban Truyền thông...',
            'TrangThai' => 'DANG_TUYEN',
            'DoiTuong' => 'Sinh viên năm 1, năm 2, năm 3 toàn trường',
            'DiaDiemPhongVan' => 'Văn phòng CLB Tòa A5 hoặc Phỏng vấn Online qua Google Meet',
            'NgayBatDau_date' => now()->subDay()->format('Y-m-d'),
            'NgayBatDau_time' => '08:00',
            'NgayKetThuc_date' => now()->addDays(20)->format('Y-m-d'),
            'NgayKetThuc_time' => '23:59',
            'NgayPhongVan_date' => now()->addDays(23)->format('Y-m-d'),
            'NgayPhongVan_time' => '08:30',
            'NgayCongBoKetQua_date' => now()->addDays(27)->format('Y-m-d'),
            'NgayCongBoKetQua_time' => '17:00',
            'positions' => [
                [
                    'TenViTri' => 'Ban Kỹ thuật & R&D AI',
                    'Mota' => 'Phát triển dự án AI',
                    'SoLuong' => 15,
                ],
                [
                    'TenViTri' => 'Ban Truyền thông & Media',
                    'Mota' => 'Thiết kế ấn phẩm truyền thông',
                    'SoLuong' => 12,
                ],
            ],
        ];

        $this->actingAs($manager)
            ->post(route('caulacbo.recruitments.store', $club->MaCLB), $createPayload)
            ->assertRedirect(route('caulacbo.recruitments.index', $club->MaCLB));

        $campaign = DotTuyenThanhVien::query()->where('TenDotTuyen', 'Tuyển Thành viên & CTV Gen 6 - Kỳ Xuân 2026')->firstOrFail();
        $this->assertEquals('DANG_TUYEN', $campaign->TrangThai->value);

        // 3. Appears on Home Page
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tuyển Thành viên &amp; CTV Gen 6 - Kỳ Xuân 2026', false)
            ->assertSee($club->TenCLB);

        // 4. Another user visits recruitment page and applies
        $position = $campaign->viTriTuyens()->where('TenViTri', 'Ban Kỹ thuật & R&D AI')->firstOrFail();

        $this->actingAs($applicant)
            ->post(route('dot-tuyen.apply', $campaign->MaDotTuyen), [
                'MaViTri' => $position->MaViTri,
                'NoiDung' => 'Em có kinh nghiệm về Python và mong muốn tham gia ban AI.',
                'resume' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('dot-tuyen.show', $campaign->MaDotTuyen));

        // 5. Manager receives notification and views application details
        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.index', $club->MaCLB))
            ->assertOk()
            ->assertSee('Thông báo hồ sơ ứng tuyển')
            ->assertSee('1 thông báo ứng tuyển mới');

        $application = DonUngTuyen::query()->where('MaNguoiUngTuyen', $applicant->MaNguoiDung)->firstOrFail();

        $this->actingAs($manager)
            ->get(route('caulacbo.recruitments.application', [$club->MaCLB, $application->MaDon]))
            ->assertOk()
            ->assertSee($applicant->HoTen)
            ->assertSee('24003002')
            ->assertSee('applicant_flow@example.test')
            ->assertSee('Em có kinh nghiệm về Python và mong muốn tham gia ban AI.')
            ->assertSee('Tải hồ sơ đính kèm');
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignPayload(?int $departmentId = null): array
    {
        return [
            'action' => 'publish',
            'TenDotTuyen' => 'Tuyển thành viên Gen mới',
            'MoTa' => 'Cùng tham gia các hoạt động và dự án của câu lạc bộ.',
            'DoiTuong' => 'Sinh viên toàn trường',
            'DiaDiemPhongVan' => 'Phòng CLB',
            'NgayBatDau' => now()->subHour()->format('Y-m-d\TH:i'),
            'NgayKetThuc' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'NgayPhongVan' => now()->addDays(12)->format('Y-m-d\TH:i'),
            'NgayCongBoKetQua' => now()->addDays(14)->format('Y-m-d\TH:i'),
            'positions' => [[
                'TenViTri' => 'Cộng tác viên Nội dung',
                'Mota' => 'Viết bài và hỗ trợ truyền thông.',
                'SoLuong' => 6,
                'MaBan' => $departmentId,
            ]],
        ];
    }

    private function createUser(string $username, string $email, string $studentNumber): NguoiDung
    {
        return NguoiDung::query()->create([
            'TenTaiKhoan' => $username,
            'MatKhauHash' => Hash::make('StrongPass123'),
            'MSSV' => $studentNumber,
            'HoTen' => 'Thành viên thử nghiệm',
            'Email' => $email,
            'SDT' => '0900000000',
            'NgaySinh' => '2004-05-18',
            'Khoa' => 'Khoa Công nghệ',
            'Nganh' => 'Công nghệ thông tin',
            'KhoaHoc' => 'K20',
            'VaiTro' => NguoiDungVaiTro::USER,
            'TrangThai' => NguoiDungTrangThai::HOAT_DONG,
        ]);
    }

    private function createManagedClub(NguoiDung $manager): CauLacBo
    {
        $club = CauLacBo::query()->create([
            'TenCLB' => 'CLB UniClub',
            'MoTa' => 'Câu lạc bộ công nghệ.',
            'TrangThai' => 'DANG_HOAT_DONG',
        ]);
        ThanhVienCLB::query()->create([
            'MaNguoiDung' => $manager->MaNguoiDung,
            'MaCLB' => $club->MaCLB,
            'ChucVu' => 'CHU_NHIEM',
            'NgayThamGia' => now(),
            'TrangThai' => ThanhVienTrangThai::DANG_HOAT_DONG,
        ]);

        return $club;
    }

    private function createOpenCampaign(CauLacBo $club): DotTuyenThanhVien
    {
        return $club->dotTuyenThanhViens()->create([
            'TenDotTuyen' => 'Tuyển thành viên Gen mới',
            'MoTa' => 'Đợt tuyển đang mở.',
            'DoiTuong' => 'Sinh viên toàn trường',
            'DiaDiemPhongVan' => 'Phòng CLB',
            'NgayBatDau' => now()->subHour(),
            'NgayKetThuc' => now()->addDays(10),
            'NgayPhongVan' => now()->addDays(12),
            'NgayCongBoKetQua' => now()->addDays(14),
            'LaBanNhap' => false,
            'TrangThai' => 'DANG_TUYEN',
        ]);
    }
}
