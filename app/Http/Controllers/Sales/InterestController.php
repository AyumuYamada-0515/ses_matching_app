<?php

namespace App\Http\Controllers\Sales;

use App\Enums\InterestStatus;
use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterestController extends Controller
{
    public function index()
    {
        return view('sales.interests.index', ['interests' => Interest::with(['project', 'engineer'])->whereHas('project', fn ($q) => $q->where('sales_user_id', auth()->id()))->latest()->paginate(20)]);
    }

    public function update(Request $request, Interest $interest)
    {
        $this->authorize('update', $interest);
        $data = $request->validate(['status' => ['required', Rule::enum(InterestStatus::class)]]);
        $interest->update($data);

        return back()->with('success', 'ステータスを更新しました。');
    }
}
