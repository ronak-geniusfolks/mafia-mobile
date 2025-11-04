<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDealerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => 'Validation failed. Please check your input.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255|min:2|unique:dealers,name',
            'contact_number' => 'nullable|string|max:20|min:10|unique:dealers,contact_number',
            'address'        => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'           => 'The dealer name field is required.',
            'name.string'             => 'The dealer name must be a valid text.',
            'name.min'                => 'The dealer name must be at least 2 characters.',
            'name.max'                => 'The dealer name may not be greater than 255 characters.',
            'name.unique'             => 'This dealer name already exists. Please choose a different name.',
            'contact_number.string'   => 'The contact number must be a valid text.',
            'contact_number.min'      => 'The contact number must be at least 10 characters.',
            'contact_number.max'       => 'The contact number may not be greater than 20 characters.',
            'contact_number.unique'    => 'This contact number already exists. Please use a different contact number.',
            'address.string'           => 'The address must be a valid text.',
            'address.max'              => 'The address may not be greater than 500 characters.',
        ];
    }
}
