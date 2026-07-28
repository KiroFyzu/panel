<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Billing;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\PricePackage;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class PackageController extends ClientApiController
{
    /**
     * Return all active price packages.
     */
    public function index(): JsonResponse
    {
        $packages = PricePackage::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get([
                'name', 'slug', 'description', 'price', 'old_price',
                'ram', 'cpu', 'disk',
            ]);

        return new JsonResponse($packages);
    }
}
