<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class EngineerSalesMigrationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_rollback_stops_before_losing_multiple_sales_assignments(): void
    {
        $engineer = User::factory()->create(['role' => UserRole::Engineer]);
        $sales = User::factory()->count(2)->create(['role' => UserRole::Sales]);
        $engineer->salesRepresentatives()->attach($sales);

        $migration = require database_path('migrations/2026_07_17_000004_create_engineer_sales_table.php');

        try {
            $migration->down();
            $this->fail('The migration should reject a lossy rollback.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('would discard assignments', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasTable('engineer_sales'));
        $this->assertFalse(Schema::hasColumn('users', 'sales_user_id'));
        $this->assertDatabaseCount('engineer_sales', 2);
    }
}
