<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_desk_account_sla_policy', function (Blueprint $table) {
            $table->foreignUlid('account_id')->constrained('service_desk_accounts')->cascadeOnDelete();
            $table->foreignUlid('sla_policy_id')->constrained('service_desk_sla_policies')->cascadeOnDelete();
            $table->primary(['account_id', 'sla_policy_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_desk_account_sla_policy');
    }
};
