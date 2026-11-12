<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * VITRITUYEN(MaViTri, TenViTri, MoTa, SoLuong, MaDotTuyen, MaBan)
     */
    public function up(): void
    {
        Schema::create('vi_tri_tuyens', function (Blueprint $table) {
            $table->increments('MaViTri');
            $table->string('TenViTri', 255);
            $table->string('Mota', 500);
            $table->integer('SoLuong')->default(0);

            $table->unsignedInteger('MaDotTuyen')->nullable();
            $table->unsignedInteger('MaBan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vi_tri_tuyens');
    }
};
