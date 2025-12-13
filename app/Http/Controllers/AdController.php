<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Requests\StoreAdRequest;
use App\Resources\AdCollection;
use App\Resources\AdResource;
use App\Services\AdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function __construct(
        protected AdService $adService
    ) {}

    /**
     * Store a newly created ad.
     *
     * POST /api/v1/ads
     *
     * @param StoreAdRequest $request
     * @return JsonResponse
     */
    public function store(StoreAdRequest $request): JsonResponse
    {
        $ad = $this->adService->createAd(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ad created successfully',
            'data' => new AdResource($ad),
        ], 201);
    }

    /**
     * Display a listing of the authenticated user's ads.
     *
     * GET /api/v1/my-ads
     *
     * @param Request $request
     * @return AdCollection
     */
    public function myAds(Request $request): AdCollection
    {
        $perPage = $request->input('per_page', 15);
        $perPage = min(max((int) $perPage, 1), 100); // Limit between 1 and 100

        $ads = $this->adService->getUserAds(
            $request->user()->id,
            $perPage
        );

        return new AdCollection($ads);
    }

    /**
     * Display the specified ad.
     *
     * GET /api/v1/ads/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $ad = $this->adService->getAdById($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Ad not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AdResource($ad),
        ]);
    }
}