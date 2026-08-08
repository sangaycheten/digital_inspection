<?php

namespace App\Http\Controllers\Admin\Master;

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
        $typeOptions  = DataType::valueLabelMap();
        $fieldTypes   = FieldType::where('status', 'active')->orderBy('name')->get();
        $fieldTypesForJs = $fieldTypes->map(fn($ft) => [
            'id'      => $ft->id,
            'name'    => $ft->name,
            'type'    => $ft->type,
            'options' => $ft->options ?? [],
        ])->values()->all();
        $parentQuestionnaires = Questionnaire::where('type', '!=', DataType::SubQuestionnaire->value)
            ->where('status', 'active')->orderBy('name')->get(['id', 'name', 'key']);
        $sections      = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $sectionsForJs = $sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->all();

        return view('admin.master.questionnaires.create', compact(
            'typeOptions', 'fieldTypes', 'fieldTypesForJs', 'parentQuestionnaires', 'sections', 'sectionsForJs'
        ));
    }

    public function edit(Questionnaire $questionnaire): View
    {
        $typeOptions  = DataType::valueLabelMap();
        $fieldTypes   = FieldType::where('status', 'active')->orderBy('name')->get();
        $fieldTypesForJs = $fieldTypes->map(fn($ft) => [
            'id'      => $ft->id,
            'name'    => $ft->name,
            'type'    => $ft->type,
            'options' => $ft->options ?? [],
        ])->values()->all();
        $parentQuestionnaires = Questionnaire::where('type', '!=', DataType::SubQuestionnaire->value)
            ->where('status', 'active')
            ->where('id', '!=', $questionnaire->id)
            ->orderBy('name')->get(['id', 'name', 'key']);
        $sections      = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $sectionsForJs = $sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->all();
        $subQuestionnaires = $questionnaire->subQuestionnaires()->orderBy('created_at')->get();
        $subsForJs = $subQuestionnaires->map(fn($q) => [
            'id'            => $q->id,
            'name'          => $q->name,
            'key'           => $q->key,
            'type'          => $q->type,
            'field_type_id' => $q->field_type_id ?? '',
            'section_id'    => $q->section_id ?? '',
            'enabled'       => $q->enabled  ? '1' : '0',
            'required'      => $q->required ? '1' : '0',
            'status'        => $q->status,
        ])->values()->all();

        return view('admin.master.questionnaires.edit', compact(
            'questionnaire', 'subQuestionnaires', 'subsForJs',
            'typeOptions', 'fieldTypes', 'fieldTypesForJs',
            'parentQuestionnaires', 'sections', 'sectionsForJs'
        ));
    }

    public function index(Request $request): View
    {
        $questionnaires = Questionnaire::with(['fieldType', 'subQuestionnaires'])
            ->whereNull('parent_id')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) =>
                $q->where('type', $request->type))
            ->when($request->section_id, fn($q) =>
                $q->where('section_id', $request->section_id))
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        $typeOptions = DataType::valueLabelMap();

        $fieldTypes = FieldType::where('status', 'active')->orderBy('name')->get();

        $fieldTypesForJs = $fieldTypes->map(fn($ft) => [
            'id'      => $ft->id,
            'name'    => $ft->name,
            'type'    => $ft->type,
            'options' => $ft->options ?? [],
        ])->values()->all();

        // For the parent questionnaire dropdown — exclude sub-questionnaires to avoid nesting
        $parentQuestionnaires = Questionnaire::where('type', '!=', DataType::SubQuestionnaire->value)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'key']);

        $sections = Section::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $sectionsForJs = $sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->all();

        return view('admin.master.questionnaires.index', compact(
            'questionnaires', 'fieldTypes', 'fieldTypesForJs', 'typeOptions', 'parentQuestionnaires',
            'sections', 'sectionsForJs'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $types = $request->input('type', []);

        $perRowRules    = [];
        $customMessages = [];

        foreach ($types as $i => $type) {
            $num     = $i + 1;
            $needsFt = in_array($type, [DataType::Toggle->value, DataType::OptionList->value]);
            $isSubQ  = $type === DataType::SubQuestionnaire->value;

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];
            $perRowRules["parent_id.$i"] = $isSubQ
                ? ['required', 'uuid', 'exists:questionnaires,id']
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
            $customMessages["parent_id.{$i}.required"]     = "Question #{$num}: Parent questionnaire is required for sub-questionnaire type.";
        }

        $validated = $request->validate(array_merge([
            'name'          => ['required', 'array', 'min:1'],
            'name.*'        => ['required', 'string', 'max:255'],
            'key'           => ['required', 'array', 'min:1'],
            'key.*'         => ['required', 'string', 'max:100', 'alpha_dash', 'distinct', 'unique:questionnaires,key'],
            'type'          => ['required', 'array', 'min:1'],
            'type.*'        => ['required', new Enum(DataType::class)],
            'field_type_id' => ['nullable', 'array'],
            'section_id'    => ['nullable', 'array'],
            'section_id.*'  => ['nullable', 'uuid', 'exists:sections,id'],
            'parent_id'     => ['nullable', 'array'],
            'enabled'       => ['nullable', 'array'],
            'enabled.*'     => ['nullable', 'in:0,1'],
            'required'      => ['nullable', 'array'],
            'required.*'    => ['nullable', 'in:0,1'],
            'status'        => ['required', 'array', 'min:1'],
            'status.*'      => ['required', 'in:active,inactive'],
        ], $perRowRules), $customMessages);

        $count = count($validated['name']);

        for ($i = 0; $i < $count; $i++) {
            $q = Questionnaire::create([
                'name'          => $validated['name'][$i],
                'key'           => strtolower($validated['key'][$i]),
                'type'          => $validated['type'][$i],
                'field_type_id' => $validated['field_type_id'][$i] ?? null,
                'section_id'    => $validated['section_id'][$i] ?? null,
                'parent_id'     => $validated['parent_id'][$i] ?? null,
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

        return redirect()->route('admin.master.questionnaires.index')->with('success', $msg);
    }

    public function update(Request $request, Questionnaire $questionnaire): RedirectResponse
    {
        $needsFieldType = in_array($request->type, [DataType::Toggle->value, DataType::OptionList->value]);
        $isSubQ         = $request->type === DataType::SubQuestionnaire->value;

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'key'           => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('questionnaires', 'key')->ignore($questionnaire->id)],
            'type'          => ['required', new Enum(DataType::class)],
            'field_type_id' => $needsFieldType
                                ? ['required', 'uuid', 'exists:field_types,id']
                                : ['nullable'],
            'section_id'    => ['nullable', 'uuid', 'exists:sections,id'],
            'parent_id'     => $isSubQ
                                ? ['required', 'uuid', 'exists:questionnaires,id', Rule::notIn([$questionnaire->id])]
                                : ['nullable'],
            'status'        => ['required', 'in:active,inactive'],
        ]);

        $data['key']       = strtolower($data['key']);
        $data['enabled']   = $request->boolean('enabled');
        $data['required']  = $request->boolean('required');
        $data['parent_id'] = $isSubQ ? ($data['parent_id'] ?? null) : null;

        $questionnaire->update($data);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($questionnaire)->event('updated')
            ->log("Questionnaire updated: {$questionnaire->name}");

        return redirect()->route('admin.master.questionnaires.index')
            ->with('success', "Questionnaire \"{$questionnaire->name}\" updated successfully.");
    }

    public function subGroupData(Questionnaire $parent): JsonResponse
    {
        $subs = Questionnaire::where('parent_id', $parent->id)
            ->orderBy('created_at')
            ->get(['id', 'name', 'key', 'type', 'field_type_id', 'section_id', 'enabled', 'required', 'status']);

        return response()->json([
            'parent' => ['id' => $parent->id, 'name' => $parent->name, 'key' => $parent->key],
            'subs'   => $subs->map(fn($q) => [
                'id'            => $q->id,
                'name'          => $q->name,
                'key'           => $q->key,
                'type'          => $q->type,
                'field_type_id' => $q->field_type_id ?? '',
                'section_id'    => $q->section_id ?? '',
                'enabled'       => $q->enabled ? '1' : '0',
                'required'      => $q->required ? '1' : '0',
                'status'        => $q->status,
            ]),
        ]);
    }

    public function updateSubGroup(Request $request, Questionnaire $parent): RedirectResponse
    {
        $types          = $request->input('type', []);
        $perRowRules    = [];
        $customMessages = [];

        foreach ($types as $i => $type) {
            $num     = $i + 1;
            $needsFt = in_array($type, [DataType::Toggle->value, DataType::OptionList->value]);

            $perRowRules["field_type_id.$i"] = $needsFt
                ? ['required', 'uuid', 'exists:field_types,id']
                : ['nullable'];

            $customMessages["name.{$i}.required"]          = "Sub-question #{$num}: Name is required.";
            $customMessages["key.{$i}.required"]           = "Sub-question #{$num}: Key is required.";
            $customMessages["key.{$i}.distinct"]           = "Sub-question #{$num}: Duplicate key — each sub-question must have a unique key.";
            $customMessages["key.{$i}.alpha_dash"]         = "Sub-question #{$num}: Key may only contain letters, numbers, dashes, and underscores.";
            $customMessages["type.{$i}.required"]          = "Sub-question #{$num}: Please select a data type.";
            $customMessages["status.{$i}.required"]        = "Sub-question #{$num}: Status is required.";
            $customMessages["field_type_id.{$i}.required"] = "Sub-question #{$num}: Option set is required for this data type.";
        }

        $validated = $request->validate(array_merge([
            'sub_id'         => ['nullable', 'array'],
            'sub_id.*'       => ['nullable', 'uuid'],
            'name'           => ['required', 'array', 'min:1'],
            'name.*'         => ['required', 'string', 'max:255'],
            'key'            => ['required', 'array', 'min:1'],
            'key.*'          => ['required', 'string', 'max:100', 'alpha_dash', 'distinct'],
            'type'           => ['required', 'array', 'min:1'],
            'type.*'         => ['required', new Enum(DataType::class)],
            'field_type_id'  => ['nullable', 'array'],
            'section_id'     => ['nullable', 'uuid', 'exists:sections,id'],
            'enabled'        => ['nullable', 'array'],
            'enabled.*'      => ['nullable', 'in:0,1'],
            'required'       => ['nullable', 'array'],
            'required.*'     => ['nullable', 'in:0,1'],
            'status'         => ['required', 'array', 'min:1'],
            'status.*'       => ['required', 'in:active,inactive'],
        ], $perRowRules), $customMessages);

        $submittedIds = array_values(array_filter($validated['sub_id'] ?? [], fn($id) => !empty($id)));

        // Delete sub-questions that were removed from the group
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

        return redirect()->route('admin.master.questionnaires.index')
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

        return redirect()->route('admin.master.questionnaires.index')
            ->with('success', "Questionnaire \"{$name}\" deleted.");
    }
}
