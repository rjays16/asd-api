<?php

namespace App\Http\Requests\Registration;

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
            'email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'confirm_password' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix'=> 'nullable|string|max:255',
            'prof_suffix'=> 'nullable|string|max:255',
            'certificate_name'=> 'required|string|max:255',
            'country' => 'required|string|max:100',
            'is_pds' => 'nullable|boolean',
            'role' => 'required|integer',

            'member_type' => 'required|integer',
            'scope' => 'required|boolean',
            'pds_number' => 'nullable|string|max:255',
            'prc_number' => 'nullable|string|max:255',
            'institution_organization'=>'nullable|string|max:255',
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
