<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('request_id')->constrained('service_desk_requests')->cascadeOnDelete();
            $table->foreignUlid('assignee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('assigner_id')->constrained('users')->cascadeOnDelete();
            $table->string('key')->unique();
            $table->string('title');
            $table->longText('description');
            $table->enum('status', ['Open', 'In Progress', 'Blocked', 'Resolved', 'Closed', 'Cancelled']);
            $table->string('status_alias')->nullable();
            $table->enum('priority', ['Critical', 'High', 'Medium', 'Low', 'Planning']);
            $table->float('priority_score');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('contacted_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_tasks');
    }
};
