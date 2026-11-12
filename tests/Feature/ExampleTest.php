<?php

namespace Tests\Feature;

use App\Enums\SuKienTrangThai;
use App\Models\SuKien;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Học tập &amp; Hoạt động cùng UniClub', false)
            ->assertSee('Hồ sơ sinh viên &amp; Hoạt động CLB', false)
            ->assertSee('CLB đang mở đơn đăng ký')
            ->assertSee('Đăng nhập để xem thông tin cá nhân')
            ->assertSee('0 CLB')
            ->assertDontSee('CodeStorm')
            ->assertDontSee('club-volunteer.svg')
            ->assertDontSee('club-language.svg');
    }

    public function test_the_event_list_routes_event_creation_through_club_management(): void
    {
        $this->get(route('sukien.index'))
            ->assertOk()
            ->assertDontSee('data-open-event-modal', false)
            ->assertDontSee('Tạo sự kiện mới')
            ->assertDontSee('create-event-modal')
            ->assertSee(route('caulacbo.index'), false);

        $this->get(route('sukien.create', ['ma_clb' => 2]))
            ->assertOk()
            ->assertSee('tạo sự kiện cho CLB #2')
            ->assertSee('action="'.route('sukien.store').'"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('type="file" name="URLAnhNen"', false)
            ->assertSee('accept="image/*"', false)
            ->assertSee('name="MaCLB" value="2"', false)
            ->assertSee('name="MaDanhMucSuKien"', false)
            ->assertSee('name="LoaiSuKien"', false)
            ->assertSee('name="PhanLoaiNoiBo"', false)
            ->assertSee('name="ThoiGianMoDangKy"', false)
            ->assertSee('name="ThoiGianDongDangKy"', false)
            ->assertSee('type="date" name="ThoiGianBatDau"', false)
            ->assertSee('type="time" name="ThoiGianBatDau_time"', false)
            ->assertSee('Chọn ngày trên lịch và nhập giờ theo định dạng 24 tiếng.');
    }

    public function test_event_form_combines_calendar_dates_with_24_hour_times(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));

        $this->post(route('sukien.store'), [
            'TenSuKien' => 'Sự kiện lịch và giờ',
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-01',
            'ThoiGianMoDangKy_time' => '08:15',
            'ThoiGianDongDangKy' => '2026-11-05',
            'ThoiGianDongDangKy_time' => '17:45',
            'ThoiGianBatDau' => '2026-11-06',
            'ThoiGianBatDau_time' => '09:30',
            'ThoiGianKetThuc' => '2026-11-06',
            'ThoiGianKetThuc_time' => '11:00',
            'LoaiSuKien' => 'NOI_BO',
            'PhanLoaiNoiBo' => 'SINH_HOAT_DINH_KY',
            'MaCLB' => 2,
        ])->assertRedirect(route('sukien.index', ['ma_clb' => 2]));

        $this->assertDatabaseHas('su_kiens', [
            'TenSuKien' => 'Sự kiện lịch và giờ',
            'ThoiGianMoDangKy' => '2026-11-01 08:15:00',
            'ThoiGianDongDangKy' => '2026-11-05 17:45:00',
            'ThoiGianBatDau' => '2026-11-06 09:30:00',
            'ThoiGianKetThuc' => '2026-11-06 11:00:00',
            'MaCLB' => 2,
            'LoaiSuKien' => 'NOI_BO',
            'PhanLoaiNoiBo' => 'SINH_HOAT_DINH_KY',
        ]);
    }

    public function test_event_form_requires_a_time_when_a_calendar_date_is_selected(): void
    {
        $createUrl = route('sukien.create', ['ma_clb' => 2]);

        $this->from($createUrl)->post(route('sukien.store'), [
            'TenSuKien' => 'Sự kiện thiếu giờ',
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-05',
            'ThoiGianMoDangKy_time' => '08:00',
            'ThoiGianDongDangKy' => '2026-11-05',
            'ThoiGianDongDangKy_time' => '18:00',
            'ThoiGianBatDau' => '2026-11-06',
            'ThoiGianKetThuc' => '2026-11-06',
            'ThoiGianKetThuc_time' => '11:00',
            'MaCLB' => 2,
        ])->assertRedirect($createUrl);

        $this->get($createUrl)
            ->assertOk()
            ->assertSee('Vui lòng nhập giờ hợp lệ theo định dạng 24 tiếng.')
            ->assertSee('name="ThoiGianBatDau_time" value=""', false);
    }

    public function test_event_schema_uses_ma_su_kien_and_the_requested_fields(): void
    {
        $this->assertSame('MaSuKien', (new SuKien)->getKeyName());
        $this->assertTrue(Schema::hasColumns('su_kiens', [
            'MaSuKien',
            'TenSuKien',
            'MoTa',
            'URLAnhNen',
            'DiaDiem',
            'SoLuongToiDa',
            'ThoiGianMoDangKy',
            'ThoiGianDongDangKy',
            'ThoiGianBatDau',
            'ThoiGianKetThuc',
            'LoaiSuKien',
            'PhanLoaiNoiBo',
            'MaCLB',
            'MaDanhMucSuKien',
        ]));
        $this->assertFalse(Schema::hasColumn('su_kiens', 'id'));
        $this->assertFalse(Schema::hasColumn('su_kiens', 'TrangThai'));
    }

    public function test_event_status_is_calculated_from_registration_and_event_times(): void
    {
        $event = SuKien::create([
            'TenSuKien' => 'Sự kiện trạng thái tự động',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 11:00:00',
        ]);

        $this->travelTo(Carbon::parse('2026-11-05 08:59:59'));
        $this->assertSame(SuKienTrangThai::CHUA_MO_DANG_KY, $event->TrangThai);

        $this->travelTo(Carbon::parse('2026-11-05 09:00:00'));
        $this->assertSame(SuKienTrangThai::DANG_MO_DANG_KY, $event->TrangThai);

        $this->travelTo(Carbon::parse('2026-11-05 18:00:00'));
        $this->assertSame(SuKienTrangThai::DA_DONG_DANG_KY, $event->TrangThai);

        $this->travelTo(Carbon::parse('2026-11-06 09:00:00'));
        $this->assertSame(SuKienTrangThai::SAP_DIEN_RA, $event->TrangThai);

        $this->travelTo(Carbon::parse('2026-11-06 11:00:00'));
        $this->assertSame(SuKienTrangThai::DA_DONG_DANG_KY, $event->TrangThai);
    }

    public function test_event_api_returns_the_new_event_attributes(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));

        $this->postJson('/api/sukien', [
            'TenSuKien' => 'Sự kiện API',
            'MoTa' => 'Mô tả API',
            'URLAnhNen' => 'https://example.test/api.jpg',
            'DiaDiem' => 'Hội trường B',
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-01 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'LoaiSuKien' => 'CONG_KHAI',
            'PhanLoaiNoiBo' => 'SU_KIEN_NOI_BO',
            'MaCLB' => 3,
            'MaDanhMucSuKien' => 7,
        ])->assertCreated()
            ->assertJsonPath('data.ten_su_kien', 'Sự kiện API')
            ->assertJsonPath('data.url_anh_nen', 'https://example.test/api.jpg')
            ->assertJsonPath('data.trang_thai', 'DANG_MO_DANG_KY')
            ->assertJsonPath('data.loai_su_kien', 'CONG_KHAI')
            ->assertJsonPath('data.phan_loai_noi_bo', null)
            ->assertJsonPath('data.ma_clb', 3)
            ->assertJsonPath('data.ma_danh_muc_su_kien', 7);

        $this->postJson('/api/sukien', [
            'TenSuKien' => 'Sự kiện có loại không hợp lệ',
            'SoLuongToiDa' => 20,
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'LoaiSuKien' => 'KHONG_HOP_LE',
            'PhanLoaiNoiBo' => 'KHONG_HOP_LE',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['LoaiSuKien', 'PhanLoaiNoiBo']);

    }

    public function test_event_background_image_can_be_uploaded_and_replaced(): void
    {
        Storage::fake('public');

        $this->post(route('sukien.store'), [
            'TenSuKien' => 'Sự kiện có ảnh nền',
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'URLAnhNen' => UploadedFile::fake()->create('background.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('sukien.index'));

        $event = SuKien::query()->where('TenSuKien', 'Sự kiện có ảnh nền')->firstOrFail();
        $firstPath = ltrim(substr(parse_url($event->URLAnhNen, PHP_URL_PATH), strlen('/storage/')), '/');
        Storage::disk('public')->assertExists($firstPath);

        $this->post(route('sukien.update', $event->MaSuKien), [
            '_method' => 'PUT',
            'TenSuKien' => $event->TenSuKien,
            'SoLuongToiDa' => 40,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'URLAnhNen' => UploadedFile::fake()->create('replacement.png', 100, 'image/png'),
        ])->assertRedirect(route('sukien.index'));

        $event->refresh();
        $secondPath = ltrim(substr(parse_url($event->URLAnhNen, PHP_URL_PATH), strlen('/storage/')), '/');

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertExists($secondPath);
        Storage::disk('public')->assertMissing($firstPath);
    }

    public function test_updating_event_without_a_background_upload_keeps_current_image(): void
    {
        $event = SuKien::create([
            'TenSuKien' => 'Sự kiện giữ ảnh',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'URLAnhNen' => 'https://example.test/current.jpg',
        ]);

        $this->put(route('sukien.update', $event->MaSuKien), [
            'TenSuKien' => 'Sự kiện giữ ảnh sau sửa',
            'SoLuongToiDa' => 20,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
        ])->assertRedirect(route('sukien.index'));

        $this->assertDatabaseHas('su_kiens', [
            'MaSuKien' => $event->MaSuKien,
            'URLAnhNen' => 'https://example.test/current.jpg',
        ]);
    }

    public function test_validation_errors_are_shown_on_the_event_creation_page(): void
    {
        $createUrl = route('sukien.create', ['ma_clb' => 2]);

        $this->from($createUrl)
            ->post(route('sukien.store'), [
                'TenSuKien' => '',
                'MaCLB' => 2,
                'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
                'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
                'ThoiGianBatDau' => '2026-11-06 09:00:00',
                'ThoiGianKetThuc' => '2026-11-06 10:00:00',
                'SoLuongToiDa' => 50,
            ])->assertRedirect($createUrl);

        $this->get($createUrl)
            ->assertOk()
            ->assertSee('name="MaCLB" value="2"', false)
            ->assertSee('Tên sự kiện không được để trống.');
    }

    public function test_edit_and_detail_pages_use_the_shared_event_form(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00'));

        $event = SuKien::create([
            'TenSuKien' => 'Workshop thử nghiệm',
            'MoTa' => 'Mô tả chi tiết',
            'URLAnhNen' => 'https://example.test/workshop.jpg',
            'DiaDiem' => 'Hội trường A',
            'ThoiGianMoDangKy' => '2026-11-01 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'SoLuongToiDa' => 80,
            'MaCLB' => 2,
            'MaDanhMucSuKien' => 3,
        ]);

        $this->get(route('sukien.edit', $event->MaSuKien))
            ->assertOk()
            ->assertSee('value="Workshop thử nghiệm"', false)
            ->assertSee('type="date" name="ThoiGianBatDau" value="2026-11-06"', false)
            ->assertSee('type="time" name="ThoiGianBatDau_time" value="09:00"', false)
            ->assertSee('value="Hội trường A"', false)
            ->assertSee('value="80"', false)
            ->assertSee('value="2"', false)
            ->assertSee('DANG MO DANG KY')
            ->assertDontSee('name="TrangThai"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Lưu thay đổi');

        $this->get(route('sukien.show', $event->MaSuKien))
            ->assertOk()
            ->assertSee('value="Workshop thử nghiệm"', false)
            ->assertSee('value="Hội trường A"', false)
            ->assertSee('src="https://example.test/workshop.jpg"', false)
            ->assertSee('disabled', false)
            ->assertSee('Mô tả chi tiết');

        $this->put(route('sukien.update', $event->MaSuKien), [
            'TenSuKien' => 'Workshop đã cập nhật',
            'MoTa' => 'Mô tả mới',
            'URLAnhNen' => 'https://example.test/updated.jpg',
            'DiaDiem' => 'Hội trường B',
            'ThoiGianMoDangKy' => '2026-11-01 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'SoLuongToiDa' => 80,
            'MaCLB' => 2,
            'MaDanhMucSuKien' => 3,
        ])->assertRedirect(route('sukien.index', ['ma_clb' => 2]));

        $this->assertDatabaseHas('su_kiens', [
            'MaSuKien' => $event->MaSuKien,
            'TenSuKien' => 'Workshop đã cập nhật',
            'DiaDiem' => 'Hội trường B',
        ]);
    }

    public function test_event_detail_links_to_attendance_dashboard_and_home_replaces_attendance_nav(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 09:30:00'));

        $event = SuKien::create([
            'TenSuKien' => 'Buổi sinh hoạt CLB',
            'ThoiGianMoDangKy' => '2026-11-01 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'SoLuongToiDa' => 60,
        ]);

        $this->get(route('sukien.show', $event->MaSuKien))
            ->assertOk()
            ->assertSee(route('sukien.attendance', $event->MaSuKien), false)
            ->assertSee('Điểm danh &amp; QR', false);

        $this->get(route('sukien.attendance', $event->MaSuKien))
            ->assertOk()
            ->assertSee('Điểm danh &amp; Trình chiếu QR', false)
            ->assertSee('Buổi sinh hoạt CLB')
            ->assertSee('SAP DIEN RA')
            ->assertSee('Chưa có dữ liệu điểm danh')
            ->assertDontSee('Nguyễn Anh Thư');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Trang chủ')
            ->assertDontSee('Điểm danh &amp; QR', false);
    }

    public function test_the_event_form_posts_to_the_web_route(): void
    {
        $this->travelTo(Carbon::parse('2026-11-05 12:00:00'));

        $this->get(route('sukien.create'))
            ->assertOk()
            ->assertSee('action="'.route('sukien.store').'"', false);

        $this->post(route('sukien.store'), [
            'TenSuKien' => 'Sự kiện thử nghiệm',
            'ThoiGianMoDangKy' => '05/11/26 09:00',
            'ThoiGianDongDangKy' => '05/11/26 18:00',
            'ThoiGianBatDau' => '06/11/26 09:00',
            'ThoiGianKetThuc' => '06/11/26 10:00',
            'SoLuongToiDa' => 50,
            'URLAnhNen' => 'https://example.test/event.jpg',
            'MaCLB' => 1,
            'MaDanhMucSuKien' => 4,
        ])->assertRedirect(route('sukien.index', ['ma_clb' => 1]));

        $this->assertDatabaseHas('su_kiens', [
            'TenSuKien' => 'Sự kiện thử nghiệm',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
        ]);

        $this->post(route('sukien.store'), [
            'TenSuKien' => 'Sự kiện nội bộ thử nghiệm',
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 10:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'SoLuongToiDa' => 25,
        ])->assertRedirect(route('sukien.index'));

        $this->assertDatabaseHas('su_kiens', [
            'TenSuKien' => 'Sự kiện thử nghiệm',
            'URLAnhNen' => 'https://example.test/event.jpg',
            'MaCLB' => 1,
            'MaDanhMucSuKien' => 4,
        ]);

        $this->get(route('sukien.index', ['trang_thai' => 'DANG_MO_DANG_KY']))
            ->assertOk()
            ->assertSee('Sự kiện thử nghiệm')
            ->assertDontSee('Sự kiện nội bộ thử nghiệm');

        $this->get(route('sukien.index', ['trang_thai' => 'DA_DONG_DANG_KY']))
            ->assertOk()
            ->assertSee('Sự kiện nội bộ thử nghiệm')
            ->assertDontSee('Sự kiện thử nghiệm');
    }

    public function test_the_event_list_shows_ten_newest_events_per_page(): void
    {
        for ($number = 1; $number <= 11; $number++) {
            $event = SuKien::create([
                'TenSuKien' => "Sự kiện {$number}",
                'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
                'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
                'ThoiGianBatDau' => '2026-11-06 09:00:00',
                'ThoiGianKetThuc' => '2026-11-06 10:00:00',
                'SoLuongToiDa' => 50,
            ]);

            DB::table('su_kiens')->where('MaSuKien', $event->MaSuKien)->update([
                'created_at' => Carbon::parse('2026-11-01')->addMinutes($number),
            ]);
        }

        $this->get(route('sukien.index'))
            ->assertOk()
            ->assertSee('>Sự kiện 11</a>', false)
            ->assertSee('>Sự kiện 2</a>', false)
            ->assertDontSee('>Sự kiện 1</a>', false)
            ->assertSee('page=2', false);

        $this->get(route('sukien.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('>Sự kiện 1</a>', false)
            ->assertDontSee('>Sự kiện 2</a>', false);
    }

    public function test_selected_events_can_be_marked_and_deleted(): void
    {
        $events = collect(['Sự kiện A', 'Sự kiện B'])->map(fn ($name) => SuKien::create([
            'TenSuKien' => $name,
            'ThoiGianMoDangKy' => '2026-11-05 09:00:00',
            'ThoiGianDongDangKy' => '2026-11-05 18:00:00',
            'ThoiGianBatDau' => '2026-11-06 09:00:00',
            'ThoiGianKetThuc' => '2026-11-06 10:00:00',
            'SoLuongToiDa' => 20,
        ]));

        $ids = $events->pluck('MaSuKien')->all();

        $this->post(route('sukien.bulk'), [
            'event_ids' => $ids,
            'action' => 'mark',
        ])->assertRedirect(route('sukien.index'));

        $this->assertDatabaseCount('su_kiens', 2);
        $this->assertSame(2, SuKien::whereIn('MaSuKien', $ids)->where('DaDanhDau', true)->count());

        $this->post(route('sukien.bulk'), [
            'event_ids' => $ids,
            'action' => 'delete',
        ])->assertRedirect(route('sukien.index'));

        $this->assertDatabaseCount('su_kiens', 0);
    }
}
