<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimilarProductService
{
    private string $name;

    /**
     * Get similar products.
     *
     * @param $name
     * @return Collection
     */
    public function getSimilarProducts($name): Collection
    {
        $this->name = $name;

        $generalWords = $this->getGeneralWords();
        // TODO change
        $products = Product::where('name', 'LIKE', '%' . $generalWords[0] . '%')
            ->select('id', 'frequency')
            ->get();

        if ($products->count() > 15) {
            $totalWeight = 0;
            $cumulativeWeights = [];

            foreach ($products as $product) {
                $frequency = $product->frequency ? 1 / $product->frequency : 0;
                $totalWeight += $frequency;
                $cumulativeWeights[] = $totalWeight;
            }

            $randomProducts = collect();
            $selectedCount = 0;
            $productCount = count($products);

            while ($selectedCount < 15 && $selectedCount < $productCount) {
                $randomNumber = mt_rand() / mt_getrandmax() * $totalWeight;

                $selectedProductIndex = $this->binarySearch($cumulativeWeights, $randomNumber);
                $selectedProductIndex2 = $selectedProductIndex + 100;

                if (is_int($selectedProductIndex / 27)) {
                    $order = 'ASC';
                    var_dump($selectedProductIndex);
                }

                $selectedProduct = Product::whereBetween('id', [$selectedProductIndex, $selectedProductIndex2])
                    ->orderBy('frequency', $order ?? 'DESC')
                    ->first();

                if (!$randomProducts->contains('id', $selectedProduct->id)) {
                    $randomProducts->push($selectedProduct);
                    $selectedCount++;
                }
            }
        }

        return $randomProducts ?? collect();
    }

    /**
     * Binary search.
     *
     * @param $weights
     * @param $target
     * @return int
     */
    private function binarySearch($weights, $target): int
    {
        $left = 0;
        $right = count($weights) - 1;

        while ($left < $right) {
            $mid = (int)(($left + $right) / 2);

            if ($weights[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid;
            }
        }

        return $left;
    }

    /**
     * Get all general words of a string.
     *
     * @return Collection
     */
    private function getGeneralWords(): Collection
    {
        $words = Str::lower($this->name);
        $words = Str::replace(['-', '_'], ' ', $words);
        return Str::of($words)->explode(' ')->reject(function ($word) {
            return Str::length($word) <= 2;
        });
    }
}
