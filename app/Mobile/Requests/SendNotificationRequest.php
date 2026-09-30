<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'target_type'  => 'required|in:all,role,user,order',
            'target_role'  => 'nullable|required_if:target_type,role|in:admin,client',
            'target_user'  => 'nullable|required_if:target_type,user|integer',
            'target_order' => 'nullable|required_if:target_type,order|string|max:100',
            'type'         => 'required|in:order_update,message,announcement,alert,promotional',
            'title'        => 'required|string|max:200',
            'body'         => 'required|string|max:1000',
            'schedule'     => 'nullable|in:now,later',
            'scheduled_at' => 'nullable|required_if:schedule,later|date|after:now',
        ];
    }
}
