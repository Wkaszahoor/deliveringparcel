<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabelsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'labels'                => 'required|array',
            'labels.*.label_key'    => 'required|string|max:100',
            'labels.*.label_value'  => 'required|string|max:255',
        ];
    }
}
