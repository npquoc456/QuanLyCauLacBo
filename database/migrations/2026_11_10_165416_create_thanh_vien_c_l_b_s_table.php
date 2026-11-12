<?php

use App\Enums\ThanhVienTrangThai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * THANHVIENCLB(MaThanhVien, MaCLB, MaNguoiDung, ChucVu, NgayThamGia, TrangThai)
     */
    public function up(): void
    {
        Schema::create('thanh_vien_c_l_b_s', function (Blueprint $table) {
            $table->increments('MaThanhVien');
            $table->unsignedInteger('MaCLB')->nullable();
            $table->unsignedInteger('MaNguoiDung')->nullable();
            $table->string('ChucVu', 50);
            $table->dateTime('NgayThamGia');

            $table->enum('TrangThai', array_map(
                fn (ThanhVienTrangThai $TVTrangThai) => $TVTrangThai->value,
                ThanhVienTrangThai::cases(),
            ))->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_vien_c_l_b_s');
    }
};
