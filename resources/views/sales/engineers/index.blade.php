@extends('layouts.app')
@section('title', '担当SE一覧')
@section('content')
<h1 class="mb-6 text-3xl font-bold">担当SE一覧</h1><div class="grid gap-4 md:grid-cols-2">@forelse($engineers as $engineer)<a class="rounded-xl bg-white p-5 shadow" href="{{ route('sales.engineers.show', $engineer) }}"><h2 class="text-lg font-bold">{{ $engineer->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $engineer->email }}</p></a>@empty<p>担当SEはいません。</p>@endforelse</div><div class="mt-6">{{ $engineers->links() }}</div>
@endsection
