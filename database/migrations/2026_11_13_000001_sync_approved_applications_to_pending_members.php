<?php

use App\Enums\DonUngTrangThai;
use App\Enums\ThanhVienTrangThai;
use App\Models\DonUngTuyen;
use App\Models\ThanhVienCLB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $approvedApplications = DonUngTuyen::query()
            ->with(['viTri.dotTuyen'])
            ->where('TrangThai', DonUngTrangThai::DA_DUYET->value)
            ->get();

        foreach ($approvedApplications as $app) {
            $clubId = $app->viTri?->dotTuyen?->MaCLB;
            if (! $clubId || ! $app->MaNguoiUngTuyen) {
                continue;
            }

            $exists = ThanhVienCLB::query()
                ->where('MaCLB', $clubId)
                ->where('MaNguoiDung', $app->MaNguoiUngTuyen)
                ->exists();

            if (! $exists) {
                ThanhVienCLB::query()->create([
                    'MaCLB' => $clubId,
                    'MaNguoiDung' => $app->MaNguoiUngTuyen,
                    'ChucVu' => $app->viTri?->TenViTri ?: 'Thành viên',
                    'NgayThamGia' => $app->NgayCapNhat ?? $app->NgayNopDon ?? now(),
                    'TrangThai' => ThanhVienTrangThai::CHO_PHE_DUYET,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
