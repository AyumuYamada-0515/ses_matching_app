@extends('layouts.app')
@section('title', '担当営業一覧')
@section('content')
<h1 class="mb-6 text-3xl font-bold">担当営業一覧</h1><div class="grid gap-4 md:grid-cols-2">@forelse($salesRepresentatives as $salesRepresentative)<div class="rounded-xl bg-white p-6 shadow"><h2 class="text-2xl font-bold">{{ $salesRepresentative->name }}</h2><dl class="mt-5 space-y-4"><div><dt class="text-sm font-semibold text-slate-500">メールアドレス</dt><dd>{{ $salesRepresentative->email }}</dd></div><div><dt class="text-sm font-semibold text-slate-500">プロフィール</dt><dd class="whitespace-pre-wrap">{{ $salesRepresentative->profile ?: '未登録です。' }}</dd></div></dl></div>@empty<div class="rounded-xl bg-amber-50 p-5 text-amber-800">担当営業はまだ設定されていません。</div>@endforelse</div>
@endsection
