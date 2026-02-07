<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained('service_desk_accounts')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('service_desk_categories')->cascadeOnDelete();
            $table->foreignUlid('subcategory_id')->constrained('service_desk_subcategories')->cascadeOnDelete();
            $table->foreignUlid('template_id')->constrained('dynamic_resources_templates')->cascadeOnDelete();
            $table->boolean('assigned')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_requests');
    }
};
