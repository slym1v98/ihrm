<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitize();
    }

    protected function sanitize(): void
    {
        $input = $this->all();
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        $this->replace($input);
    }
}
