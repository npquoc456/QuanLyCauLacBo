<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE thanh_vien_c_l_b_s MODIFY COLUMN TrangThai ENUM('DANG_HOAT_DONG', 'TAM_NGHI', 'DA_ROI_CLB', 'CHO_PHE_DUYET', 'CHO_DUYET_ROI') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE thanh_vien_c_l_b_s MODIFY COLUMN TrangThai ENUM('DANG_HOAT_DONG', 'TAM_NGHI', 'DA_ROI_CLB', 'CHO_PHE_DUYET') NULL");
        }
    }
};
