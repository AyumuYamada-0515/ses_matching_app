<?php

namespace Tests\Feature;

use App\Enums\InterestStatus;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Interest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentAndInterestCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function sales(): User
    {
        return User::factory()->create(['role' => UserRole::Sales]);
    }

    private function engineer(?User $sales = null): User
    {
        $engineer = User::factory()->create(['role' => UserRole::Engineer]);
        if ($sales) {
            $engineer->salesRepresentatives()->attach($sales);
        }

        return $engineer;
    }

    private function project(User $sales): Project
    {
        return Project::create(['sales_user_id' => $sales->id, 'title' => '案件', 'description' => '概要', 'required_skills' => 'PHP', 'process' => '実装', 'location' => '東京', 'remote_type' => 'hybrid', 'min_price' => 50, 'max_price' => 70, 'recruitment_count' => 1, 'application_deadline' => today()->addWeek(), 'status' => ProjectStatus::Open]);
    }

    public function test_sales_can_only_view_assigned_engineers(): void
    {
        $sales = $this->sales();
        $otherSales = $this->sales();
        $assigned = $this->engineer($sales);
        $other = $this->engineer($otherSales);
        $this->actingAs($sales)->get(route('sales.engineers.index'))->assertOk()->assertSee($assigned->name)->assertDontSee($other->name);
        $this->get(route('sales.engineers.show', $assigned))->assertOk()->assertSee($assigned->email);
        $this->get(route('sales.engineers.show', $other))->assertForbidden();
    }

    public function test_engineer_can_view_assigned_sales_profile(): void
    {
        $sales = $this->sales();
        $sales->update(['profile' => '営業プロフィール']);
        $engineer = $this->engineer($sales);
        $this->actingAs($engineer)->get(route('engineer.sales-representative.show'))->assertOk()->assertSee($sales->name)->assertSee('営業プロフィール');
    }

    public function test_engineer_can_cancel_only_own_pending_interest(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer($sales);
        $other = $this->engineer($sales);
        $interest = Interest::create(['project_id' => $this->project($sales)->id, 'engineer_id' => $engineer->id, 'message' => 'test', 'status' => InterestStatus::Pending]);
        $this->actingAs($other)->delete(route('engineer.interests.destroy', $interest))->assertForbidden();
        $this->actingAs($engineer)->delete(route('engineer.interests.destroy', $interest))->assertSessionHas('success');
        $this->assertDatabaseHas('interests', ['id' => $interest->id, 'status' => InterestStatus::Cancelled->value]);
        $this->delete(route('engineer.interests.destroy', $interest->fresh()))->assertForbidden();
    }
}
