<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use App\Models\Field;
use App\Services\ApiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Bool_;

class CategoriesSeeder extends Seeder
{
    protected ApiService $apiService;

    public function __construct()
    {
        $this->apiService = app(ApiService::class);
    }

    public function run(): void
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $this->command->info('Fetching categories from OLX API...');

        $categories = $this->apiService->fetchCategories();

        if (!$categories) {
            $this->command->error('Failed to fetch categories from API');
            return;
        }

        $this->command->info('Syncing ' . count($categories) . ' main categories and sub categories with thier related fields and field options...');
        $this->command->info("Please wait...");

        $this->command->getOutput()->progressStart(count($categories));

        DB::transaction(function () use ($categories) {
            foreach ($categories as $catData) {
                $this->syncCategory($catData);
                $this->command->getOutput()->progressAdvance();
            }
        });

        $this->command->getOutput()->progressFinish();

        $this->command->info("✓ Synced done.");
        $this->command->info("✓ Your database is ready now.");
    }

    private function syncCategory(array $catData, ?int $parentId = null): Category
    {
        $category = Category::updateOrCreate(
            ['external_id' => $catData['externalID']],
            [
                'parent_id' => $parentId,
                'name' => $catData['name'],
                'slug' => $catData['slug'],
                'name_l1' => $catData['name_l1'],
                'level' => $catData['level'],
            ]
        );
        $category->fields()->detach();

        if (!empty($catData['children'])) {
            foreach ($catData['children'] as $childData) {
                $id=$childData['id'];
                $childCategory = $this->syncCategory($childData, $category->id);
                DB::transaction(function () use ($childCategory,$id) {
                    $this->syncCategoryFields($childCategory,$id);
                });
            }
        }

        return $category;
    }

    private function syncCategoryFields(Category $category,int $id): void
    {
        try {
            $fields = $this->apiService->fetchCategoryFields($category->external_id);

            if (!$fields || !isset($fields[$id]['flatFields'])) {
                return;
            }

            $data = $fields[$id];
            $this->syncField($data['flatFields'],$category);
            if(!empty($data['childrenFields'])) {
                $this->syncField($data['childrenFields'],$category,true);
            }
            usleep(50000); 

        } catch (\Exception $e) {
            Log::warning("Failed to sync fields for category {$category->name}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function syncField(array $data,Category $category,bool $is_children=false) 
    {
        foreach ($data as $fieldData) {
            if(isset($fieldData['roles']) && is_array($fieldData['roles']) && !in_array('exclude_from_post_an_ad', $fieldData['roles'], true)) {
                $field=Field::updateOrCreate(
                    [
                        'attribute' => $fieldData['attribute'],
                        'name' => $fieldData['name'],
                        'value_type' => $fieldData['valueType'],
                        'filter_type' => $fieldData['filterType'],
                    ],
                );
                
                $category->fields()->attach($field->id,['is_mandatory' => $fieldData['isMandatory']]);

                if (isset($fieldData['choices']) && is_array($fieldData['choices']) && in_array($fieldData['filterType'], ['multiple_choice','single_choice'])) {
                    $this->syncFieldOptions( $fieldData['choices'],$field, $is_children);
                }
            }   
        }
    }
    private function syncFieldOptions(array $choices,Field $field,bool $is_children,?int $parentId = null): void
    {
        if (!$is_children) {
            foreach ($choices as $choice) {
                CategoryFieldOption::updateOrCreate(
                    [
                        'field_id'=>$field->id,
                        'parent_id'=>$parentId,
                        'value' => $choice['value'],
                        'label' => $choice['label'],
                    ]
                );       
            }
            return;
        }
        foreach ($choices as $key=> $choice) {
            $parentOption=CategoryFieldOption::where('value',$key)->first();
            foreach ($choice as $children) {
                CategoryFieldOption::updateOrCreate(
                    [
                        'field_id'  => $field->id,
                        'parent_id' => $parentOption->id,
                        'value'     => $children['value'],
                        'label'     => $children['label'],
                    ]
                );
            }
        }
    }
}