<?php

use App\Enums\DonUngTrangThai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DONUNGTUYEN(MaDon, MaViTri, MaNguoiUngTuyen, NoiDung,
     * URLHoSo, TrangThai, MaNguoiXetDuyet, GhiChu, NgayNopDon, NgayCapNhat)
     */
    public function up(): void
    {
        Schema::create('don_ung_tuyens', function (Blueprint $table) {
            $table->increments('MaDon');
            $table->unsignedInteger('MaViTri');
            $table->unsignedInteger('MaNguoiUngTuyen');
            $table->string('NoiDung', 500)->nullable();
            $table->string('URLHoSo', 2048)->nullable();

            $table->enum('TrangThai', array_map(
                fn (DonUngTrangThai $DUTrangThai) => $DUTrangThai->value,
                DonUngTrangThai::cases(),
            ))->nullable();

            $table->unsignedInteger('MaNguoiXetDuyet')->nullable();
            $table->string('GhiChu', 1024)->nullable();
            $table->dateTime('NgayNopDon');
            $table->dateTime('NgayCapNhat');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_ung_tuyens');
    }
};
