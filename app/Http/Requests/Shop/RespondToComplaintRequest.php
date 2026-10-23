<?php

namespace App\Http\Requests\Shop;

use App\Models\OrderComplaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondToComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route limits this to the owner and staff with Operations manage.
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
    }

    protected function prepareForValidation(): void
    {
        $reply = $this->input('reply');

        $this->merge([
            'reply' => is_string($reply) && trim($reply) !== '' ? trim($reply) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrderComplaint::RESPONSE_STATUSES)],
            // The customer must be told the outcome when a report is resolved or closed.
            'reply' => [
                Rule::requiredIf(fn () => in_array($this->input('status'), ['resolved', 'rejected'], true)),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Choose a status.',
            'reply.required' => 'Write a reply so the customer knows the outcome.',
            'reply.max' => 'Keep the reply to 1000 characters or fewer.',
        ];
    }
}
