@extends('layouts.app') @section('content')<h1 class="mb-6 text-3xl font-bold">{{ $project->title }}</h1>
<div class="rounded-xl bg-white p-6 shadow">
    <p class="whitespace-pre-line">{{ $project->description }}</p>
    <dl class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach(['required_skills'=>'必須スキル','preferred_skills'=>'歓迎スキル','process'=>'担当工程','location'=>'勤務地'] as $k=>$l)
        <div>
            <dt class="text-sm text-slate-500">{{ $l }}</dt>
            <dd>{{ $project->$k?:'—' }}</dd>
        </div>@endforeach<div>
            <dt class="text-sm text-slate-500">単価</dt>
            <dd>{{ $project->min_price }}〜{{ $project->max_price??'応相談' }}万円</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">募集期限</dt>
            <dd>{{ $project->application_deadline->format('Y/m/d') }}</dd>
        </div>
    </dl>
    <div class="mt-8 border-t pt-6">@if($sent)<button class="rounded bg-slate-300 px-6 py-3"
            disabled>送信済み</button>@elseif(auth()->user()->hasActiveMatch())<button
            class="rounded bg-slate-300 px-6 py-3" disabled>気になる！</button>
        <p class="mt-2 text-amber-700">現在マッチング中の案件があるため、新しい案件へ「気になる！」を送信できません。</p>@else<form method="post"
            action="{{ route('engineer.interests.store',$project) }}">@csrf<button
                class="rounded bg-pink-600 px-6 py-3 font-bold text-white">気になる！</button></form>@endif
    </div>
</div>@endsection