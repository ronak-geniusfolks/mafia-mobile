<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDealerRequest extends FormRequest
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
        $dealerId = $this->route('id') ?? $this->input('id');

        return [
            'id'             => 'sometimes|exists:dealers,id',
            'name'           => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('dealers', 'name')->ignore($dealerId),
            ],
            'contact_number' => [
                'required',
                'string',
                'min:10',
                'max:20',
                Rule::unique('dealers', 'contact_number')->ignore($dealerId),
            ],
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
            'id.exists'               => 'The selected dealer does not exist.',
            'name.required'           => 'The dealer name field is required.',
            'name.string'             => 'The dealer name must be a valid text.',
            'name.min'                => 'The dealer name must be at least 2 characters.',
            'name.max'                => 'The dealer name may not be greater than 255 characters.',
            'name.unique'             => 'This dealer name already exists. Please choose a different name.',
            'contact_number.required' => 'The contact number field is required.',
            'contact_number.string'   => 'The contact number must be a valid text.',
            'contact_number.min'      => 'The contact number must be at least 10 characters.',
            'contact_number.max'      => 'The contact number may not be greater than 20 characters.',
            'contact_number.unique'    => 'This contact number already exists. Please use a different contact number.',
            'address.string'           => 'The address must be a valid text.',
            'address.max'              => 'The address may not be greater than 500 characters.',
        ];
    }
}
