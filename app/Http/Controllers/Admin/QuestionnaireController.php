<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DataType;
use App\Http\Controllers\Controller;
use App\Models\FieldType;
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
        $sections = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.questionnaires.create', compact(
            'typeOptions', 'fieldTypesForJs', 'sections'
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
        $sections          = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);
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
            'typeOptions', 'fieldTypesForJs', 'parentQuestionnaires', 'sections'
        ));
    }

    public function index(Request $request): View
    {
        $questionnaires = Questionnaire::with(['subQuestionnaires'])
            ->whereNull('parent_id')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%"))
            ->when($request->type,       fn($q) => $q->where('type',       $request->type))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->status,     fn($q) => $q->where('status',     $request->status))
            ->latest()->paginate(15)->withQueryString();

        $typeOptions = DataType::valueLabelMap();
        $sections    = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.questionnaires.index', compact('questionnaires', 'typeOptions', 'sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $types          = $request->input('type', []);
        $groupSeqs      = $request->input('group_seq', []);
        $isGroupParents = $request->input('is_group_parent', []);

        $perRowRules    = [];
        $customMessages = [];

        foreach ($types as $i => $type) {
            $num     = $i + 1;
            $needsFt = in_array($type, [DataType::Toggle->value, DataType::OptionList->value]);

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];

            $customMessages["name.{$i}.required"]          = "Question #{$num}: Name is required.";
            $customMessages["key.{$i}.required"]           = "Question #{$num}: Key is required.";
            $customMessages["key.{$i}.unique"]             = "Question #{$num}: This key is already taken — please choose a different one.";
            $customMessages["key.{$i}.distinct"]           = "Question #{$num}: Duplicate key — each question must have a unique key.";
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
            'key.*'           => ['required', 'string', 'max:100', 'alpha_dash', 'distinct', 'unique:questionnaires,key'],
            'type'            => ['required', 'array', 'min:1'],
            'type.*'          => ['required', new Enum(DataType::class)],
            'field_type_id'   => ['nullable', 'array'],
            'section_id'      => ['nullable', 'array'],
            'section_id.*'    => ['nullable', 'uuid', 'exists:sections,id'],
            'enabled'         => ['nullable', 'array'],
            'enabled.*'       => ['nullable', 'in:0,1'],
            'required'        => ['nullable', 'array'],
            'required.*'      => ['nullable', 'in:0,1'],
            'status'          => ['required', 'array', 'min:1'],
            'status.*'        => ['required', 'in:active,inactive'],
            'is_group_parent' => ['nullable', 'array'],
            'group_seq'       => ['nullable', 'array'],
        ], $perRowRules), $customMessages);

        $count = count($validated['name']);

        // Pass 1 — create standalone questions and group parents; track IDs by group_seq.
        $groupParentIds = [];
        for ($i = 0; $i < $count; $i++) {
            $seq      = $validated['group_seq'][$i] ?? '';
            $isParent = ($validated['is_group_parent'][$i] ?? '0') === '1';
            if ($seq !== '' && !$isParent) continue; // children handled in pass 2

            $q = Questionnaire::create([
                'name'          => $validated['name'][$i],
                'key'           => strtolower($validated['key'][$i]),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
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

            $parentId = $groupParentIds[$seq] ?? null;

            $q = Questionnaire::create([
                'name'          => $validated['name'][$i],
                'key'           => strtolower($validated['key'][$i]),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
                'section_id'    => $validated['section_id'][$i] ?? null,
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
        $needsFieldType = in_array($request->type, [DataType::Toggle->value, DataType::OptionList->value]);
        $isSubQ         = $request->type === DataType::SubQuestionnaire->value;
        $parentId       = $request->input('parent_id');
        $parentIsSwitch = $isSubQ && $parentId && Questionnaire::where('id', $parentId)->value('type') === DataType::Toggle->value;

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'key'           => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('questionnaires', 'key')->ignore($questionnaire->id)],
            'type'          => ['required', new Enum(DataType::class)],
            'field_type_id' => $needsFieldType ? ['required', 'uuid', 'exists:field_types,id'] : ['nullable'],
            'section_id'    => ['nullable', 'uuid', 'exists:sections,id'],
            'parent_id'     => $isSubQ
                                ? ['required', 'uuid', 'exists:questionnaires,id', Rule::notIn([$questionnaire->id])]
                                : ['nullable'],
            'condition'     => $parentIsSwitch ? ['required', 'in:yes,no'] : ['nullable', 'in:yes,no'],
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
        $parentIsSwitch = $parent->type === DataType::Toggle->value;

        foreach ($types as $i => $type) {
            $num     = $i + 1;
            $needsFt = in_array($type, [DataType::Toggle->value, DataType::OptionList->value]);

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];

            $perRowRules["condition.$i"] = $parentIsSwitch
                ? ['required', 'in:yes,no']
                : ['nullable', 'in:yes,no'];

            $customMessages["name.{$i}.required"]          = "Sub-question #{$num}: Name is required.";
            $customMessages["key.{$i}.required"]           = "Sub-question #{$num}: Key is required.";
            $customMessages["key.{$i}.distinct"]           = "Sub-question #{$num}: Duplicate key — each sub-question must have a unique key.";
            $customMessages["key.{$i}.alpha_dash"]         = "Sub-question #{$num}: Key may only contain letters, numbers, dashes, and underscores.";
            $customMessages["type.{$i}.required"]          = "Sub-question #{$num}: Please select a data type.";
            $customMessages["status.{$i}.required"]        = "Sub-question #{$num}: Status is required.";
            $customMessages["field_type_id.{$i}.required"] = "Sub-question #{$num}: Option set is required for this data type.";
            $customMessages["condition.{$i}.required"]     = "Sub-question #{$num}: Condition (Yes/No) is required.";
        }

        $validated = $request->validate(array_merge([
            'sub_id'        => ['nullable', 'array'],
            'sub_id.*'      => ['nullable', 'uuid'],
            'name'          => ['required', 'array', 'min:1'],
            'name.*'        => ['required', 'string', 'max:255'],
            'key'           => ['required', 'array', 'min:1'],
            'key.*'         => ['required', 'string', 'max:100', 'alpha_dash', 'distinct'],
            'type'          => ['required', 'array', 'min:1'],
            'type.*'        => ['required', new Enum(DataType::class)],
            'field_type_id' => ['nullable', 'array'],
            'condition'     => ['nullable', 'array'],
            'section_id'    => ['nullable', 'uuid', 'exists:sections,id'],
            'enabled'       => ['nullable', 'array'],
            'enabled.*'     => ['nullable', 'in:0,1'],
            'required'      => ['nullable', 'array'],
            'required.*'    => ['nullable', 'in:0,1'],
            'status'        => ['required', 'array', 'min:1'],
            'status.*'      => ['required', 'in:active,inactive'],
        ], $perRowRules), $customMessages);

        $submittedIds = array_values(array_filter($validated['sub_id'] ?? [], fn($id) => !empty($id)));

        Questionnaire::where('parent_id', $parent->id)
            ->when(!empty($submittedIds), fn($q) => $q->whereNotIn('id', $submittedIds))
            ->delete();

        $count = count($validated['name']);
        for ($i = 0; $i < $count; $i++) {
            $row = [
                'name'          => $validated['name'][$i],
                'key'           => strtolower($validated['key'][$i]),
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
}
