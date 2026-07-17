<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('status')->default('pending')->index();
            $table->timestamps();
            $table->unique(['project_id', 'engineer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
