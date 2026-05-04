<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;

class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    /**
     * Remove the specified poll.
     */
    public function remove(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();

        return response()->json(['message' => 'success'], 200);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'question' => 'required|string|max:500',
        'is_draft' => 'boolean',
        'allow_multiple_choices' => 'boolean',
        'results_public' => 'boolean',
        'duration' => 'nullable|integer|min:1',
        'options' => 'required|array|min:2',
        'options.*.label' => 'required|string|max:255',
    ]);

    $poll = new Poll();
    $poll->user_id = $request->user()->id;
    $poll->title = $validated['title'] ?? null;
    $poll->question = $validated['question'];
    $poll->is_draft = $validated['is_draft'] ?? true;
    $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
    $poll->results_public = $validated['results_public'] ?? false;
    $poll->duration = $validated['duration'] ?? null;
    $poll->secret_token = \Illuminate\Support\Str::random(32);

    if (!$poll->is_draft) {
        $poll->started_at = now();
        if ($poll->duration) {
            $poll->ends_at = now()->addSeconds($poll->duration);
        }
    }

    $poll->save();

    foreach ($validated['options'] as $opt) {
        $option = new \App\Models\PollOption();
        $option->poll_id = $poll->id;
        $option->label = $opt['label'];
        $option->save();
    }

    return response()->json($poll->load('options'), 201);
}

public function update(Request $request, int $id)
{
    $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();
    if (!$poll) {
        return response()->json(['message' => 'Poll not found.'], 404);
    }

    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'question' => 'required|string|max:500',
        'is_draft' => 'boolean',
        'allow_multiple_choices' => 'boolean',
        'results_public' => 'boolean',
        'duration' => 'nullable|integer|min:1',
        'options' => 'required|array|min:2',
        'options.*.label' => 'required|string|max:255',
    ]);

    $poll->title = $validated['title'] ?? null;
    $poll->question = $validated['question'];
    $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
    $poll->results_public = $validated['results_public'] ?? false;
    $poll->duration = $validated['duration'] ?? null;

    if ($poll->is_draft && isset($validated['is_draft']) && !$validated['is_draft']) {
        $poll->is_draft = false;
        $poll->started_at = now();
        if ($poll->duration) {
            $poll->ends_at = now()->addSeconds($poll->duration);
        }
    }

    $poll->save();

    $poll->options()->delete();
    foreach ($validated['options'] as $opt) {
        $option = new \App\Models\PollOption();
        $option->poll_id = $poll->id;
        $option->label = $opt['label'];
        $option->save();
    }

    return response()->json($poll->load('options'));
}

public function vote(Request $request, string $token)
{
    $poll = Poll::with('options')->where('secret_token', $token)->first();
    if (!$poll || $poll->is_draft) {
        return response()->json(['message' => 'Poll not found or not active.'], 404);
    }
    if ($poll->ends_at && now()->isAfter($poll->ends_at)) {
        return response()->json(['message' => 'Poll is closed.'], 403);
    }

    $validated = $request->validate([
        'option_ids' => 'required|array|min:1',
        'option_ids.*' => 'integer|exists:poll_options,id',
    ]);

    if (!$poll->allow_multiple_choices && count($validated['option_ids']) > 1) {
        return response()->json(['message' => 'Only one choice allowed.'], 422);
    }

    $validOptionIds = $poll->options->pluck('id')->toArray();
    foreach ($validated['option_ids'] as $optId) {
        if (!in_array($optId, $validOptionIds)) {
            return response()->json(['message' => 'Invalid option.'], 422);
        }
    }

    // Unicité : supprimer les votes existants si choix unique
    \App\Models\PollVote::where('poll_id', $poll->id)
        ->where('user_id', $request->user()->id)
        ->delete();

    foreach ($validated['option_ids'] as $optId) {
        $vote = new \App\Models\PollVote();
        $vote->poll_id = $poll->id;
        $vote->user_id = $request->user()->id;
        $vote->poll_option_id = $optId;
        $vote->save();
    }

    return response()->json(['message' => 'Vote recorded.'], 201);
}

public function results(string $token)
{
    $poll = Poll::with(['options' => function ($query) {
        $query->withCount('votes');
    }])->where('secret_token', $token)->first();

    if (!$poll) {
        return response()->json(['message' => 'Poll not found.'], 404);
    }

    return response()->json([
        'poll' => $poll,
        'total_votes' => $poll->votes()->count(),
    ]);
}
}
