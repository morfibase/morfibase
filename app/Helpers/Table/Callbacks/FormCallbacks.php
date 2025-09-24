<?php

namespace App\Helpers\Table\Callbacks;

use Closure;
use Filament\Forms\Components\Field;

use function PHPUnit\Framework\isEmpty;

class FormCallbacks
{
    public static function textInput(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'inputType' => function (Field $input, array $options = []) {
                foreach ($options as $option) {
                    $input->{$option}();
                }

                return $input;
            },
            'datalist' => fn (Field $input, array $params = []) => $input->datalist(...$params),
            'step' => fn (Field $input, array $params = []) => $input->step(...$params),
            'minLength' => fn (Field $input, array $params = []) => $input->minLength(...$params),
            'maxLength' => fn (Field $input, array $params = []) => $input->maxLength(...$params),
            'minValue' => fn (Field $input, array $params = []) => $input->minValue(...$params),
            'maxValue' => fn (Field $input, array $params = []) => $input->maxValue(...$params),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
            'copyable' => fn (Field $input, array $params = []) => $input->copyable(...$params),
            'revealable' => fn (Field $input, array $params = []) => $input->revealable(...$params),
            'autocapitalize' => fn (Field $input, array $params = []) => $input->autocapitalize(...$params),
            'prefix' => fn (Field $input, array $params = []) => $input->prefix(...$params),
            'suffix' => fn (Field $input, array $params = []) => $input->suffix(...$params),
        ];
    }

    public static function select(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'options' => fn (Field $input, array $params = []) => $input->options(...$params),
            'native' => fn (Field $input, array $params = []) => $input->native(false),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
            'searchable' => fn (Field $input, array $params = []) => $input->searchable(...$params),
            'multiple' => fn (Field $input, array $params = []) => $input->multiple(...$params),
            'minItems' => fn (Field $input, array $params = []) => $input->minItems(...$params),
            'maxItems' => fn (Field $input, array $params = []) => $input->maxItems(...$params),
            'prefix' => fn (Field $input, array $params = []) => $input->prefix(...$params),
            'suffix' => fn (Field $input, array $params = []) => $input->suffix(...$params),
        ];
    }

    public static function checkbox(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'inline' => fn (Field $input, array $params = []) => $input->inline(...$params),
            'accepted' => fn (Field $input, array $params = []) => $input->accepted(...$params),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
        ];
    }

    public static function toggle(): array
    {
        return self::checkbox();
    }

    public static function checkboxList(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'searchable' => fn (Field $input, array $params = []) => $input->searchable(...$params),
            'noSearchResultsMessage' => fn (Field $input, array $params = []) => $input->noSearchResultsMessage(...$params),
            'bulkToggleable' => fn (Field $input, array $params = []) => $input->bulkToggleable(...$params),
            'options' => fn (Field $input, array $params = []) => $input->options(...$params),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
        ];
    }

    public static function radio(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'inline' => fn (Field $input, array $params = []) => $input->inline(...$params),
            'options' => fn (Field $input, array $params = []) => $input->options(...$params),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
        ];
    }

    public static function fileUpload(): array
    {
        return [
            'label' => fn (Field $input, array $params = []) => $input->label(...$params),
            'required' => fn (Field $input, array $params = []) => $input->required(...$params),
            'directory' => fn (Field $input, array $params = []) => $input->directory(...$params),
            'disk' => fn (Field $input, array $params = []) => $input->disk(...$params),
            'multiple' => fn (Field $input, array $params = []) => $input->multiple(...$params),
            'minFiles' => fn (Field $input, array $params = []) => $input->minFiles(...$params),
            'maxFiles' => fn (Field $input, array $params = []) => $input->maxFiles(...$params),
            'avatar' => fn (Field $input, array $params = []) => isset($params[0]) && $params[0] == true ? $input->avatar() : $input,
            'image' => fn (Field $input, array $params = []) => isset($params[0]) && $params[0] == true ? $input->image() : $input,
            
            /**
             * When you save an edited image, it consideres that you have +1 image when you save so it will not work if 
             * the user is already at maxFiles.
             * That is why we disabled the image editor an all its features
             * This issue was documented here: https://github.com/filamentphp/filament/issues/14307
             */
            // 'imageEditor' => fn (Field $input, array $params = []) => $input->imageEditor(...$params),
            // 'imageEditorAspectRatios' => fn (Field $input, array $params = []) => $input->imageEditorAspectRatios($params),
            // 'imageEditorEmptyFillColor' => fn (Field $input, array $params = []) => $input->imageEditorEmptyFillColor(...$params),
            // 'imageEditorViewportWidth' => fn (Field $input, array $params = []) => $input->imageEditorViewportWidth(...$params),
            // 'imageEditorViewportHeight' => fn (Field $input, array $params = []) => $input->imageEditorViewportHeight(...$params),
            // 'circleCropper' => fn (Field $input, array $params = []) => $input->circleCropper(...$params),
            // 'imageResizeMode' => fn (Field $input, array $params = []) => $input->imageResizeMode(...$params),

            'reorderable' => fn (Field $input, array $params = []) => $input->reorderable(...$params),
            'openable' => fn (Field $input, array $params = []) => $input->openable(...$params),
            'downloadable' => fn (Field $input, array $params = []) => $input->downloadable(...$params),
            'deletable' => fn (Field $input, array $params = []) => $input->deletable(...$params),
            'previewable' => fn (Field $input, array $params = []) => $input->previewable(...$params),
            'acceptedFileTypes' => fn (Field $input, array $params = []) => isset($params[0]) && is_array($params[0]) && count($params[0]) != 0 ? $input->acceptedFileTypes($params[0]) : $input,
            'minSize' => fn (Field $input, array $params = []) => isset($params[0]) ? $input->minSize($params[0]) : $input,
            'maxSize' => fn (Field $input, array $params = []) => isset($params[0]) ? $input->maxSize($params[0]) : $input,
        ];
    }
}
