<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_workflow_stages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_id')->constrained('service_desk_workflows')->cascadeOnDelete();
            $table->string('name');
            $table->string('description', 512)->nullable();
            $table->string('color')->nullable();
            $table->string('maps_to_status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_workflow_stages');
    }
};
