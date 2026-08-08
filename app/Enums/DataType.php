<?php

namespace App\Enums;

enum DataType: string
{
    case Text              = 'text';
    case Numeric           = 'numeric';
    case Toggle            = 'switch';       // 'Switch' is a reserved PHP keyword
    case OptionList        = 'option_list';
    case LongText          = 'long_text';
    case SubQuestionnaire  = 'sub_questionnaire';

    public function label(): string
    {
        return match($this) {
            DataType::Text             => 'Text',
            DataType::Numeric          => 'Numeric',
            DataType::Toggle           => 'Switch',
            DataType::OptionList       => 'Option List',
            DataType::LongText         => 'Long Text',
            DataType::SubQuestionnaire => 'Sub-Questionnaire',
        };
    }

    public function description(): string
    {
        return match($this) {
            DataType::Text             => 'Single-line text input up to the allowed length.',
            DataType::Numeric          => 'Numeric value input (integer or decimal).',
            DataType::Toggle           => 'Two-option toggle — e.g. Yes / No, Pass / Fail.',
            DataType::OptionList       => 'Select from a list of predefined options (more than one choice).',
            DataType::LongText         => 'Multi-line text input up to the allowed length.',
            DataType::SubQuestionnaire => 'A nested group of questions linked to a parent questionnaire.',
        };
    }

    /**
     * Returns all cases as a flat array for dropdowns and select inputs.
     * Each item: ['value' => '...', 'label' => '...', 'description' => '...']
     */
    public static function selectOptions(): array
    {
        return array_map(
            fn(DataType $type) => [
                'value'       => $type->value,
                'label'       => $type->label(),
                'description' => $type->description(),
            ],
            self::cases()
        );
    }

    /**
     * Returns just value => label pairs, handy for simple <select> elements.
     */
    public static function valueLabelMap(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->value] = $case->label();
        }
        return $map;
    }
}
