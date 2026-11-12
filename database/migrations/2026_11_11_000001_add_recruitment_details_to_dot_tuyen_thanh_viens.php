<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dot_tuyen_thanh_viens', function (Blueprint $table) {
            $table->string('URLPoster', 2048)->nullable();
            $table->string('DoiTuong', 255)->nullable();
            $table->string('DiaDiemPhongVan', 255)->nullable();
            $table->dateTime('NgayPhongVan')->nullable();
            $table->dateTime('NgayCongBoKetQua')->nullable();
            $table->boolean('LaBanNhap')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('dot_tuyen_thanh_viens', function (Blueprint $table) {
            $table->dropColumn([
                'URLPoster',
                'DoiTuong',
                'DiaDiemPhongVan',
                'NgayPhongVan',
                'NgayCongBoKetQua',
                'LaBanNhap',
            ]);
        });
    }
};
