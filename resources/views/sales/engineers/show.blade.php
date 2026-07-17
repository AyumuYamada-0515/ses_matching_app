@extends('layouts.app')
@section('title', $engineer->name)
@section('content')
<a class="text-sm text-indigo-600" href="{{ route('sales.engineers.index') }}">← 担当SE一覧へ</a><div class="mt-4 rounded-xl bg-white p-6 shadow"><h1 class="text-3xl font-bold">{{ $engineer->name }}</h1><dl class="mt-6 space-y-5"><div><dt class="text-sm font-semibold text-slate-500">メールアドレス</dt><dd>{{ $engineer->email }}</dd></div><div><dt class="text-sm font-semibold text-slate-500">プロフィール・スキル・希望条件</dt><dd class="whitespace-pre-wrap">{{ $engineer->profile ?: '未登録です。' }}</dd></div></dl></div>
@endsection
