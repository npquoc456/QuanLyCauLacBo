<?php

use App\Enums\TrangThaiDangKy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DANGKY(MaDangKy, MaNguoiDung,
     * MaSuKien, TrangThai, ThoiGianCheckIn, NgayDangKy, MaQRToken)
     */
    public function up(): void
    {
        Schema::create('dang_kies', function (Blueprint $table) {
            $table->increments('MaDangKy');
            $table->unsignedInteger('MaNguoiDung');
            $table->unsignedInteger('MaSuKien');

            $table->enum('TrangThai', array_map(
                fn (TrangThaiDangKy $TrangThai) => $TrangThai->value,
                TrangThaiDangKy::cases()
            ))->nullable();

            $table->dateTime('ThoiGianCheckIn');
            $table->dateTime('NgayDangKy');

            $table->string('MaQRToken')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dang_kies');
    }
};
