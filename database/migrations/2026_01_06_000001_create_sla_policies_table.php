<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_sla_policies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('priority', ['Critical', 'High', 'Medium', 'Low', 'Planning']);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->integer('max_contact_time');
            $table->integer('max_resolution_time');
            $table->integer('update_frequency')->nullable();
            $table->json('supported_channels');
            $table->string('service_timezone');
            $table->json('service_time');
            $table->json('service_exceptions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_sla_policies');
    }
};
