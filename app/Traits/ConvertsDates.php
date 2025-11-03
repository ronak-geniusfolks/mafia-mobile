<?php

namespace App\Traits;

use Carbon\Carbon;

trait ConvertsDates
{
    /**
     * Convert date from d/m/Y format to Y-m-d format.
     *
     * @param string|null $date
     * @return string|null
     */
    protected function convertDateFormat(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $date)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                return $date;
            }
        }

        return $date;
    }
}

