<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DataType;
use App\Http\Controllers\Controller;
use App\Models\FieldType;
use App\Models\MasterLookup;
use App\Models\Questionnaire;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function create(): View
    {
        $typeOptions     = DataType::valueLabelMap();
        $fieldTypesForJs = FieldType::where('status', 'active')->orderBy('name')->get()
            ->map(fn($ft) => ['id' => $ft->id, 'name' => $ft->name, 'type' => $ft->type, 'options' => $ft->options ?? []])
            ->values()->all();
        $sections   = Section::where('status', 'active')->orderBy('name')->get(['id', 'name', 'asset_type']);
        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.questionnaires.create', compact(
            'typeOptions', 'fieldTypesForJs', 'sections', 'assetTypes'
        ));
    }

    public function edit(Questionnaire $questionnaire): View
    {
        $typeOptions     = DataType::valueLabelMap();
        $fieldTypesForJs = FieldType::where('status', 'active')->orderBy('name')->get()
            ->map(fn($ft) => ['id' => $ft->id, 'name' => $ft->name, 'type' => $ft->type, 'options' => $ft->options ?? []])
            ->values()->all();
        $parentQuestionnaires = Questionnaire::whereNull('deleted_at')
            ->where('type', '!=', DataType::SubQuestionnaire->value)
            ->where('status', 'active')->where('id', '!=', $questionnaire->id)
            ->orderBy('name')->get(['id', 'name', 'key', 'type']);
        $sections          = Section::where('status', 'active')->orderBy('name')->get(['id', 'name', 'asset_type']);
        $assetTypes        = MasterLookup::assetTypeMap();
        $subQuestionnaires = $questionnaire->subQuestionnaires()->orderBy('created_at')->get();
        $subsForJs         = $subQuestionnaires->map(fn($q) => [
            'id'            => $q->id,
            'name'          => $q->name,
            'key'           => $q->key,
            'type'          => $q->type,
            'field_type_id' => $q->field_type_id ?? '',
            'section_id'    => $q->section_id    ?? '',
            'condition'     => $q->condition     ?? '',
            'enabled'       => $q->enabled  ? '1' : '0',
            'required'      => $q->required ? '1' : '0',
            'status'        => $q->status,
        ])->values()->all();

        return view('admin.questionnaires.edit', compact(
            'questionnaire', 'subQuestionnaires', 'subsForJs',
            'typeOptions', 'fieldTypesForJs', 'parentQuestionnaires', 'sections', 'assetTypes'
        ));
    }

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $baseQuery = Questionnaire::whereNull('parent_id')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%"))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->type,       fn($q) => $q->where('type',       $request->type))
            ->when($request->status,     fn($q) => $q->where('status',     $request->status));

        // Per-tab counts grouped by asset_type (respects all filters except asset_type)
        $tabCounts = (clone $baseQuery)
            ->selectRaw('asset_type, count(*) as c')
            ->groupBy('asset_type')
            ->pluck('c', 'asset_type');

        $questionnaires = (clone $baseQuery)
            ->with(['subQuestionnaires'])
            ->when($tab !== 'all', fn($q) => $q->where('asset_type', $tab))
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        $typeOptions = DataType::valueLabelMap();
        $sections    = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $assetTypes  = MasterLookup::assetTypeMap();

        return view('admin.questionnaires.index', compact(
            'questionnaires', 'typeOptions', 'sections', 'assetTypes', 'tab', 'tabCounts'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $types          = $request->input('type', []);
        $groupSeqs      = $request->input('group_seq', []);
        $isGroupParents = $request->input('is_group_parent', []);

        // Pre-compute group parent type per group_seq so we can validate condition for children
        $groupParentTypes = [];
        foreach ($types as $i => $type) {
            $seq      = $groupSeqs[$i] ?? '';
            $isParent = ($isGroupParents[$i] ?? '0') === '1';
            if ($seq !== '' && $isParent) {
                $groupParentTypes[$seq] = $type;
            }
        }

        $perRowRules    = [];
        $customMessages = [];

        foreach ($types as $i => $type) {
            $num      = $i + 1;
            $seq      = $groupSeqs[$i] ?? '';
            $isParent = ($isGroupParents[$i] ?? '0') === '1';
            $needsFt  = in_array($type, [DataType::Toggle->value, DataType::ThreeTierSwitch->value, DataType::OptionList->value]);

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];

            // condition required for children whose group parent is a switch or three-tier switch
            if (!$isParent && $seq !== '') {
                $parentType     = $groupParentTypes[$seq] ?? null;
                $parentIsSwitch = in_array($parentType, [DataType::Toggle->value, DataType::ThreeTierSwitch->value]);
                $perRowRules["condition.$i"] = $parentIsSwitch
                    ? ['required', 'in:yes,no,opt1,opt2,opt3']
                    : ['nullable', 'in:yes,no,opt1,opt2,opt3'];
                if ($parentIsSwitch) {
                    $customMessages["condition.{$i}.required"] = "Sub-question #{$num}: Condition is required.";
                }
            } else {
                $perRowRules["condition.$i"] = ['nullable'];
            }

            $customMessages["name.{$i}.required"]          = "Question #{$num}: Name is required.";
            $customMessages["key.{$i}.required"]           = "Question #{$num}: Key is required.";
            $customMessages["key.{$i}.alpha_dash"]         = "Question #{$num}: Key may only contain letters, numbers, dashes, and underscores.";
            $customMessages["key.{$i}.max"]                = "Question #{$num}: Key must not exceed 100 characters.";
            $customMessages["type.{$i}.required"]          = "Question #{$num}: Please select a data type.";
            $customMessages["status.{$i}.required"]        = "Question #{$num}: Status is required.";
            $customMessages["field_type_id.{$i}.required"] = "Question #{$num}: Option set is required for this data type.";
        }

        $validated = $request->validate(array_merge([
            'name'            => ['required', 'array', 'min:1'],
            'name.*'          => ['required', 'string', 'max:255'],
            'key'             => ['required', 'array', 'min:1'],
            'key.*'           => ['required', 'string', 'max:100', 'alpha_dash'],
            'type'            => ['required', 'array', 'min:1'],
            'type.*'          => ['required', new Enum(DataType::class)],
            'field_type_id'   => ['nullable', 'array'],
            'condition'       => ['nullable', 'array'],
            'condition.*'     => ['nullable', 'in:yes,no,opt1,opt2,opt3'],
            'asset_type'      => ['required', 'string', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'section_id'      => ['required', 'array'],
            'section_id.*'    => ['required', 'uuid', 'exists:sections,id'],
            'enabled'         => ['nullable', 'array'],
            'enabled.*'       => ['nullable', 'in:0,1'],
            'required'        => ['nullable', 'array'],
            'required.*'      => ['nullable', 'in:0,1'],
            'status'          => ['required', 'array', 'min:1'],
            'status.*'        => ['required', 'in:active,inactive'],
            'is_group_parent' => ['nullable', 'array'],
            'group_seq'       => ['nullable', 'array'],
        ], $perRowRules), $customMessages);

        $count    = count($validated['name']);
        $usedKeys = Questionnaire::pluck('key')->flip()->toArray();

        // Pass 1 — create standalone questions and group parents; track IDs by group_seq.
        $groupParentIds = [];
        for ($i = 0; $i < $count; $i++) {
            $seq      = $validated['group_seq'][$i] ?? '';
            $isParent = ($validated['is_group_parent'][$i] ?? '0') === '1';
            if ($seq !== '' && !$isParent) continue; // children handled in pass 2

            $q = Questionnaire::create([
                'name'          => $validated['name'][$i],
                'key'           => $this->makeUniqueKey(strtolower($validated['key'][$i]), $usedKeys),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
                'asset_type'    => $validated['asset_type'] ?? null,
                'section_id'    => $validated['section_id'][$i] ?? null,
                'enabled'       => ($validated['enabled'][$i] ?? '0') === '1',
                'required'      => ($validated['required'][$i] ?? '0') === '1',
                'status'        => $validated['status'][$i],
            ]);

            activity()->useLog('master')->causedBy($request->user())
                ->performedOn($q)->event('created')
                ->log("Questionnaire created: {$q->name}");

            if ($seq !== '') $groupParentIds[$seq] = $q->id;
        }

        // Pass 2 — create children, linking to their parent.
        for ($i = 0; $i < $count; $i++) {
            $seq      = $validated['group_seq'][$i] ?? '';
            $isParent = ($validated['is_group_parent'][$i] ?? '0') === '1';
            if ($seq === '' || $isParent) continue;

            $parentId       = $groupParentIds[$seq] ?? null;
            $parentIsSwitch = in_array($groupParentTypes[$seq] ?? null, [DataType::Toggle->value, DataType::ThreeTierSwitch->value]);

            $q = Questionnaire::create([
                'name'          => $validated['name'][$i],
                'key'           => $this->makeUniqueKey(strtolower($validated['key'][$i]), $usedKeys),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
                'asset_type'    => $validated['asset_type'] ?? null,
                'section_id'    => $validated['section_id'][$i] ?? null,
                'condition'     => $parentIsSwitch ? ($validated['condition'][$i] ?? null) : null,
                'parent_id'     => $parentId,
                'enabled'       => ($validated['enabled'][$i] ?? '0') === '1',
                'required'      => ($validated['required'][$i] ?? '0') === '1',
                'status'        => $validated['status'][$i],
            ]);

            activity()->useLog('master')->causedBy($request->user())
                ->performedOn($q)->event('created')
                ->log("Questionnaire created: {$q->name}");
        }

        $msg = $count === 1
            ? "Questionnaire \"{$validated['name'][0]}\" created successfully."
            : "{$count} questionnaires created successfully.";

        return redirect()->route('admin.questionnaires.index')->with('success', $msg);
    }

    public function update(Request $request, Questionnaire $questionnaire): RedirectResponse
    {
        $needsFieldType = in_array($request->type, [DataType::Toggle->value, DataType::ThreeTierSwitch->value, DataType::OptionList->value]);
        $isSubQ         = $request->type === DataType::SubQuestionnaire->value;
        $parentId       = $request->input('parent_id');
        $parentType     = $isSubQ && $parentId ? Questionnaire::where('id', $parentId)->value('type') : null;
        $parentIsSwitch = in_array($parentType, [DataType::Toggle->value, DataType::ThreeTierSwitch->value]);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'key'           => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('questionnaires', 'key')->ignore($questionnaire->id)],
            'type'          => ['required', new Enum(DataType::class)],
            'field_type_id' => $needsFieldType ? ['required', 'uuid', 'exists:field_types,id'] : ['nullable'],
            'asset_type'    => ['required', 'string', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'section_id'    => ['required', 'uuid', 'exists:sections,id'],
            'parent_id'     => $isSubQ
                                ? ['required', 'uuid', 'exists:questionnaires,id', Rule::notIn([$questionnaire->id])]
                                : ['nullable'],
            'condition'     => $parentIsSwitch ? ['required', 'in:yes,no,opt1,opt2,opt3'] : ['nullable', 'in:yes,no,opt1,opt2,opt3'],
            'status'        => ['required', 'in:active,inactive'],
        ]);

        $data['key']       = strtolower($data['key']);
        $data['enabled']   = $request->boolean('enabled');
        $data['required']  = $request->boolean('required');
        $data['parent_id'] = $isSubQ ? ($data['parent_id'] ?? null) : null;
        $data['condition'] = ($isSubQ && $parentIsSwitch) ? ($data['condition'] ?? null) : null;

        $questionnaire->update($data);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($questionnaire)->event('updated')
            ->log("Questionnaire updated: {$questionnaire->name}");

        return redirect()->route('admin.questionnaires.index')
            ->with('success', "Questionnaire \"{$questionnaire->name}\" updated successfully.");
    }

    public function updateSubGroup(Request $request, Questionnaire $parent): RedirectResponse
    {
        $types          = $request->input('type', []);
        $perRowRules    = [];
        $customMessages = [];
        $parentIsSwitch = in_array($parent->type, [DataType::Toggle->value, DataType::ThreeTierSwitch->value]);

        foreach ($types as $i => $type) {
            $num     = $i + 1;
            $needsFt = in_array($type, [DataType::Toggle->value, DataType::ThreeTierSwitch->value, DataType::OptionList->value]);

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];

            $perRowRules["condition.$i"] = $parentIsSwitch
                ? ['required', 'in:yes,no,opt1,opt2,opt3']
                : ['nullable', 'in:yes,no,opt1,opt2,opt3'];

            $customMessages["name.{$i}.required"]          = "Sub-question #{$num}: Name is required.";
            $customMessages["key.{$i}.required"]           = "Sub-question #{$num}: Key is required.";
            $customMessages["key.{$i}.distinct"]           = "Sub-question #{$num}: Duplicate key — each sub-question must have a unique key.";
            $customMessages["key.{$i}.alpha_dash"]         = "Sub-question #{$num}: Key may only contain letters, numbers, dashes, and underscores.";
            $customMessages["type.{$i}.required"]          = "Sub-question #{$num}: Please select a data type.";
            $customMessages["status.{$i}.required"]        = "Sub-question #{$num}: Status is required.";
            $customMessages["field_type_id.{$i}.required"] = "Sub-question #{$num}: Option set is required for this data type.";
            $customMessages["condition.{$i}.required"]     = "Sub-question #{$num}: Condition is required.";
        }

        $validated = $request->validate(array_merge([
            'asset_type'    => ['required', 'string', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'sub_id'        => ['nullable', 'array'],
            'sub_id.*'      => ['nullable', 'uuid'],
            'name'          => ['required', 'array', 'min:1'],
            'name.*'        => ['required', 'string', 'max:255'],
            'key'           => ['required', 'array', 'min:1'],
            'key.*'         => ['required', 'string', 'max:100', 'alpha_dash'],
            'type'          => ['required', 'array', 'min:1'],
            'type.*'        => ['required', new Enum(DataType::class)],
            'field_type_id' => ['nullable', 'array'],
            'condition'     => ['nullable', 'array'],
            'section_id'    => ['required', 'uuid', 'exists:sections,id'],
            'enabled'       => ['nullable', 'array'],
            'enabled.*'     => ['nullable', 'in:0,1'],
            'required'      => ['nullable', 'array'],
            'required.*'    => ['nullable', 'in:0,1'],
            'status'        => ['required', 'array', 'min:1'],
            'status.*'      => ['required', 'in:active,inactive'],
        ], $perRowRules), $customMessages);

        $parent->update(['asset_type' => $validated['asset_type'] ?? null]);

        $submittedIds = array_values(array_filter($validated['sub_id'] ?? [], fn($id) => !empty($id)));

        // Build a set of keys already in use, excluding this parent's own children
        // (so their current keys won't cause false conflicts when unchanged)
        $childIds = Questionnaire::where('parent_id', $parent->id)->pluck('id');
        $usedKeys = Questionnaire::whereNotIn('id', $childIds)->pluck('key')->flip()->toArray();

        $count = count($validated['name']);

        Questionnaire::where('parent_id', $parent->id)
            ->when(!empty($submittedIds), fn($q) => $q->whereNotIn('id', $submittedIds))
            ->delete();

        for ($i = 0; $i < $count; $i++) {
            $row = [
                'name'          => $validated['name'][$i],
                'key'           => $this->makeUniqueKey(strtolower($validated['key'][$i]), $usedKeys),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
                'section_id'    => $validated['section_id'] ?? null,
                'parent_id'     => $parent->id,
                'condition'     => $parentIsSwitch ? ($validated['condition'][$i] ?? null) : null,
                'enabled'       => ($validated['enabled'][$i] ?? '0') === '1',
                'required'      => ($validated['required'][$i] ?? '0') === '1',
                'status'        => $validated['status'][$i],
            ];

            $subId = $validated['sub_id'][$i] ?? null;
            if ($subId) {
                $q = Questionnaire::where('id', $subId)->where('parent_id', $parent->id)->first();
                if ($q) {
                    $q->update($row);
                    activity()->useLog('master')->causedBy($request->user())
                        ->performedOn($q)->event('updated')
                        ->log("Sub-questionnaire updated: {$q->name}");
                }
            } else {
                $q = Questionnaire::create($row);
                activity()->useLog('master')->causedBy($request->user())
                    ->performedOn($q)->event('created')
                    ->log("Sub-questionnaire created: {$q->name}");
            }
        }

        return redirect()->route('admin.questionnaires.index')
            ->with('success', "Sub-questionnaires for \"{$parent->name}\" updated successfully.");
    }

    public function moveUp(Questionnaire $questionnaire): RedirectResponse
    {
        $prev = Questionnaire::where('asset_type', $questionnaire->asset_type)
            ->whereNull('parent_id')
            ->where('sort_order', '<', $questionnaire->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($prev) {
            [$questionnaire->sort_order, $prev->sort_order] = [$prev->sort_order, $questionnaire->sort_order];
            $questionnaire->save();
            $prev->save();
        }

        return redirect()->route('admin.questionnaires.index', ['tab' => $questionnaire->asset_type]);
    }

    public function moveDown(Questionnaire $questionnaire): RedirectResponse
    {
        $next = Questionnaire::where('asset_type', $questionnaire->asset_type)
            ->whereNull('parent_id')
            ->where('sort_order', '>', $questionnaire->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            [$questionnaire->sort_order, $next->sort_order] = [$next->sort_order, $questionnaire->sort_order];
            $questionnaire->save();
            $next->save();
        }

        return redirect()->route('admin.questionnaires.index', ['tab' => $questionnaire->asset_type]);
    }

    public function destroy(Request $request, Questionnaire $questionnaire): RedirectResponse|JsonResponse
    {
        $name = $questionnaire->name;
        $questionnaire->delete();

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($questionnaire)->event('deleted')
            ->log("Questionnaire deleted: {$name}");

        if ($request->expectsJson()) {
            return response()->json(['message' => "Questionnaire \"{$name}\" deleted."]);
        }

        return redirect()->route('admin.questionnaires.index')
            ->with('success', "Questionnaire \"{$name}\" deleted.");
    }

    private function makeUniqueKey(string $base, array &$used): string
    {
        $key = $base;
        $n   = 1;
        while (isset($used[$key])) {
            $key = $base . '_' . $n++;
        }
        $used[$key] = true;
        return $key;
    }
}
