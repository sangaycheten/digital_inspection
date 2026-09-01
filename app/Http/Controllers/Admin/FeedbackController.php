<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFeedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $feedbacks = ClientFeedback::with(['user', 'client', 'job.site'])
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->rating,    fn ($q) => $q->where('overall_rating', $request->rating))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $clients = Client::orderBy('name')->get(['id', 'name']);

        $averages = ClientFeedback::selectRaw('
            round(avg(overall_rating), 1)  as overall,
            round(avg(service_quality), 1) as service_quality,
            round(avg(communication), 1)   as communication,
            round(avg(timeliness), 1)      as timeliness,
            round(avg(professionalism), 1) as professionalism,
            count(*)                       as total
        ')->first();

        $distribution = ClientFeedback::selectRaw('overall_rating, count(*) as cnt')
            ->groupBy('overall_rating')
            ->orderByDesc('overall_rating')
            ->pluck('cnt', 'overall_rating');

        return view('admin.feedback.index', compact('feedbacks', 'clients', 'averages', 'distribution'));
    }
}
