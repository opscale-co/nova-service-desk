<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_resolutions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('subcategory_id');
            $table->string('documentation_url');
            $table->longText('notes');
            $table->string('author');
            $table->date('last_modified');
            $table->timestamps();

            $table->foreign('subcategory_id')
                ->references('id')
                ->on('catalog_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_resolutions');
    }
};
