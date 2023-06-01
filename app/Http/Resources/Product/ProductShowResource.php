<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $similarProductService = \App::make('App\Services\SimilarProductService');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'frequency' => $this->frequency,
            'similar_products' => $similarProductService->getSimilarProducts($this->id, $this->name)
        ];
    }
}
