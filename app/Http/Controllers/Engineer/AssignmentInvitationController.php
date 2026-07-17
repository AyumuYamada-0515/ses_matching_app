<?php

namespace App\Http\Controllers\Engineer;

use App\Enums\AssignmentInvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\AssignmentInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssignmentInvitationController extends Controller
{
    public function index()
    {
        return view('engineer.assignment-invitations.index', ['invitations' => auth()->user()->receivedAssignmentInvitations()->with('salesRepresentative')->latest()->paginate(20)]);
    }

    public function update(Request $request, AssignmentInvitation $invitation)
    {
        abort_unless($invitation->engineer_id === auth()->id(), 403);
        $data = $request->validate(['decision' => ['required', Rule::in(['accept', 'reject'])]]);
        DB::transaction(function () use ($invitation, $data) {
            $lockedInvitation = AssignmentInvitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedInvitation->status === AssignmentInvitationStatus::Pending, 422, 'この勧誘には回答済みです。');
            if ($data['decision'] === 'accept') {
                auth()->user()->salesRepresentatives()->syncWithoutDetaching([$lockedInvitation->sales_user_id]);
                $lockedInvitation->update(['status' => AssignmentInvitationStatus::Accepted, 'responded_at' => now()]);
            } else {
                $lockedInvitation->update(['status' => AssignmentInvitationStatus::Rejected, 'responded_at' => now()]);
            }
        });

        return back()->with('success', $data['decision'] === 'accept' ? '担当勧誘を承諾しました。' : '担当勧誘を拒否しました。');
    }
}
