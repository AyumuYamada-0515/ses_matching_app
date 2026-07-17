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

        $attributes = ['sales_user_id' => auth()->id(), 'engineer_id' => $engineer->id];
        $existing = AssignmentInvitation::query()->where($attributes)->first();
        $isResend = $existing?->status === AssignmentInvitationStatus::Pending;

        $invitation = AssignmentInvitation::updateOrCreate(
            $attributes,
            ['status' => AssignmentInvitationStatus::Pending, 'responded_at' => null],
        );

        Mail::to($engineer)->queue(new AssignmentInvitationMail($invitation));

        return back()->with(
            'success',
            $isResend ? '担当勧誘メールを再送キューに登録しました。' : '担当勧誘メールを送信キューに登録しました。',
        );
    }
}
