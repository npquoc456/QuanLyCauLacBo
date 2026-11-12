<?php

use App\Enums\SuKienLoai;
use App\Enums\SuKienPhanLoaiNoiBo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('su_kiens', function (Blueprint $table) {
            $table->increments('MaSuKien');
            $table->string('TenSuKien', 255);
            $table->text('MoTa')->nullable();
            $table->string('URLAnhNen', 2048)->nullable();
            $table->string('DiaDiem', 255)->nullable();
            $table->integer('SoLuongToiDa')->default(0);
            $table->dateTime('ThoiGianMoDangKy');
            $table->dateTime('ThoiGianDongDangKy');
            $table->dateTime('ThoiGianBatDau');
            $table->dateTime('ThoiGianKetThuc');
            $table->unsignedInteger('MaCLB')->nullable();
            $table->unsignedInteger('MaDanhMucSuKien')->nullable();
            $table->enum('LoaiSuKien', array_map(
                fn (SuKienLoai $loai) => $loai->value,
                SuKienLoai::cases(),
            ))->default(SuKienLoai::NOI_BO->value);
            $table->enum('PhanLoaiNoiBo', array_map(
                fn (SuKienPhanLoaiNoiBo $phanLoai) => $phanLoai->value,
                SuKienPhanLoaiNoiBo::cases(),
            ))->nullable();
            $table->boolean('DaDanhDau')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('su_kiens');
    }
};
