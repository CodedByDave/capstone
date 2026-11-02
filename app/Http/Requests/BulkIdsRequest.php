<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** The keys of the table rows picked for a bulk archive or restore. */
class BulkIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Tables send numeric row keys as numbers; compare them all as strings. */
    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids');

        if (is_array($ids)) {
            $this->merge(['ids' => array_map(
                fn ($id) => is_int($id) || is_float($id) ? (string) $id : $id,
                $ids,
            )]);
        }
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'No records were selected.',
            'ids.min' => 'Please select at least one record.',
            'ids.max' => 'You can select up to 500 records at a time.',
            'ids.*.regex' => 'One or more selected records are invalid.',
        ];
    }

    /** @return list<string> */
    public function ids(): array
    {
        return array_map('strval', $this->validated('ids'));
    }
}
