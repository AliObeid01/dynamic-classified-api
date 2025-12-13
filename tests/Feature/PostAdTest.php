<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostAdTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Field $textField;
    protected Field $selectField;
    protected CategoryField $textCategoryField;
    protected CategoryField $selectCategoryField;
    protected CategoryFieldOption $option1;
    protected CategoryFieldOption $option2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();

        // Create a category
        $this->category = Category::create([
            'external_id' => 23,
            'name' => 'Cars for Sale',
            'slug' => 'cars-for-sale',
            'name_l1' => 'سيارات للبيع',
            'level' => 2,
        ]);

        // Create a text field (e.g., mileage)
        $this->textField = Field::create([
            'name' => 'Mileage',
            'attribute' => 'mileage',
            'value_type' => 'integer',
            'filter_type' => 'range',
        ]);

        // Create a select field (e.g., fuel type)
        $this->selectField = Field::create([
            'name' => 'Fuel Type',
            'attribute' => 'fuel_type',
            'value_type' => 'string',
            'filter_type' => 'single_choice',
        ]);

        // Link fields to category
        $this->textCategoryField = CategoryField::create([
            'category_id' => $this->category->id,
            'field_id' => $this->textField->id,
            'is_mandatory' => true,
        ]);

        $this->selectCategoryField = CategoryField::create([
            'category_id' => $this->category->id,
            'field_id' => $this->selectField->id,
            'is_mandatory' => true,
        ]);

        // Create options for select field
        $this->option1 = CategoryFieldOption::create([
            'field_id' => $this->selectField->id,
            'value' => 'petrol',
            'label' => 'Petrol',
        ]);

        $this->option2 = CategoryFieldOption::create([
            'field_id' => $this->selectField->id,
            'value' => 'diesel',
            'label' => 'Diesel',
        ]);
    }

    /** @test */
    public function authenticated_user_can_create_ad_with_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ads', [
            'category_id' => $this->category->id,
            'title' => 'BMW 320i 2020 Model',
            'description' => 'Well maintained car, single owner, full service history available.',
            'price' => 25000.00,
            'fields' => [
                'mileage' => 50000,
                'fuel_type' => 'petrol',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price',
                    // 'category',
                    // 'dynamic_fields',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Ad created successfully',
                'data' => [
                    'title' => 'BMW 320i 2020 Model',
                    'price' => 25000.00,
                ],
            ]);

        $this->assertDatabaseHas('ads', [
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'BMW 320i 2020 Model',
        ]);

        $this->assertDatabaseHas('ad_field_values', [
            'value' => '50000',
        ]);

        $response->dump();
    }

    /** @test */
    public function validation_fails_when_required_fields_are_missing(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ads', [
            // Missing all required fields
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'category_id',
                    'title',
                    'description',
                    'price',
                ],
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);

            $response->dump();
    }

    /** @test */
    public function validation_fails_when_required_dynamic_field_is_missing(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ads', [
            'category_id' => $this->category->id,
            'title' => 'BMW 320i 2020 Model',
            'description' => 'Well maintained car, single owner, full service history.',
            'price' => 25000.00,
            'fields' => [
                // Missing required 'mileage' and 'fuel_type'
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fields.mileage', 'fields.fuel_type']);
        $response->dump();
    }

    /** @test */
    public function validation_fails_when_dynamic_field_has_invalid_type(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/ads', [
            'category_id' => $this->category->id,
            'title' => 'BMW 320i 2020 Model',
            'description' => 'Well maintained car, single owner, full service history.',
            'price' => 25000.00,
            'fields' => [
                'mileage' => 'not-a-number', // Should be integer
                'fuel_type' => 'petrol',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fields.mileage']);

        $response->dump();
    }
}