<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeaturesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'flags'                        => 'required|array',
            'flags.*.is_enabled'           => 'nullable|boolean',
            'flags.*.allowed_roles'        => 'nullable|array',
            'flags.*.allowed_roles.*'      => 'in:admin,client',
            'maintenance.active'           => 'nullable|boolean',
            'maintenance.message'          => 'nullable|string|max:500',
            'maintenance.expected_back'    => 'nullable|string|max:100',
            'force_update.required'        => 'nullable|boolean',
            'force_update.min_ios'         => 'nullable|string|max:20',
            'force_update.min_android'     => 'nullable|string|max:20',
            'force_update.message'         => 'nullable|string|max:500',
        ];
    }
}
