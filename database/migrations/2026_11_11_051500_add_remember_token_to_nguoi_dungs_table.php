<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table): void {
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table): void {
            $table->dropRememberToken();
        });
    }
};
