<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSales() ? null : false;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->sales_user_id === $user->id;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
