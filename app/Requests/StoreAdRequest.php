<?php

namespace App\Requests;

use App\Services\CategoryFieldValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAdRequest extends FormRequest
{
    protected CategoryFieldValidationService $validationService;

    public function __construct(CategoryFieldValidationService $validationService)
    {
        parent::__construct();
        $this->validationService = $validationService;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by Sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Base rules for core ad fields
        $rules = [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'fields' => ['sometimes', 'array'],
        ];

        // Add dynamic validation rules based on category fields
        $categoryId = $this->input('category_id');
        
        if ($categoryId && $this->validationService->categoryExists($categoryId)) {
            $dynamicRules = $this->validationService->buildValidationRules($categoryId);
            $rules = array_merge($rules, $dynamicRules);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'title.required' => 'Please provide a title for your ad.',
            'title.min' => 'The title must be at least :min characters.',
            'title.max' => 'The title must not exceed :max characters.',
            'description.required' => 'Please provide a description for your ad.',
            'description.min' => 'The description must be at least :min characters.',
            'description.max' => 'The description must not exceed :max characters.',
            'price.required' => 'Please provide a price for your ad.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
        ];

        // Add dynamic messages for category fields
        $categoryId = $this->input('category_id');
        
        if ($categoryId && $this->validationService->categoryExists($categoryId)) {
            $dynamicMessages = $this->validationService->buildValidationMessages($categoryId);
            $messages = array_merge($messages, $dynamicMessages);
        }

        return $messages;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'category_id' => 'category',
            'title' => 'title',
            'description' => 'description',
            'price' => 'price',
        ];

        // Add dynamic attributes for category fields
        $categoryId = $this->input('category_id');
        
        if ($categoryId && $this->validationService->categoryExists($categoryId)) {
            $dynamicAttributes = $this->validationService->buildValidationAttributes($categoryId);
            $attributes = array_merge($attributes, $dynamicAttributes);
        }

        return $attributes;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}