<?php

namespace App\Helpers\Collection\Callbacks;

use App\Models\GenericModel;
use Filament\Forms\Components\Field;

class Callbacks
{
    public static function textInput(): array
    {
        return [
            'label' => fn(Field $input, array $params = []) => $input->label(...$params),
            'inputType' => function (Field $input, array $options = []) {
                foreach($options as $option) {
                    $input->{$option}();
                }
                return $input;
            },
            'datalist' => fn(Field $input, array $params = []) => $input->datalist(...$params),
            'step' => fn(Field $input, array $params = []) => $input->step(...$params),
            'minLength' => fn(Field $input, array $params = []) => $input->minLength(...$params),
            'maxLength' => fn(Field $input, array $params = []) => $input->maxLength(...$params),
            'minValue' => fn(Field $input, array $params = []) => $input->minValue(...$params),
            'maxValue' => fn(Field $input, array $params = []) => $input->maxValue(...$params),
            'required' => fn(Field $input, array $params = []) => $input->required(...$params),
            'copyable' => fn(Field $input, array $params = []) => $input->copyable(fn(?GenericModel $genericModel) => $genericModel !== null),
            'revealable' => fn(Field $input, array $params = []) => $input->revealable(...$params),
            'autocapitalize' => fn(Field $input, array $params = []) => $input->autocapitalize(...$params),
            'prefix' => fn(Field $input, array $params = []) => $input->prefix(...$params),
            'suffix' => fn(Field $input, array $params = []) => $input->suffix(...$params),
        ];
    }

    public static function select(): array
    {
        return [
            'label' => fn(Field $input, array $params = []) => $input->label(...$params),
            'options' => fn(Field $input, array $params = []) => $input->options(...$params),
            'native' => fn(Field $input, array $params = []) => $input->native(false),
            'required' => fn(Field $input, array $params = []) => $input->required(...$params),
            'multiple' => fn(Field $input, array $params = []) => $input->multiple(...$params),
            'minItems' => fn(Field $input, array $params = []) => $input->minItems(...$params),
            'maxItems' => fn(Field $input, array $params = []) => $input->maxItems(...$params),
            'prefix' => fn(Field $input, array $params = []) => $input->prefix(...$params),
            'suffix' => fn(Field $input, array $params = []) => $input->suffix(...$params),
        ];
    }
}