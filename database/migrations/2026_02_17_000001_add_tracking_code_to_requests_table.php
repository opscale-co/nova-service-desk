<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_desk_requests', function (Blueprint $table) {
            $table->string('tracking_code')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_desk_requests', function (Blueprint $table) {
            $table->dropColumn('tracking_code');
        });
    }
};
