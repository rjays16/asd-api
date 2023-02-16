<?php

namespace App\Http\Requests\Delegate;

use Anik\Form\FormRequest;

class Update extends FormRequest
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
            'email' => 'nullable|string|max:255|unique:users',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'suffix'=> 'nullable|string|max:255',
            'prof_suffix'=> 'nullable|string|max:255',
            'certificate_name'=> 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'is_pds' => 'nullable|boolean',
            'status'=>'nullable|integer',

            'member_type' => 'nullable|integer',
            'scope' => 'nullable|boolean',
            'pds_number' => 'nullable|string|max:255',
            'resident_certificate' => 'nullable|string|max:255',
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
