<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with('causer')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('log')) {
            $query->where('log_name', $request->log);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.audit-log.index', compact('logs'));
    }
}
