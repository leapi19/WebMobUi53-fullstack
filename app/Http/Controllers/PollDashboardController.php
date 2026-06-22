<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PollDashboardController extends Controller
{
    public function __invoke(Request $request)
{ // recup sondages du user connecté avec eager loading
    $polls = $request->user()->polls()->with('options')->orderBy('created_at', 'desc')->get();
    $props = json_encode([ // prépare données
        'polls' => $polls,
        'loginUrl' => route('login'),
        'username' => 'test name',
        'i18n' => __('ui.polls'),
    ]);
    return view('polls.dashboard', compact('props'));
}
}
