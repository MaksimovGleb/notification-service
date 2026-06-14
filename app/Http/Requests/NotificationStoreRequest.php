<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'external_id' => 'nullable|string|max:255',
            'channel' => 'required|string|in:sms,email',
            'content' => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'priority' => 'nullable|string|in:low,normal,high',
        ];
    }
}
