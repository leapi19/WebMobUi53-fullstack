<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PollDashboardController extends Controller
{
    public function __invoke(Request $request)
{
    $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();
    $props = json_encode([
        'polls' => $polls,
        'loginUrl' => route('login'),
        'username' => 'test name',
        'i18n' => __('ui.polls'),
    ]);
    return view('polls.dashboard', compact('props'));
}
}
