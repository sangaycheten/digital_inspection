<?php

namespace App\Http\Controllers\Admin\Master;

use App\Enums\DataType;
use App\Http\Controllers\Controller;
use App\Models\FieldType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class DataTypeController extends Controller
{
    public function index(Request $request): View
    {
        $fieldTypes = FieldType::when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.master.data-types.index', [
            'fieldTypes'  => $fieldTypes,
            'typeOptions' => DataType::valueLabelMap(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);

        $fieldType = FieldType::create($data);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($fieldType)->event('created')
            ->withProperties(['attributes' => $data])
            ->log("Data Type created: {$fieldType->name}");

        return redirect()->route('admin.master.data-types.index')
            ->with('success', "Data Type \"{$fieldType->name}\" created successfully.");
    }

    public function update(Request $request, FieldType $fieldType): RedirectResponse
    {
        $data = $this->validateInput($request);

        $fieldType->update($data);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($fieldType)->event('updated')
            ->log("Data Type updated: {$fieldType->name}");

        return redirect()->route('admin.master.data-types.index')
            ->with('success', "Data Type \"{$fieldType->name}\" updated successfully.");
    }

    public function destroy(FieldType $fieldType): RedirectResponse
    {
        $name = $fieldType->name;
        $fieldType->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($fieldType)->event('deleted')
            ->log("Data Type deleted: {$name}");

        return redirect()->route('admin.master.data-types.index')
            ->with('success', "Data Type \"{$name}\" deleted.");
    }

    private function validateInput(Request $request): array
    {
        $type = $request->input('type');

        $rules = [
            'name'   => ['required', 'string', 'max:255'],
            'type'   => ['required', new Enum(DataType::class)],
            'status' => ['required', 'in:active,inactive'],
        ];

        if ($type === DataType::Toggle->value) {
            $rules['options']   = ['required', 'array', 'size:2'];
            $rules['options.*'] = ['required', 'string', 'max:100'];
        } elseif ($type === DataType::OptionList->value) {
            $rules['options']   = ['required', 'array', 'min:2'];
            $rules['options.*'] = ['required', 'string', 'max:100'];
        } else {
            $rules['options'] = ['nullable', 'array'];
        }

        $data = $request->validate($rules);

        if (!in_array($type, [DataType::Toggle->value, DataType::OptionList->value])) {
            $data['options'] = null;
        }

        return $data;
    }
}
