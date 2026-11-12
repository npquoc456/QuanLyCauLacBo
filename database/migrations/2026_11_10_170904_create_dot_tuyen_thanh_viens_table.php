<?php

use App\Enums\DotTuyenTrangThai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * DOTTUYENTHANHVIEN(MaDotTuyen, TenDotTuyen, MoTa,
     * NgayBatDau, NgayKetThuc, TrangThai, MaCLB)
     */
    public function up(): void
    {
        Schema::create('dot_tuyen_thanh_viens', function (Blueprint $table) {
            $table->increments('MaDotTuyen');
            $table->string('TenDotTuyen', 255);
            $table->string('MoTa', 500)->nullable();
            $table->dateTime('NgayBatDau');
            $table->dateTime('NgayKetThuc');

            $table->enum('TrangThai', array_map(
                fn (DotTuyenTrangThai $DTTrangThai) => $DTTrangThai->value,
                DotTuyenTrangThai::cases(),
            ))->nullable();

            $table->unsignedInteger('MaCLB');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dot_tuyen_thanh_viens');
    }
};
