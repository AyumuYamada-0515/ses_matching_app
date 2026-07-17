<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['sales_user_id', 'engineer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_invitations');
    }
};
