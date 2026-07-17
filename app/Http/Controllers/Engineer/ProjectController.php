<?php

namespace App\Http\Controllers\Engineer;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return view('engineer.projects.index', ['projects' => Project::where('status', ProjectStatus::Open)->whereDate('application_deadline', '>=', today())->latest()->paginate(12)]);
    }

    public function show(Project $project)
    {
        abort_unless($project->isAcceptingApplications(), 404);
        $sent = auth()->user()->interests()->where('project_id', $project->id)->exists();

        return view('engineer.projects.show', compact('project', 'sent'));
    }
}
