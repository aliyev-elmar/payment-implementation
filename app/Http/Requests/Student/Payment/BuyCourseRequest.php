<?php

namespace App\Http\Requests\Student\Payment;

use Illuminate\Foundation\Http\FormRequest;

class BuyCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'courses_ids' => 'required|array',
            'courses_ids.*' => 'integer|exists:courses,id',
        ];
    }
}
