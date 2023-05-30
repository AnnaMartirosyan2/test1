<?php

namespace App\Services;

class FrequencyProductService
{
    /**
     * Add a frequency count to the product you are looking for.
     *
     * @param $data
     * @return void
     */
    public function addFrequencyCount($data): void
    {
        foreach ($data as $item) {
            $item->update([
                'frequency' => $item->frequency + 1
            ]);
        }
    }
}
