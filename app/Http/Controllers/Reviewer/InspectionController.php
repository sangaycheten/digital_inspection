<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Client;
use App\Models\InspectionRecord;
use App\Models\MasterLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InspectionController extends Controller
{
    private function scopeToManager(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $clientIds = Client::where('manager_id', Auth::id())->pluck('id');

        if ($clientIds->isEmpty()) {
            return $query;
        }

        return $query->whereHas('asset.site', fn ($q) => $q->whereIn('client_id', $clientIds));
    }

    public function index(Request $request): View
    {
        $records = $this->scopeToManager(
            InspectionRecord::with(['asset.site', 'asset.building', 'technician'])
        )
            ->when($request->status ?? 'draft', fn ($q, $s) => $q->where('document_status', $s))
            ->when($request->technician_id, fn ($q) => $q->where('technician_id', $request->technician_id))
            ->when($request->result, fn ($q) => $q->where('result', $request->result))
            ->latest('inspection_date')
            ->paginate(25)
            ->withQueryString();

        $technicians = \App\Models\User::role('field-technician')->orderBy('name')->get();

        $pendingCount  = $this->scopeToManager(InspectionRecord::query())
            ->where('document_status', 'draft')->count();

        $approvedToday = $this->scopeToManager(InspectionRecord::query())
            ->where('document_status', 'approved')
            ->whereDate('created_at', today())
            ->count();

        return view('reviewer.inspections.index', compact('records', 'technicians', 'pendingCount', 'approvedToday'));
    }

    public function show(InspectionRecord $inspection): View
    {
        $inspection->load([
            'asset.site', 'asset.building', 'technician',
            'previousInspection.technician',
            'answers.questionnaire',
        ]);
        $assetTypes = MasterLookup::assetTypeMap();

        return view('reviewer.inspections.show', compact('inspection', 'assetTypes'));
    }

    public function approve(InspectionRecord $inspection): RedirectResponse
    {
        abort_if($inspection->document_status === 'approved', 422, 'Already approved.');

        DB::transaction(function () use ($inspection) {
            InspectionRecord::where('asset_id', $inspection->asset_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $inspection->update([
                'document_status' => 'approved',
                'is_current'      => true,
            ]);

            Asset::where('id', $inspection->asset_id)->update([
                'current_status'        => $inspection->result,
                'current_inspection_id' => $inspection->id,
            ]);
        });

        return back()->with('success', "Inspection for asset {$inspection->asset->asset_code} approved.");
    }

    public function reject(Request $request, InspectionRecord $inspection): RedirectResponse
    {
        abort_if($inspection->document_status === 'approved', 422, 'Cannot reject an approved record.');

        $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ]);

        $inspection->update([
            'required_action' => '[REJECTED] ' . $request->rejection_note,
        ]);

        return back()->with('warning', "Inspection for asset {$inspection->asset->asset_code} sent back for revision.");
    }
}
