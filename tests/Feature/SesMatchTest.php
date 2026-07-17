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

class SesMatchTest extends TestCase
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

    private function project(User $sales, array $overrides = []): Project
    {
        return Project::create(array_merge(['sales_user_id' => $sales->id, 'title' => '公開案件', 'description' => '概要', 'required_skills' => 'PHP', 'process' => '実装', 'location' => '東京', 'remote_type' => 'hybrid', 'min_price' => 50, 'max_price' => 70, 'recruitment_count' => 1, 'application_deadline' => today()->addWeek(), 'status' => ProjectStatus::Open], $overrides));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_role_routes_are_protected(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer($sales);
        $this->actingAs($engineer)->get('/sales/projects')->assertForbidden();
        $this->actingAs($sales)->get('/engineer/projects')->assertForbidden();
    }

    public function test_only_open_projects_are_visible_to_engineer(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer($sales);
        $this->project($sales);
        $this->project($sales, ['title' => '下書き', 'status' => ProjectStatus::Draft]);
        $this->actingAs($engineer)->get('/engineer/projects')->assertOk()->assertSee('公開案件')->assertDontSee('下書き');
    }

    public function test_engineer_cannot_send_duplicate_interest(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer($sales);
        $project = $this->project($sales);
        $this->actingAs($engineer)->post(route('engineer.interests.store', $project))->assertSessionHas('success');
        $this->post(route('engineer.interests.store', $project))->assertSessionHasErrors('interest');
        $this->assertDatabaseCount('interests', 1);
    }

    public function test_active_match_blocks_new_interest_but_completed_allows_it(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer($sales);
        $first = $this->project($sales);
        $second = $this->project($sales, ['title' => '別案件']);
        $interest = Interest::create(['project_id' => $first->id, 'engineer_id' => $engineer->id, 'message' => 'test', 'status' => InterestStatus::Matched]);
        $this->actingAs($engineer)->post(route('engineer.interests.store', $second))->assertSessionHasErrors('interest');
        $interest->update(['status' => InterestStatus::Completed]);
        $this->post(route('engineer.interests.store', $second))->assertSessionHas('success');
    }

    public function test_other_sales_cannot_update_project_or_interest(): void
    {
        $owner = $this->sales();
        $other = $this->sales();
        $engineer = $this->engineer($owner);
        $project = $this->project($owner);
        $interest = Interest::create(['project_id' => $project->id, 'engineer_id' => $engineer->id, 'message' => 'test', 'status' => InterestStatus::Pending]);
        $this->actingAs($other)->get(route('sales.projects.edit', $project))->assertForbidden();
        $this->patch(route('sales.interests.update', $interest), ['status' => 'matched'])->assertForbidden();
    }
}
