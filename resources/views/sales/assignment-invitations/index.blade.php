@extends('layouts.app')
@section('title','担当候補SE')
@section('content')
<h1 class="mb-2 text-3xl font-bold">担当候補SE</h1><p class="mb-6 text-slate-600">あなたがまだ担当していないSEへ勧誘メールを送れます。</p><div class="space-y-4">@forelse($engineers as $engineer) @php($invitation = $engineer->receivedAssignmentInvitations->first())<div class="flex items-center justify-between rounded-xl bg-white p-5 shadow"><div><h2 class="font-bold">{{ $engineer->name }}</h2><p class="text-sm text-slate-500">{{ $engineer->email }}</p><p class="mt-2 text-sm">{{ Str::limit($engineer->profile ?: 'プロフィール未登録', 100) }}</p></div><form method="post" action="{{ route('sales.assignment-invitations.store', $engineer) }}">@csrf<button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ $invitation?->status === App\Enums\AssignmentInvitationStatus::Pending ? '勧誘メールを再送する' : '担当に勧誘する' }}</button></form></div>@empty<p>担当候補のSEはいません。</p>@endforelse</div><div class="mt-6">{{ $engineers->links() }}</div>
@endsection
