<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * BAN(MaBan, TenBan, MoTa, MaCLB)
     */
    public function up(): void
    {
        Schema::create('bans', function (Blueprint $table) {
            $table->increments('MaBan');
            $table->string('TenBan', 100);
            $table->string('MoTa', 500)->nullable();
            $table->unsignedInteger('MaCLB')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
