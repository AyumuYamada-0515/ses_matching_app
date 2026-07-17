<?php

namespace App\Http\Controllers\Sales;

use App\Enums\AssignmentInvitationStatus;
use App\Http\Controllers\Controller;
use App\Mail\AssignmentInvitationMail;
use App\Models\AssignmentInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AssignmentInvitationController extends Controller
{
    public function index()
    {
        $engineers = User::query()->where('role', 'engineer')->whereDoesntHave('salesRepresentatives', fn ($query) => $query->whereKey(auth()->id()))->with(['receivedAssignmentInvitations' => fn ($query) => $query->where('sales_user_id', auth()->id())])->orderBy('name')->paginate(20);

        return view('sales.assignment-invitations.index', compact('engineers'));
    }

    public function store(User $engineer)
    {
        abort_unless($engineer->isEngineer(), 422, 'SEを選択してください。');
        abort_if($engineer->salesRepresentatives()->whereKey(auth()->id())->exists(), 422, 'このSEは既にあなたの担当です。');
        $existing = AssignmentInvitation::where('sales_user_id', auth()->id())->where('engineer_id', $engineer->id)->first();
        if ($existing?->status === AssignmentInvitationStatus::Pending) {
            return back()->withErrors(['invitation' => 'このSEには送信済みです。']);
        }
        $invitation = AssignmentInvitation::updateOrCreate(['sales_user_id' => auth()->id(), 'engineer_id' => $engineer->id], ['status' => AssignmentInvitationStatus::Pending, 'responded_at' => null]);
        Mail::to($engineer)->send(new AssignmentInvitationMail($invitation));

        return back()->with('success', '担当勧誘メールを送信しました。');
    }
}
