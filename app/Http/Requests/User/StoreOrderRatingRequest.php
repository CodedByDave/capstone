<?php

namespace App\Http\Requests\User;

use App\Models\OrderRating;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    protected function prepareForValidation(): void
    {
        $comment = $this->input('comment');

        // An empty or whitespace-only comment means "no comment".
        $this->merge([
            'comment' => is_string($comment) && trim($comment) !== '' ? trim($comment) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:'.OrderRating::MIN.','.OrderRating::MAX],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Choose a star rating.',
            'rating.between' => 'The rating must be between 1 and 5 stars.',
            'comment.max' => 'Keep the comment to 500 characters or fewer.',
        ];
    }
}
