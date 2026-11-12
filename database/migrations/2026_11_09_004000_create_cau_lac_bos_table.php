<?php

use App\Enums\CauLacBoTrangThai;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cau_lac_bos', function (Blueprint $table) {
            $table->increments('MaCLB');
            $table->string('TenCLB', 255);
            $table->text('MoTa')->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('SDT', 30)->nullable();

            $table->enum('TrangThai', array_map(
                fn (CauLacBoTrangThai $trangThai) => $trangThai->value,
                CauLacBoTrangThai::cases(),
            ))->nullable();

            $table->string('URLLogo', 2048)->nullable();
            $table->string('URLAnhNen', 2048)->nullable();
            $table->dateTime('NgayTao')->useCurrent();
            $table->unsignedInteger('MaDanhMucCLB')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_lac_bos');
    }
};
