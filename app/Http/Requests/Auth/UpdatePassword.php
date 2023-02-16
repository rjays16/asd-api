<?php

namespace App\Http\Requests\Auth;

use Anik\Form\FormRequest;

class UpdatePassword extends FormRequest
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
            'current_password' => 'required|string|max:150',
            'password' => 'required|string|max:150',
            'confirm_password' => 'required|string|max:150',
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