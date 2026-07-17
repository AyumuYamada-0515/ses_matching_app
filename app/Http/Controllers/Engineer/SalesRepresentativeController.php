<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;

class SalesRepresentativeController extends Controller
{
    public function show()
    {
        return view('engineer.sales-representative.show', ['salesRepresentatives' => auth()->user()->salesRepresentatives()->orderBy('name')->get()]);
    }
}
