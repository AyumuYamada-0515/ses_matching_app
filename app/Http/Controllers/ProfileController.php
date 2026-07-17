<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'profile' => ['nullable', 'string', 'max:5000']]);
        $request->user()->update($data);

        return back()->with('success', 'プロフィールを更新しました。');
    }
}
