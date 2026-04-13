<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_desk_tasks', function (Blueprint $table) {
            $table->foreignUlid('workflow_id')->nullable()->constrained('service_desk_workflows')->nullOnDelete();
            $table->foreignUlid('workflow_stage_id')->nullable()->constrained('service_desk_workflow_stages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_desk_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workflow_stage_id');
            $table->dropConstrainedForeignId('workflow_id');
        });
    }
};
