<?php

namespace App\Http\Controllers\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductIndexResource;
use App\Http\Resources\Product\ProductShowResource;
use App\Models\Product;
use App\Services\FrequencyProductService;
use Illuminate\Http\Request;

class ProductInfoController extends Controller
{
    public function __construct(
        private FrequencyProductService $frequencyProductService
    ){}

    /**
     * Get all products.
     * Frequency count will be added if you search for some of these products.
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        $products = Product::search($request->search ?? null)->inRandomOrder()->paginate(20);
        if (isset($request->search) && count($products)) {
            $this->frequencyProductService->addFrequencyCount($products);
        }
        return ProductIndexResource::collection($products)->response()->getData(true);
    }

    /**
     * Get a product with similar products.
     *
     * @param Product $product
     * @return ProductShowResource
     */
    public function show(Product $product): ProductShowResource
    {
        return new ProductShowResource($product);
    }
}
