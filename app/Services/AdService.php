<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdFieldValue;
use App\Models\Category;
use App\Models\CategoryField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AdService
{
    /**
     * Create a new ad with its dynamic field values.
     *
     * @param array $validatedData The validated request data
     * @param int $userId The authenticated user's ID
     * @return Ad The created ad with its relationships
     */
    public function createAd(array $validatedData, int $userId): Ad
    {
        return DB::transaction(function () use ($validatedData, $userId) {
            // Create the ad
            $ad = Ad::create([
                'user_id' => $userId,
                'category_id' => $validatedData['category_id'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
            ]);

            // Get the category fields and store dynamic values
            if (!empty($validatedData['fields'])) {
                $this->saveDynamicFields($ad, $validatedData['fields']);
            }

            // Load relationships for the response
            $ad->load(['category', 'fieldValues.categoryField.field', 'fieldValues.selectedOption']);

            return $ad;
        });
    }

    /**
     * Save dynamic field values for an ad.
     *
     * @param Ad $ad The ad to save fields for
     * @param array $fields The field values from the request
     * @return void
     */
    protected function saveDynamicFields(Ad $ad, array $fields): void
    {
        $category = Category::with(['categoryFields.field'])->find($ad->category_id);
        
        if (!$category) {
            return;
        }

        foreach ($category->categoryFields as $categoryField) {
            $fieldAttribute = $categoryField->field->attribute;
            
            if (!isset($fields[$fieldAttribute])) {
                continue;
            }

            $value = $fields[$fieldAttribute];
            $fieldData = [
                'ad_id' => $ad->id,
                'category_field_id' => $categoryField->id,
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'selected_option_id' => null,
            ];

            // If it's a select/radio field type, try to find the option ID
            if (in_array($categoryField->field->filter_type, ['single_choice', 'multiple_choice'])) {
                $option = $categoryField->field->options()
                    ->where('value', $value)
                    ->first();
                
                if ($option) {
                    $fieldData['selected_option_id'] = $option->id;
                }
            }

            AdFieldValue::create($fieldData);
        }
    }

    /**
     * Get paginated ads for a specific user.
     *
     * @param int $userId The user's ID
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserAds(int $userId, int $perPage = 15)
    {
        return Ad::where('user_id', $userId)
            ->with(['category', 'fieldValues.categoryField.field', 'fieldValues.selectedOption'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single ad by ID with all its relationships.
     *
     * @param int $adId The ad's ID
     * @return Ad|null
     */
    public function getAdById(int $adId): ?Ad
    {
        return Ad::with([
            'category',
            'user:id,name,email',
            'fieldValues.categoryField.field',
            'fieldValues.selectedOption',
        ])->find($adId);
    }

    /**
     * Get the category fields for building dynamic validation rules.
     *
     * @param int $categoryId The category ID
     * @return Collection
     */
    public function getCategoryFields(int $categoryId): Collection
    {
        return CategoryField::where('category_id', $categoryId)
            ->with(['field.options'])
            ->get();
    }
}