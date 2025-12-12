<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Field;
use Illuminate\Support\Collection;

class CategoryFieldValidationService
{
    /**
     * Build dynamic validation rules based on category fields.
     *
     * @param int $categoryId The category ID
     * @return array Array of validation rules
     */
    public function buildValidationRules(int $categoryId): array
    {
        $rules = [];
        $categoryFields = $this->getCategoryFieldsWithOptions($categoryId);
        
        foreach ($categoryFields as $categoryField) {
            $field = $categoryField->field;
            $fieldAttribute = "fields.{$field->attribute}";
            
            $fieldRules = $this->buildFieldRules($categoryField, $field);
            
            if (!empty($fieldRules)) {
                $rules[$fieldAttribute] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Build validation rules for a single field.
     *
     * @param CategoryField $categoryField The category field pivot
     * @param Field $field The field definition
     * @return array The validation rules for this field
     */
    protected function buildFieldRules(CategoryField $categoryField, Field $field): array
    {
        $rules = [];

        // Required/optional
        if ($categoryField->is_mandatory) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-based validation
        $rules = array_merge($rules, $this->getTypeValidationRules($field));

        // Choice-based validation (select/radio)
        if (in_array($field->filter_type, ['single_choice', 'multiple_choice'])) {
            $optionValues = $field->options->pluck('value')->toArray();
            if (!empty($optionValues)) {
                $rules[] = 'in:' . implode(',', $optionValues);
            }
        }

        return $rules;
    }

    /**
     * Get type-specific validation rules.
     *
     * @param Field $field The field definition
     * @return array Type-specific validation rules
     */
    protected function getTypeValidationRules(Field $field): array
    {
        $rules = [];

        switch ($field->value_type) {
            case 'integer':
            case 'number':
                $rules[] = 'integer';
                break;
            case 'decimal':
            case 'float':
                $rules[] = 'numeric';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'string':
            case 'text':
            default:
                $rules[] = 'string';
                break;
        }

        // Additional rules based on filter type
        switch ($field->filter_type) {
            case 'range':
                if ($field->value_type === 'integer' || $field->value_type === 'number') {
                    $rules[] = 'min:0';
                }
                break;
            case 'text':
                $rules[] = 'max:500';
                break;
        }

        return $rules;
    }

    /**
     * Build custom validation messages for category fields.
     *
     * @param int $categoryId The category ID
     * @return array Array of custom validation messages
     */
    public function buildValidationMessages(int $categoryId): array
    {
        $messages = [];
        $categoryFields = $this->getCategoryFieldsWithOptions($categoryId);

        foreach ($categoryFields as $categoryField) {
            $field = $categoryField->field;
            $fieldKey = "fields.{$field->attribute}";
            $fieldName = $field->name;

            $messages["{$fieldKey}.required"] = "The {$fieldName} field is required.";
            $messages["{$fieldKey}.integer"] = "The {$fieldName} field must be an integer.";
            $messages["{$fieldKey}.numeric"] = "The {$fieldName} field must be a number.";
            $messages["{$fieldKey}.string"] = "The {$fieldName} field must be a string.";
            $messages["{$fieldKey}.in"] = "The selected {$fieldName} is invalid.";
            $messages["{$fieldKey}.max"] = "The {$fieldName} field must not exceed :max characters.";
        }

        return $messages;
    }

    /**
     * Build custom validation attribute names.
     *
     * @param int $categoryId The category ID
     * @return array Array of custom attribute names
     */
    public function buildValidationAttributes(int $categoryId): array
    {
        $attributes = [];
        $categoryFields = $this->getCategoryFieldsWithOptions($categoryId);

        foreach ($categoryFields as $categoryField) {
            $field = $categoryField->field;
            $attributes["fields.{$field->attribute}"] = $field->name;
        }

        return $attributes;
    }

    /**
     * Get category fields with their options loaded.
     *
     * @param int $categoryId The category ID
     * @return Collection
     */
    protected function getCategoryFieldsWithOptions(int $categoryId): Collection
    {
        return CategoryField::where('category_id', $categoryId)
            ->with(['field.options'])
            ->get();
    }

    /**
     * Check if a category exists.
     *
     * @param int $categoryId The category ID
     * @return bool
     */
    public function categoryExists(int $categoryId): bool
    {
        return Category::where('id', $categoryId)->exists();
    }
}