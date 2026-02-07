<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_insights', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('account_id');
            $table->string('author');
            $table->enum('scope', ['Business', 'Technical', 'Legal', 'Operational', 'Other']);
            $table->string('title');
            $table->longText('details');
            $table->string('attachment_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')
                ->references('id')
                ->on('service_desk_accounts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_insights');
    }
};
