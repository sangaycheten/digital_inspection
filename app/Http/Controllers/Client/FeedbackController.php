<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientFeedback;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $feedbacks = ClientFeedback::where('user_id', $user->id)
            ->with('job.site')
            ->latest()
            ->get();

        $completedJobs = Job::whereIn('site_id', $user->sites->pluck('id'))
            ->where('client_id', $user->client_id)
            ->whereIn('status', ['issued', 'closed'])
            ->whereDoesntHave('feedbacks', fn ($q) => $q->where('user_id', $user->id))
            ->with('site')
            ->latest()
            ->get();

        return view('client.feedback.index', compact('feedbacks', 'completedJobs'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $data = $request->validate([
            'job_id'          => ['required', 'exists:work_orders,id'],
            'overall_rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'service_quality' => ['nullable', 'integer', 'min:1', 'max:5'],
            'communication'   => ['nullable', 'integer', 'min:1', 'max:5'],
            'timeliness'      => ['nullable', 'integer', 'min:1', 'max:5'],
            'professionalism' => ['nullable', 'integer', 'min:1', 'max:5'],
            'message'         => ['nullable', 'string', 'max:1000'],
        ]);

        // Prevent duplicate feedback per job per user
        $exists = ClientFeedback::where('user_id', $user->id)
            ->where('job_id', $data['job_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['job_id' => 'You have already submitted feedback for this job.']);
        }

        // Verify job belongs to the client's accessible sites
        $job = Job::whereIn('site_id', $user->sites->pluck('id'))
            ->where('client_id', $user->client_id)
            ->find($data['job_id']);

        if (!$job) {
            abort(403);
        }

        ClientFeedback::create([
            ...$data,
            'user_id'   => $user->id,
            'client_id' => $user->client_id,
        ]);

        return redirect()->route('client.feedback.index')
            ->with('success', 'Thank you for your feedback!');
    }
}
