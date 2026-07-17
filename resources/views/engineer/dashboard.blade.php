@extends('layouts.app') @section('title','SEダッシュボード') @section('content')<h1 class="mb-6 text-3xl font-bold">SEダッシュボード
</h1>@if($matched)<div class="mb-5 rounded bg-amber-100 p-4">現在マッチング中です。新しい「気になる！」は送信できません。</div>@endif<div
    class="grid gap-5 md:grid-cols-2">
    <div class="rounded-xl bg-white p-6 shadow">公開案件 <strong class="ml-3 text-3xl">{{ $open }}</strong></div>
    <div class="rounded-xl bg-white p-6 shadow">送信数 <strong class="ml-3 text-3xl">{{ $interests }}</strong></div>
</div>@endsection