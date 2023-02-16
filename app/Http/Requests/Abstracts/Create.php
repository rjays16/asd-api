<?php

namespace App\Http\Requests\Abstracts;

use Anik\Form\FormRequest;

class Create extends FormRequest
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * @var \Illuminate\Contracts\Validation\Validator
     */

    protected $validator;

    public function setContainer($app)
    {
        $this->app = $app;
    }

    protected function authorize(): bool
    {
        return true;
    }

    protected function rules(): array
    {
        return [
            'authors.*.last_name' => 'required|string|max:255',
            'authors.*.first_name' => 'required|string|max:255',
            'authors.*.city' => 'required|string|max:255',
            'authors.*.country' => 'required|string|max:255',
            'authors.*.email' => 'nullable|string|max:255',
            'title' => 'required|string',
            'structured_abstract' => 'string',
            'keywords' => 'nullable|string',
            'is_conflict_interest' => 'boolean',
            'conflict_interest' => 'nullable|string|max:255',
            'is_commercial_funding' => 'boolean',
            'commercial_funding' => 'nullable|string|max:255',
            'abstract_type' => 'nullable|integer',
        ];
    }

    protected function messages(): array
    {
        return [];
    }

    protected function attributes(): array
    {
        return [];
    }

    public function validated(): array
    {
        return $this->validator->validated();
    }
}
