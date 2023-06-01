<?php

namespace App\Services;

class FrequencyProductService
{
    /**
     * Add a frequency count to the product you are looking for.
     *
     * @param object $data
     * @param bool $show
     * @return void
     */
    public function addFrequencyCount(object $data, bool $show = false): void
    {
        if ($show) {
            $data->update([
                'frequency' => $data->frequency + 1
            ]);
        } else {
            foreach ($data as $item) {
                $item->update([
                    'frequency' => $item->frequency + 1
                ]);
            }
        }
    }
}
