<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\User;

class EngineerController extends Controller
{
    public function index()
    {
        return view('sales.engineers.index', ['engineers' => auth()->user()->assignedEngineers()->orderBy('name')->paginate(20)]);
    }

    public function show(User $engineer)
    {
        abort_unless($engineer->isEngineer() && auth()->user()->assignedEngineers()->whereKey($engineer->id)->exists(), 403);

        return view('sales.engineers.show', compact('engineer'));
    }
}
