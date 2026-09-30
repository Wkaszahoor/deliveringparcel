<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'colors'                        => 'nullable|array',
            'colors.primary_color'          => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'colors.secondary_color'        => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'colors.background_color'       => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'message_colors'                => 'nullable|array',
            'message_colors.*.bubble'       => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'message_colors.*.text'         => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'message_colors.*.position'     => 'nullable|in:left,right,center',
            'typography'                    => 'nullable|array',
            'typography.font_size_base'     => 'nullable|integer|min:8|max:72',
            'typography.font_size_header'   => 'nullable|integer|min:8|max:72',
            'typography.font_size_message'  => 'nullable|integer|min:8|max:72',
        ];
    }
}
