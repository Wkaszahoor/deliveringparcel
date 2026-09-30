<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScreenRequest extends FormRequest
{
    public function authorize()
    {
        return true; // route middleware already enforces auth + role:admin
    }

    public function rules()
    {
        return [
            'is_visible'                => 'boolean',
            'is_enabled'                => 'boolean',
            'visible_to'                => 'array',
            'visible_to.*'              => 'in:admin,client',
            'config'                    => 'nullable|array',
            'config.visible_elements'   => 'nullable|array',
            'config.visible_buttons'    => 'nullable|array',
        ];
    }
}
