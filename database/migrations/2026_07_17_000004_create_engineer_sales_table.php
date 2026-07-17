<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sales_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['engineer_id', 'sales_user_id']);
        });
        DB::table('users')->whereNotNull('sales_user_id')->orderBy('id')->each(function ($user) {
            DB::table('engineer_sales')->insertOrIgnore(['engineer_id' => $user->id, 'sales_user_id' => $user->sales_user_id, 'created_at' => now(), 'updated_at' => now()]);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_user_id');
        });
    }

    public function down(): void
    {
        $hasMultipleAssignments = DB::table('engineer_sales')
            ->select('engineer_id')
            ->groupBy('engineer_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleAssignments) {
            throw new RuntimeException(
                'engineer_sales contains engineers assigned to multiple sales representatives. '
                .'Rolling back would discard assignments, so the migration was stopped.',
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sales_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
        DB::table('engineer_sales')->orderBy('id')->get()->each(function ($assignment) {
            DB::table('users')->where('id', $assignment->engineer_id)->update(['sales_user_id' => $assignment->sales_user_id]);
        });
        Schema::dropIfExists('engineer_sales');
    }
};
