<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_category_template', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('category_id')->constrained('catalogs')->cascadeOnDelete();
            $table->foreignUlid('template_id')->constrained('dynamic_resources_templates')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_category_template');
    }
};
