<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class ApiService
{
    protected string $baseUrl = 'https://www.olx.com.lb/api';

    protected int $cacheDuration = 86400;

    protected string $cachePrefix = 'olx_api_';

    /**
     * Fetch all categories from Categories API
     *
     * @return array|null
     */
    public function fetchCategories(): ?array
    {
        $cacheKey = $this->cachePrefix . 'categories';

        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            try {
                $response = Http::timeout(30)
                    ->retry(3, 100)
                    ->get("{$this->baseUrl}/categories");

                if ($response->successful()) {
                    Log::info('Categories fetched successfully');
                    return $response->json();
                }

                Log::warning('Failed to fetch categories', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (RequestException $e) {
                Log::error('Request exception while fetching categories', [
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Fetch category fields for a specific category
     *
     * @param int $categoryExternalId
     * @return array|null
     */
    public function fetchCategoryFields(int $categoryExternalId): ?array
    {
        $cacheKey = $this->cachePrefix . "category_fields_{$categoryExternalId}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($categoryExternalId) {
            try {
                $response = Http::timeout(30)
                    ->retry(3, 100)
                    ->get("{$this->baseUrl}/categoryFields", [
                        'categoryExternalIDs' => $categoryExternalId,
                        'includeWithoutCategory' => 'true',
                        'splitByCategoryIDs' => 'true',
                        'flatChoices' => 'true',
                        'groupChoicesBySection' => 'true',
                        'flat' => 'true',
                    ]);

                if ($response->successful()) {
                    Log::info("Category fields fetched for category {$categoryExternalId}");
                    return $response->json();
                }

                Log::warning("Failed to fetch fields for category {$categoryExternalId}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (RequestException $e) {
                Log::error("Request exception while fetching category fields", [
                    'category_id' => $categoryExternalId,
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }
}