<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_subcategories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('category_id')->constrained('service_desk_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('key');
            $table->text('description')->nullable();
            $table->string('impact')->nullable();
            $table->string('urgency')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_subcategories');
    }
};
