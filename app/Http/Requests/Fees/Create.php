<?php

namespace App\Http\Requests\Fees;

use Anik\Form\FormRequest;

class Create extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'type' => 'required|numeric',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'year' => 'required|date_format:Y',
            'scope' => 'required|boolean',
            'amount' => 'required|numeric',
            'status' => 'required|boolean',
            'uses_late_amount' => 'required|boolean',
            'late_amount' => 'required_if:uses_late_amount,true|numeric',
            'late_amount_starts_on' => 'required_if:uses_late_amount,true|date_format:Y-m-d|nullable',
        ];
    }
}
