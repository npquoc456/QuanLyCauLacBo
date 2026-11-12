<?php

namespace Tests\Feature;

use App\Models\CauLacBo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CauLacBoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_schema_contains_the_requested_fields(): void
    {
        $this->assertSame('MaCLB', (new CauLacBo)->getKeyName());
        $this->assertTrue(Schema::hasColumns('cau_lac_bos', [
            'MaCLB',
            'TenCLB',
            'MoTa',
            'Email',
            'SDT',
            'TrangThai',
            'URLLogo',
            'URLAnhNen',
            'NgayTao',
            'MaDanhMucCLB',
        ]));
    }

    public function test_club_api_supports_list_show_create_update_and_delete(): void
    {
        $createResponse = $this->postJson('/api/caulacbo', [
            'TenCLB' => 'Câu lạc bộ Công nghệ',
            'MoTa' => 'Cộng đồng yêu công nghệ',
            'Email' => 'tech@example.test',
            'SDT' => '0901234567',
            'TrangThai' => 'HOAT_DONG',
            'URLLogo' => 'https://example.test/logo.png',
            'URLAnhNen' => 'https://example.test/cover.jpg',
            'MaDanhMucCLB' => 4,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.ten_clb', 'Câu lạc bộ Công nghệ')
            ->assertJsonPath('data.email', 'tech@example.test')
            ->assertJsonPath('data.trang_thai', 'HOAT_DONG')
            ->assertJsonPath('data.ma_danh_muc_clb', 4);

        $maCLB = $createResponse->json('data.ma_clb');
        $this->assertNotNull($createResponse->json('data.ngay_tao'));

        $this->getJson('/api/caulacbo')
            ->assertOk()
            ->assertJsonPath('data.0.ma_clb', $maCLB);

        $this->getJson("/api/caulacbo/{$maCLB}")
            ->assertOk()
            ->assertJsonPath('data.url_anh_nen', 'https://example.test/cover.jpg');

        $this->patchJson("/api/caulacbo/{$maCLB}", [
            'TenCLB' => 'Câu lạc bộ Công nghệ cập nhật',
            'TrangThai' => 'TAM_DUNG',
        ])->assertOk()
            ->assertJsonPath('data.ten_clb', 'Câu lạc bộ Công nghệ cập nhật')
            ->assertJsonPath('data.trang_thai', 'TAM_DUNG');

        $this->deleteJson("/api/caulacbo/{$maCLB}")
            ->assertOk()
            ->assertJsonPath('message', 'Đã xóa câu lạc bộ thành công.');

        $this->assertDatabaseMissing('cau_lac_bos', ['MaCLB' => $maCLB]);
    }

    public function test_club_api_validates_required_and_optional_fields(): void
    {
        $this->postJson('/api/caulacbo', [
            'TenCLB' => '',
            'Email' => 'not-an-email',
            'URLLogo' => 'not-a-url',
            'MaDanhMucCLB' => 0,
            'TrangThai' => 'KHONG_HOP_LE',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['TenCLB', 'Email', 'URLLogo', 'MaDanhMucCLB', 'TrangThai']);

        $this->getJson('/api/caulacbo/999')
            ->assertNotFound();
    }
}
