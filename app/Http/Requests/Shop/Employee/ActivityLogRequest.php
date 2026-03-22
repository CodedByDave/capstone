<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action'       => ['nullable', 'string', 'in:created,updated,status_changed,salary_changed,archived,restored,imported'],
            'performed_by' => ['nullable', 'integer'],
            'module'       => ['nullable', 'string'],
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date'],
            'search'       => ['nullable', 'string', 'max:100'],
            'page'         => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['action', 'performed_by', 'module', 'date_from', 'date_to', 'search']);
    }
}
