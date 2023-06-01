<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimilarProductService
{
    /**
     * Show product id.
     *
     * @var int
     */
    private int $productId;

    /**
     * Show product name.
     *
     * @var string
     */
    private string $name;

    /**
     * All products filtered by name.
     *
     * @var Collection
     */
    private Collection $products;

    /**
     * Get similar products.
     *
     * @param int $id
     * @param string $name
     * @return Collection
     */
    public function getSimilarProducts(int $id, string $name): Collection
    {
        $this->productId = $id;
        $this->name = $name;
        $this->products = $this->getProductsByNameFilter();

        if (!$this->products->count()) {
            $similarProducts = $this->getRandomProducts(15);
        } elseif  ($this->products->count() <= 15) {
            $similarProducts = Product::whereIn('id', $this->products->pluck('id'))->get();

            if ($this->products->count() < 15) {
                $randomProducts = $this->getRandomProducts(15 - $this->products->count());
                $similarProducts = $randomProducts->merge($similarProducts);
            }
        } else {
            $similarProducts = $this->getProductsByFrequency();
        }

        return $similarProducts;
    }

    /**
     * Get products by name.
     *
     * @return mixed
     */
    private function getProductsByNameFilter(): mixed
    {
        $words = Str::lower($this->name);
        $words = Str::replace(['-', '_'], ' ', $words);
        /**
         * ТЗ: (предлоги, союзы и частицы не учитываются)
         * We can reject when in_array($word, ['and', 'or', 'и', 'или', etc]).
         * But I reject when Str::length($word) <= 2 because the data in the db was added by the factory.
         */
        $generalWords =  Str::of($words)->explode(' ')->reject(function ($word) {
            return Str::length($word) <= 2;
        });

        return Product::where('id', '!=', $this->productId)
            ->where(function ($query) use ($generalWords) {
                foreach ($generalWords as $word) {
                        $query->orWhere('name', 'like', "%{$word}%");
                        $query->orWhere('name', 'like', "%{$word}_%");
                        $query->orWhere('name', 'like', "%{$word}'%");
                    }
                })
            ->select('id', 'frequency')
            ->get();
    }

    /**
     * Get products by frequency.
     *
     * @return Collection
     */
    private function getProductsByFrequency(): Collection
    {
        /**
         * 14/100
         * Chance to get low frequency products.
         */
        $lowFrequencyChance = (int)(Product::all()->count() / 14);
        $productsPluckId = $this->products->pluck('id')->toArray();

        $totalFrequency = 0;
        $cumulativeFrequency = [];

        foreach ($this->products as $product) {
            $frequency = $product->frequency ? (1 / $product->frequency) : 0;
            $totalFrequency += $frequency;
            $cumulativeFrequency[] = $totalFrequency;
        }

        $similarProducts = collect();
        $selectedCount = 0;

        while ($selectedCount < 15 && $selectedCount < count($this->products)) {
            $randomNumber = mt_rand() / mt_getrandmax() * $totalFrequency;

            $selectedProductIndex = $this->binarySearch($cumulativeFrequency, $randomNumber);
            $selectedProductIndex2 = $selectedProductIndex + 200;

            $productIds = array_filter(
                $productsPluckId,
                function ($value) use($selectedProductIndex, $selectedProductIndex2) {
                    return ($value >= $selectedProductIndex && $value <= $selectedProductIndex2);
                }
            );

            $selectedProduct = Product::whereIn('id', $productIds)
                ->orderBy('frequency', is_int($selectedProductIndex / $lowFrequencyChance) ? 'ASC' : 'DESC')
                ->first();

            if (!is_null($selectedProduct) && !$similarProducts->contains('id', $selectedProduct->id)) {
                $similarProducts->push($selectedProduct);
                $selectedCount++;
            }
        }

        return $similarProducts;
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
     * Get random products.
     *
     * @param $count
     * @return mixed
     */
    private function getRandomProducts($count): mixed
    {
        return Product::whereNotIn('id', $this->products->pluck('id'))
            ->where('id', '!=', $this->productId)
            ->inRandomOrder()
            ->take($count)
            ->get();
    }
}
