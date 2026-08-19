<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\InspectionAnswer;
use App\Models\InspectionRecord;
use App\Models\InstallationAsset;
use App\Models\Job;
use App\Models\JobTargetAsset;
use App\Models\MasterLookup;
use App\Models\Questionnaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CaptureController extends Controller
{
    private function authorizeJob(Job $job): void
    {
        $job->loadMissing('technicians');
        abort_if(!$job->technicians->contains('id', Auth::id()), 403, 'You are not assigned to this job.');
        abort_if($job->isClosed(), 403, 'This job is closed.');
    }

    // ─── Inspection ────────────────────────────────────────────────────────────

    public function inspectForm(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load(['site', 'client', 'buildings', 'targetAssets']);

        $buildingIds = $job->buildings->pluck('id');

        $assets = Asset::where('site_id', $job->site_id)
            ->when($buildingIds->isNotEmpty(), fn ($q) => $q->whereIn('building_id', $buildingIds))
            ->whereNotIn('current_status', ['removed', 'replaced'])
            ->with(['building', 'currentInspection'])
            ->orderBy('building_id')
            ->orderBy('asset_code')
            ->get();

        $targetAssetIds = $job->targetAssets->pluck('asset_id');
        $grouped        = $assets->groupBy(fn ($a) => $a->building?->name_or_level ?? 'Unassigned');
        $doneAssetIds   = InspectionRecord::where('job_id', $job->id)->pluck('asset_id');
        $assetTypes     = MasterLookup::assetTypeMap();

        // Questions grouped by asset_type for the checklist
        $usedAssetTypes = $assets->pluck('asset_type')->unique()->filter()->values();
        $questionsByType = Questionnaire::whereIn('asset_type', $usedAssetTypes)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where('enabled', true)
            ->with(['subQuestionnaires' => fn ($q) => $q->where('status', 'active')->where('enabled', true)->orderBy('created_at'), 'fieldType'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('asset_type');

        return view('technician.capture.inspect', compact('job', 'grouped', 'targetAssetIds', 'doneAssetIds', 'assetTypes', 'questionsByType'));
    }

    public function inspectStore(Request $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $data = $request->validate([
            'inspection_date'                  => ['required', 'date'],
            'assets'                           => ['nullable', 'array'],
            'assets.*.result'                  => ['required_with:assets.*.asset_id', 'in:' . implode(',', InspectionRecord::RESULTS)],
            'assets.*.condition'               => ['nullable', 'string', 'max:1000'],
            'assets.*.defect_description'      => ['nullable', 'string', 'max:1000'],
            'assets.*.reason_for_result'       => ['nullable', 'string', 'max:1000'],
            'assets.*.recommendation'          => ['nullable', 'string', 'max:1000'],
            'assets.*.required_action'         => ['nullable', 'string', 'max:1000'],
            'answers'                          => ['nullable', 'array'],
            'answers.*'                        => ['nullable', 'array'],
            'answers.*.*'                      => ['nullable', 'string', 'max:2000'],
        ]);

        $records = collect($data['assets'] ?? [])
            ->filter(fn ($rec) => !empty($rec['result']));

        if ($records->isEmpty()) {
            return back()->withErrors(['assets' => 'Please record at least one inspection result.']);
        }

        $saved = 0;

        $allAnswers = $data['answers'] ?? [];

        DB::transaction(function () use ($data, $job, $records, $allAnswers, &$saved) {
            foreach ($records as $assetId => $rec) {
                $previous = InspectionRecord::where('asset_id', $assetId)
                    ->orderByDesc('inspection_date')
                    ->first();

                // Saved as draft — asset.current_status updated only on approval
                $inspectionRecord = InspectionRecord::create([
                    'asset_id'               => $assetId,
                    'job_id'                 => $job->id,
                    'inspection_date'        => $data['inspection_date'],
                    'technician_id'          => Auth::id(),
                    'result'                 => $rec['result'],
                    'condition'              => $rec['condition']          ?? null,
                    'defect_description'     => $rec['defect_description'] ?? null,
                    'reason_for_result'      => $rec['reason_for_result']  ?? null,
                    'recommendation'         => $rec['recommendation']     ?? null,
                    'required_action'        => $rec['required_action']    ?? null,
                    'document_status'        => 'draft',
                    'is_current'             => false,
                    'previous_inspection_id' => $previous?->id,
                ]);

                // Save checklist answers for this asset
                $assetAnswers = $allAnswers[$assetId] ?? [];
                foreach ($assetAnswers as $questionnaireId => $answerValue) {
                    if ($answerValue === null || $answerValue === '') continue;
                    InspectionAnswer::create([
                        'inspection_record_id' => $inspectionRecord->id,
                        'questionnaire_id'     => $questionnaireId,
                        'answer_value'         => $answerValue,
                        'created_at'           => now(),
                    ]);
                }

                JobTargetAsset::where('job_id', $job->id)
                    ->where('asset_id', $assetId)
                    ->update(['completed' => true, 'completed_at' => now()]);

                $saved++;
            }

            // Advance job to in_progress if still on scheduled
            if ($job->status === 'scheduled') {
                $job->update(['status' => 'in_progress']);
            }
        });

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', "{$saved} inspection record(s) saved successfully.");
    }

    // ─── Installation / Rectification ──────────────────────────────────────────

    public function installForm(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load(['site', 'client', 'buildings']);

        $buildingIds = $job->buildings->pluck('id');

        $existingAssets = Asset::where('site_id', $job->site_id)
            ->when($buildingIds->isNotEmpty(), fn ($q) => $q->whereIn('building_id', $buildingIds))
            ->whereNotIn('current_status', ['removed', 'replaced'])
            ->orderBy('asset_code')
            ->get();

        $buildings  = $job->buildings->isNotEmpty()
            ? $job->buildings
            : $job->site->buildings()->orderBy('name_or_level')->get();
        $assetTypes = MasterLookup::assetTypeMap();

        return view('technician.capture.install', compact('job', 'existingAssets', 'buildings', 'assetTypes'));
    }

    public function installStore(Request $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $data = $request->validate([
            'entries'                        => ['required', 'array', 'min:1'],
            'entries.*.mode'                 => ['required', 'in:existing,new'],

            // Existing asset fields
            'entries.*.asset_id'             => ['nullable', 'exists:assets,id'],
            'entries.*.action'               => ['required', 'in:' . implode(',', InstallationAsset::ACTIONS)],
            'entries.*.material_notes'       => ['nullable', 'string', 'max:1000'],

            // New asset fields
            'entries.*.asset_code'           => ['nullable', 'string', 'max:100'],
            'entries.*.asset_type'           => ['nullable', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'entries.*.building_id'          => ['nullable', 'exists:buildings,id'],
            'entries.*.zone'                 => ['nullable', 'string', 'max:255'],
            'entries.*.make'                 => ['nullable', 'string', 'max:100'],
            'entries.*.model'                => ['nullable', 'string', 'max:100'],
            'entries.*.serial_or_batch'      => ['nullable', 'string', 'max:100'],
            'entries.*.rating'               => ['nullable', 'string', 'max:100'],
            'entries.*.install_date'         => ['nullable', 'date'],
        ]);

        $saved = 0;

        DB::transaction(function () use ($data, $job, &$saved) {
            foreach ($data['entries'] as $entry) {
                if ($entry['mode'] === 'existing') {
                    if (empty($entry['asset_id'])) continue;

                    InstallationAsset::create([
                        'job_id'         => $job->id,
                        'asset_id'       => $entry['asset_id'],
                        'action'         => $entry['action'],
                        'material_notes' => $entry['material_notes'] ?? null,
                    ]);
                } else {
                    // Create new asset then link it
                    if (empty($entry['asset_code']) || empty($entry['asset_type'])) continue;

                    $asset = Asset::create([
                        'site_id'           => $job->site_id,
                        'building_id'       => $entry['building_id'] ?? null,
                        'zone'              => $entry['zone'] ?? null,
                        'asset_code'        => $entry['asset_code'],
                        'asset_type'        => $entry['asset_type'],
                        'make'              => $entry['make'] ?? null,
                        'model'             => $entry['model'] ?? null,
                        'serial_or_batch'   => $entry['serial_or_batch'] ?? null,
                        'rating'            => $entry['rating'] ?? null,
                        'current_status'    => 'not_inspected',
                        'install_date'      => $entry['install_date'] ?? null,
                        'created_from_job_id' => $job->id,
                    ]);

                    InstallationAsset::create([
                        'job_id'         => $job->id,
                        'asset_id'       => $asset->id,
                        'action'         => $entry['action'],
                        'material_notes' => $entry['material_notes'] ?? null,
                    ]);
                }

                $saved++;
            }

            if ($job->status === 'scheduled') {
                $job->update(['status' => 'in_progress']);
            }
        });

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', "{$saved} installation record(s) saved successfully.");
    }
}
