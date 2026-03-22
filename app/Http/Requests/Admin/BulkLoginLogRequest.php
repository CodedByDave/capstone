<?php

namespace App\Http\Requests\Admin\LoginLog;

use Illuminate\Foundation\Http\FormRequest;

class BulkLoginLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:login_logs,id',
        ];
    }
}
