<?php

namespace Tests\Feature;

use App\Enums\AssignmentInvitationStatus;
use App\Enums\UserRole;
use App\Mail\AssignmentInvitationMail;
use App\Models\AssignmentInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationAndAssignmentInvitationTest extends TestCase
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

    public function test_sales_and_engineer_can_register(): void
    {
        $this->post(route('register'), ['name' => '新規営業', 'email' => 'new-sales@example.com', 'role' => 'sales', 'password' => 'password', 'password_confirmation' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new-sales@example.com', 'role' => 'sales']);
        auth()->logout();
        $this->post(route('register'), ['name' => '新規SE', 'email' => 'new-se@example.com', 'role' => 'engineer', 'password' => 'password', 'password_confirmation' => 'password'])->assertRedirect(route('dashboard'));
        $engineer = User::where('email', 'new-se@example.com')->firstOrFail();
        $this->assertCount(0, $engineer->salesRepresentatives);
    }

    public function test_registration_rejects_invalid_role(): void
    {
        $this->post(route('register'), ['name' => '管理者', 'email' => 'admin@example.com', 'role' => 'admin', 'password' => 'password', 'password_confirmation' => 'password'])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_sales_sees_engineers_they_do_not_already_manage_and_can_invite(): void
    {
        Mail::fake();
        $sales = $this->sales();
        $candidate = $this->engineer();
        $ownEngineer = $this->engineer($sales);
        $otherSales = $this->sales();
        $managedByAnother = $this->engineer($otherSales);
        $this->actingAs($sales)->get(route('sales.assignment-invitations.index'))->assertOk()->assertSee($candidate->name)->assertSee($managedByAnother->name)->assertDontSee($ownEngineer->name);
        $this->post(route('sales.assignment-invitations.store', $managedByAnother))->assertSessionHas('success');
        Mail::assertSent(AssignmentInvitationMail::class, fn ($mail) => $mail->hasTo($managedByAnother->email));
        $this->post(route('sales.assignment-invitations.store', $ownEngineer))->assertStatus(422);
    }

    public function test_engineer_can_accept_multiple_sales_invitations(): void
    {
        $firstSales = $this->sales();
        $secondSales = $this->sales();
        $engineer = $this->engineer();
        $first = AssignmentInvitation::create(['sales_user_id' => $firstSales->id, 'engineer_id' => $engineer->id, 'status' => AssignmentInvitationStatus::Pending]);
        $second = AssignmentInvitation::create(['sales_user_id' => $secondSales->id, 'engineer_id' => $engineer->id, 'status' => AssignmentInvitationStatus::Pending]);
        $this->actingAs($engineer)->patch(route('engineer.assignment-invitations.update', $first), ['decision' => 'accept'])->assertSessionHas('success');
        $this->patch(route('engineer.assignment-invitations.update', $second), ['decision' => 'accept'])->assertSessionHas('success');
        $this->assertDatabaseHas('engineer_sales', ['engineer_id' => $engineer->id, 'sales_user_id' => $firstSales->id]);
        $this->assertDatabaseHas('engineer_sales', ['engineer_id' => $engineer->id, 'sales_user_id' => $secondSales->id]);
        $this->assertCount(2, $engineer->fresh()->salesRepresentatives);
        $this->get(route('engineer.sales-representative.show'))->assertOk()->assertSee($firstSales->name)->assertSee($secondSales->name);
    }

    public function test_engineer_can_reject_invitation_and_cannot_answer_for_another_engineer(): void
    {
        $sales = $this->sales();
        $engineer = $this->engineer();
        $other = $this->engineer();
        $invitation = AssignmentInvitation::create(['sales_user_id' => $sales->id, 'engineer_id' => $engineer->id, 'status' => AssignmentInvitationStatus::Pending]);
        $this->actingAs($other)->patch(route('engineer.assignment-invitations.update', $invitation), ['decision' => 'reject'])->assertForbidden();
        $this->actingAs($engineer)->patch(route('engineer.assignment-invitations.update', $invitation), ['decision' => 'reject'])->assertSessionHas('success');
        $this->assertDatabaseHas('assignment_invitations', ['id' => $invitation->id, 'status' => 'rejected']);
        $this->assertCount(0, $engineer->fresh()->salesRepresentatives);
    }
}
