<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Building;
use App\Models\InspectionAnswer;
use App\Models\InspectionRecord;
use App\Models\InstallationAsset;
use App\Models\Job;
use App\Models\JobTargetAsset;
use App\Models\MasterLookup;
use App\Models\Questionnaire;
use App\Notifications\InspectionResubmittedNotification;
use App\Notifications\InspectionSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CaptureController extends Controller
{
    private function buildingsClaimedByOthers(Job $job, \Illuminate\Support\Collection $buildingIds): \Illuminate\Support\Collection
    {
        if ($buildingIds->isEmpty()) return collect();

        return DB::table('inspection_records')
            ->join('assets', 'assets.id', '=', 'inspection_records.asset_id')
            ->where('inspection_records.job_id', $job->id)
            ->where('inspection_records.technician_id', '!=', Auth::id())
            ->whereNotNull('inspection_records.technician_id')
            ->whereIn('assets.building_id', $buildingIds)
            ->pluck('assets.building_id')
            ->unique();
    }

    private function authorizeJob(Job $job): void
    {
        $job->loadMissing('technicians');
        abort_if(!$job->technicians->contains('id', Auth::id()), 403, 'You are not assigned to this job.');
        abort_if($job->isClosed(), 403, 'This job is closed.');
        abort_if(
            $job->scheduled_date && today()->lt($job->scheduled_date),
            403,
            'Inspection cannot start before the scheduled date (' . $job->scheduled_date->format('d M Y') . ').'
        );
    }

    // ─── Inspection ────────────────────────────────────────────────────────────

    public function inspectForm(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load(['site', 'client', 'buildings', 'targetAssets']);

        $buildingIds      = $job->assignedBuildingIdsForTechnician(Auth::id());
        $claimedByOthers  = $this->buildingsClaimedByOthers($job, $buildingIds);
        $availableIds     = $buildingIds->diff($claimedByOthers);
        $lockedBuildings  = Building::whereIn('id', $claimedByOthers)->orderBy('name_or_level')->get();

        $assets = Asset::where('site_id', $job->site_id)
            ->when($buildingIds->isNotEmpty(), function ($q) use ($availableIds) {
                $availableIds->isNotEmpty()
                    ? $q->whereIn('building_id', $availableIds)
                    : $q->whereRaw('0 = 1');
            })
            ->whereNotIn('current_status', ['removed', 'replaced'])
            ->with(['building', 'currentInspection'])
            ->orderBy('building_id')
            ->orderBy('asset_code')
            ->get();

        $targetAssetIds = $job->targetAssets->pluck('asset_id');
        $grouped        = $assets->groupBy(fn ($a) => $a->building?->name_or_level ?? 'Unassigned');
        // Non-draft (submitted/approved) records — locked, shown read-only.
        $lockedRecords = InspectionRecord::where('job_id', $job->id)
            ->whereIn('document_status', ['submitted', 'approved'])
            ->with('answers')
            ->get()
            ->keyBy('asset_id');
        $doneAssetStatuses = $lockedRecords->mapWithKeys(fn ($r, $id) => [$id => $r->document_status]);
        $doneAssetIds      = $lockedRecords->keys();
        $assetTypes        = MasterLookup::assetTypeMap();

        // Draft records — editable, pre-fill the inspect forms.
        $existingRecords = InspectionRecord::where('job_id', $job->id)
            ->where('document_status', 'draft')
            ->with('answers')
            ->get()
            ->keyBy('asset_id');

        // Questions grouped by asset_type for the checklist
        $usedAssetTypes = $assets->pluck('asset_type')->unique()->filter()->values();
        $questionsByType = Questionnaire::whereIn('asset_type', $usedAssetTypes)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where('enabled', true)
            ->with(['subQuestionnaires' => fn ($q) => $q->where('status', 'active')->where('enabled', true)->orderBy('sort_order')->orderBy('created_at'), 'fieldType'])
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->groupBy('asset_type');

        return view('technician.capture.inspect', compact('job', 'grouped', 'targetAssetIds', 'doneAssetIds', 'doneAssetStatuses', 'lockedRecords', 'existingRecords', 'assetTypes', 'questionsByType', 'lockedBuildings'));
    }

    public function inspectStore(Request $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $data = $request->validate([
            'inspection_date'                  => ['required', 'date'],
            'assets'                           => ['nullable', 'array'],
            'assets.*.result'                  => ['nullable', 'in:' . implode(',', InspectionRecord::RESULTS)],
            'assets.*.condition'               => ['nullable', 'string', 'max:1000'],
            'assets.*.defect_description'      => ['nullable', 'string', 'max:1000'],
            'assets.*.reason_for_result'       => ['nullable', 'string', 'max:1000'],
            'assets.*.recommendation'          => ['nullable', 'string', 'max:1000'],
            'assets.*.required_action'         => ['nullable', 'string', 'max:1000'],
            'photos'                           => ['nullable', 'array'],
            'photos.*'                         => ['nullable', 'image', 'max:5120'],
            'answers'                          => ['nullable', 'array'],
            'answers.*'                        => ['nullable', 'array'],
            'answers.*.*'                      => ['nullable', 'string', 'max:2000'],
        ]);

        // Store uploaded photos before the transaction so file I/O is outside the DB lock
        $uploadedPhotos = [];
        foreach ($request->file('photos', []) as $assetId => $file) {
            $uploadedPhotos[$assetId] = $file->store('inspection-photos', 'public');
        }

        $isSavingDraft  = $request->boolean('save_as_draft');
        $isResubmission = $job->status === 'rectification_required';

        // Determine which buildings this technician may write to
        $assignedBuildingIds = $job->assignedBuildingIdsForTechnician(Auth::id());
        $claimedByOthers     = $this->buildingsClaimedByOthers($job, $assignedBuildingIds);
        $availableIds        = $assignedBuildingIds->diff($claimedByOthers);

        // Pre-load building_id for each submitted asset so we can gate below
        $assetBuildingMap = $assignedBuildingIds->isNotEmpty()
            ? Asset::whereIn('id', array_keys($data['assets'] ?? []))->pluck('building_id', 'id')
            : collect();

        $records = collect($data['assets'] ?? [])
            ->filter(fn ($rec) => !empty($rec['result']))
            ->filter(function ($_rec, $assetId) use ($assignedBuildingIds, $availableIds, $assetBuildingMap) {
                if ($assignedBuildingIds->isEmpty()) return true; // no building restriction
                $bldId = $assetBuildingMap->get($assetId);
                return $bldId === null || $availableIds->contains($bldId);
            });

        if ($records->isEmpty()) {
            // If there are already submitted/approved records, nothing left to do
            $alreadyDone = InspectionRecord::where('job_id', $job->id)
                ->whereIn('document_status', ['submitted', 'approved'])
                ->exists();

            if ($alreadyDone) {
                return redirect()->route('technician.jobs.show', $job)
                    ->with('success', 'All inspection records have already been submitted.');
            }

            if (!$isSavingDraft) {
                return back()->withErrors(['assets' => 'Please record at least one inspection result.']);
            }
        }

        $saved           = 0;
        $submittedAssetIds = [];

        $allAnswers = $data['answers'] ?? [];

        $docStatus = $isSavingDraft ? 'draft' : 'submitted';

        DB::transaction(function () use ($data, $job, $records, $allAnswers, $docStatus, $uploadedPhotos, &$saved, &$submittedAssetIds) {
            foreach ($records as $assetId => $rec) {
                // Only draft records are editable; submitted/approved records are locked
                $existing = InspectionRecord::where('job_id', $job->id)
                    ->where('asset_id', $assetId)
                    ->where('document_status', 'draft')
                    ->first();

                $newPhoto = $uploadedPhotos[$assetId] ?? null;

                if ($existing) {
                    $existing->answers()->delete();
                    $existing->update([
                        'inspection_date'    => $data['inspection_date'],
                        'result'             => $rec['result'],
                        'condition'          => $rec['condition']          ?? null,
                        'defect_description' => $rec['defect_description'] ?? null,
                        'reason_for_result'  => $rec['reason_for_result']  ?? null,
                        'recommendation'     => $rec['recommendation']     ?? null,
                        'required_action'    => $rec['required_action']    ?? null,
                        'photo_path'         => $newPhoto ?? $existing->photo_path,
                        'document_status'    => $docStatus,
                    ]);
                    $inspectionRecord = $existing;
                } else {
                    $previous = InspectionRecord::where('asset_id', $assetId)
                        ->orderByDesc('inspection_date')
                        ->first();

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
                        'photo_path'             => $newPhoto,
                        'document_status'        => $docStatus,
                        'is_current'             => false,
                        'previous_inspection_id' => $previous?->id,
                    ]);
                }

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

                if ($docStatus === 'submitted') {
                    $submittedAssetIds[] = $assetId;
                }
                $saved++;
            }

            if (in_array($job->status, ['new', 'scheduled']) && $saved > 0) {
                $job->update(['status' => 'in_progress']);
            }
        });

        if ($isSavingDraft) {
            return redirect()->route('technician.jobs.inspect', $job)
                ->with('draft_saved', true);
        }

        // Auto-advance job to submitted_for_review once all job assets are captured
        if ($job->status === 'in_progress') {
            $buildingIds   = $job->buildings()->pluck('buildings.id');
            $totalAssets   = \App\Models\Asset::where('site_id', $job->site_id)
                ->when($buildingIds->isNotEmpty(), fn ($q) => $q->whereIn('building_id', $buildingIds))
                ->whereNotIn('current_status', ['removed', 'replaced'])
                ->count();
            $capturedCount = InspectionRecord::where('job_id', $job->id)
                ->whereIn('document_status', ['submitted', 'approved'])
                ->distinct('asset_id')
                ->count('asset_id');

            if ($totalAssets > 0 && $capturedCount >= $totalAssets) {
                $job->update(['status' => 'submitted_for_review']);
            }
        }

        // Notify the mapped manager (submission or resubmission after rejection)
        if (!empty($submittedAssetIds)) {
            $job->loadMissing(['client.manager', 'site']);
            $manager = $job->client?->manager;
            if ($manager) {
                $buildingNames = Building::whereIn('id',
                    Asset::whereIn('id', $submittedAssetIds)->pluck('building_id')->unique()->filter()
                )->pluck('name_or_level')->sort()->values()->all();

                if ($isResubmission) {
                    $manager->notify(new InspectionResubmittedNotification(
                        job: $job,
                        technician: Auth::user(),
                        recordCount: $saved,
                        buildingNames: $buildingNames,
                    ));
                } else {
                    $manager->notify(new InspectionSubmittedNotification(
                        job: $job,
                        technician: Auth::user(),
                        recordCount: $saved,
                        buildingNames: $buildingNames,
                    ));
                }
            }
        }

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', "{$saved} inspection record(s) saved successfully.");
    }

    // ─── Register & Inspect (combined) ────────────────────────────────────────

    public function registerInspectForm(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load(['site.client', 'client', 'buildings']);

        $buildingIds = $job->assignedBuildingIdsForTechnician(Auth::id());
        $buildings   = $buildingIds->isNotEmpty()
            ? Building::whereIn('id', $buildingIds)->orderBy('name_or_level')->get()
            : $job->site->buildings()->orderBy('name_or_level')->get();

        $clientCode = $job->site?->client?->custom_client_code ?? '';

        $assetTypes = MasterLookup::assetTypeMap();

        $questionsByType = Questionnaire::whereIn('asset_type', array_keys($assetTypes))
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->where('enabled', true)
            ->with(['subQuestionnaires' => fn ($q) => $q->where('status', 'active')->where('enabled', true)->orderBy('sort_order')->orderBy('created_at'), 'fieldType'])
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->groupBy('asset_type');

        $registeredThisJob = Asset::where('created_from_job_id', $job->id)
            ->with(['building', 'currentInspection'])
            ->orderBy('asset_type')
            ->orderBy('asset_code')
            ->get();

        return view('technician.capture.register-inspect', compact('job', 'buildings', 'clientCode', 'assetTypes', 'questionsByType', 'registeredThisJob'));
    }

    public function registerInspectStore(Request $request, Job $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $mode        = $request->input('mode', 'single');
        $resultsRule = implode(',', InspectionRecord::RESULTS);

        // Shared inspection + asset detail rules
        $sharedRules = [
            'asset_type'         => ['required', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'building_id'        => ['required', 'exists:buildings,id'],
            'zone'               => ['nullable', 'string', 'max:255'],
            'make'               => ['nullable', 'string', 'max:100'],
            'model'              => ['nullable', 'string', 'max:100'],
            'serial_or_batch'    => ['nullable', 'string', 'max:100'],
            'rating'             => ['nullable', 'string', 'max:100'],
            'install_date'       => ['nullable', 'date'],
            'inspection_date'    => ['required', 'date'],
            'result'             => ['required', "in:{$resultsRule}"],
            'condition'          => ['nullable', 'string', 'max:1000'],
            'defect_description' => ['nullable', 'string', 'max:1000'],
            'reason_for_result'  => ['nullable', 'string', 'max:1000'],
            'recommendation'     => ['nullable', 'string', 'max:1000'],
            'required_action'    => ['nullable', 'string', 'max:1000'],
            'photo'              => ['nullable', 'image', 'max:5120'],
            'answers'            => ['nullable', 'array'],
            'answers.*'          => ['nullable', 'string', 'max:2000'],
        ];

        $job->load(['site.client']);
        $clientCode   = $job->site?->client?->custom_client_code ?? '';
        $buildingCode = $request->filled('building_id')
            ? (Building::find($request->input('building_id'))?->building_code ?? '')
            : '';
        $locParts  = array_filter([$clientCode, $buildingCode]);
        $autoPrefix = ($locParts ? implode('-', $locParts) . '-' : '') . ($request->input('asset_type') ?? '');

        if ($mode === 'range') {
            $data = $request->validate(array_merge($sharedRules, [
                'range_start' => ['required', 'numeric', 'min:0'],
                'range_end'   => ['required', 'numeric', 'gte:range_start'],
                'quantity'    => ['required', 'integer', 'min:1', 'max:200'],
            ]));

            $startRaw   = $request->input('range_start');
            $endRaw     = $request->input('range_end');
            $padLen     = strlen($endRaw);
            $assetCodes = [];
            for ($i = (int)$startRaw; $i <= (int)$endRaw; $i++) {
                $assetCodes[] = $autoPrefix . str_pad($i, $padLen, '0', STR_PAD_LEFT);
            }
        } else {
            $data = $request->validate(array_merge($sharedRules, [
                'asset_code' => ['required', 'string', 'max:100'],
            ]));
            $assetCodes = [$autoPrefix . $data['asset_code']];
        }

        // Reject if any of the generated codes already exist at this site
        $existing = Asset::where('site_id', $job->site_id)
            ->whereIn('asset_code', $assetCodes)
            ->pluck('asset_code')
            ->toArray();

        if (!empty($existing)) {
            $list = implode(', ', $existing);
            return back()->withInput()->withErrors([
                'asset_code' => count($existing) === 1
                    ? "Asset code {$list} already exists at this site."
                    : "The following asset codes already exist at this site: {$list}.",
            ]);
        }

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('inspection-photos', 'public')
            : null;

        $saved = 0;

        DB::transaction(function () use ($data, $job, $assetCodes, $photoPath, &$saved) {
            foreach ($assetCodes as $code) {
                $asset = Asset::create([
                    'site_id'             => $job->site_id,
                    'building_id'         => $data['building_id'] ?? null,
                    'zone'                => $data['zone'] ?? null,
                    'asset_code'          => $code,
                    'asset_type'          => $data['asset_type'],
                    'make'                => $data['make'] ?? null,
                    'model'               => $data['model'] ?? null,
                    'serial_or_batch'     => $data['serial_or_batch'] ?? null,
                    'rating'              => $data['rating'] ?? null,
                    'current_status'      => 'not_inspected',
                    'install_date'        => $data['install_date'] ?? null,
                    'created_from_job_id' => $job->id,
                ]);

                $record = InspectionRecord::create([
                    'asset_id'           => $asset->id,
                    'job_id'             => $job->id,
                    'inspection_date'    => $data['inspection_date'],
                    'technician_id'      => Auth::id(),
                    'result'             => $data['result'],
                    'condition'          => $data['condition'] ?? null,
                    'defect_description' => $data['defect_description'] ?? null,
                    'reason_for_result'  => $data['reason_for_result'] ?? null,
                    'recommendation'     => $data['recommendation'] ?? null,
                    'required_action'    => $data['required_action'] ?? null,
                    'photo_path'         => $photoPath,
                    'document_status'    => 'submitted',
                    'is_current'         => false,
                    'previous_inspection_id' => null,
                ]);

                foreach ($data['answers'] ?? [] as $questionnaireId => $answerValue) {
                    if ($answerValue === null || $answerValue === '') continue;
                    InspectionAnswer::create([
                        'inspection_record_id' => $record->id,
                        'questionnaire_id'     => $questionnaireId,
                        'answer_value'         => $answerValue,
                        'created_at'           => now(),
                    ]);
                }

                InstallationAsset::create([
                    'job_id'   => $job->id,
                    'asset_id' => $asset->id,
                    'action'   => 'installed',
                ]);

                $saved++;
            }

            if (in_array($job->status, ['new', 'scheduled']) && $saved > 0) {
                $job->update(['status' => 'in_progress']);
            }
        });

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', "{$saved} asset(s) registered and inspected successfully.");
    }

    public function checkAssetCodes(Request $request, Job $job): JsonResponse
    {
        abort_if(!$job->technicians()->where('users.id', Auth::id())->exists(), 403);

        $codes = array_slice((array) $request->input('codes', []), 0, 200);
        if (empty($codes)) {
            return response()->json(['existing' => []]);
        }

        $existing = Asset::where('site_id', $job->site_id)
            ->whereIn('asset_code', $codes)
            ->pluck('asset_code')
            ->toArray();

        return response()->json(['existing' => $existing]);
    }

    // ─── Installation / Rectification ──────────────────────────────────────────

    public function installForm(Job $job): View
    {
        $this->authorizeJob($job);

        $job->load(['site', 'client', 'buildings']);

        $buildingIds = $job->assignedBuildingIdsForTechnician(Auth::id());

        $existingAssets = Asset::where('site_id', $job->site_id)
            ->when($buildingIds->isNotEmpty(), fn ($q) => $q->whereIn('building_id', $buildingIds))
            ->whereNotIn('current_status', ['removed', 'replaced'])
            ->orderBy('asset_code')
            ->get();

        $buildings = $buildingIds->isNotEmpty()
            ? Building::whereIn('id', $buildingIds)->orderBy('name_or_level')->get()
            : $job->site->buildings()->orderBy('name_or_level')->get();

        $assetTypes = MasterLookup::assetTypeMap();

        // Already saved installation records for this job
        $registeredEntries = \App\Models\InstallationAsset::with(['asset.building'])
            ->where('job_id', $job->id)
            ->latest()
            ->get();

        return view('technician.capture.install', compact('job', 'existingAssets', 'buildings', 'assetTypes', 'registeredEntries'));
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
