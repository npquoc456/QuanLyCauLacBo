<?php

use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
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
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->increments('MaNguoiDung');
            $table->string('TenTaiKhoan', 255)->unique();
            $table->string('MatKhauHash', 255);
            $table->string('MSSV', 20)->unique();
            $table->string('HoTen', 255);
            $table->string('SDT', 30)->nullable();
            $table->string('Email', 255)->unique();
            $table->date('NgaySinh');
            $table->string('URLAnhDaiDien', 2048)->nullable();
            $table->string('Khoa', 255)->nullable();
            $table->string('Nganh', 255)->nullable();
            $table->string('KhoaHoc', 50)->nullable();

            $table->enum('VaiTro', array_map(
                fn (NguoiDungVaiTro $vaiTro) => $vaiTro->value,
                NguoiDungVaiTro::cases(),
            ))->default(NguoiDungVaiTro::USER->value);

            $table->enum('TrangThai', array_map(
                fn (NguoiDungTrangThai $trangThai) => $trangThai->value,
                NguoiDungTrangThai::cases(),
            ))->default(NguoiDungTrangThai::HOAT_DONG->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dungs');
    }
};
