<?php

namespace App\Http\Controllers\Engineer;

use App\Enums\InterestStatus;
use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InterestController extends Controller
{
    public function index()
    {
        return view('engineer.interests.index', ['interests' => auth()->user()->interests()->with('project')->latest()->paginate(20)]);
    }

    public function store(Project $project)
    {
        $user = auth()->user();
        abort_unless($project->isAcceptingApplications(), 422, 'この案件は現在募集していません。');
        if ($user->hasActiveMatch()) {
            return back()->withErrors(['interest' => '現在マッチング中の案件があるため、新しい案件へ「気になる！」を送信できません。']);
        } try {
            DB::transaction(fn () => Interest::create(['project_id' => $project->id, 'engineer_id' => $user->id, 'message' => Interest::DEFAULT_MESSAGE, 'status' => InterestStatus::Pending]));
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ['23000', '23505'])) {
                return back()->withErrors(['interest' => 'この案件には送信済みです。']);
            }throw $e;
        }

return back()->with('success', '「気になる！」を送信しました。');
    }

    public function destroy(Interest $interest)
    {
        $this->authorize('cancel', $interest);
        $interest->update(['status' => InterestStatus::Cancelled]);

        return back()->with('success', '「気になる！」を取り消しました。');
    }
}
