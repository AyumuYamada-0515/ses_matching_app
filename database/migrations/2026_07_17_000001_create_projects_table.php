<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('required_skills');
            $table->text('preferred_skills')->nullable();
            $table->string('process');
            $table->string('location');
            $table->string('remote_type');
            $table->unsignedInteger('min_price');
            $table->unsignedInteger('max_price')->nullable();
            $table->unsignedSmallInteger('recruitment_count')->default(1);
            $table->date('start_date')->nullable();
            $table->date('application_deadline');
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
