<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetPurchase;
use App\Models\MasterAssetsCategory;
use App\Models\MasterAssetsLocation;

class AssetNumberGenerator
{
    /**
     * Generate nomor aset dengan format:
     * aaaa.bb.cc.dddd.ee.ff.gggg.x
     *
     * aaaa = kode kategori
     * bb = 00 (fixed)
     * cc = kode lokasi
     * dddd = Nomor urut permintaan/faktur per tahun (sama untuk 1 permintaan)
     * ee = tanggal
     * ff = bulan
     * gggg = tahun
     * x = suffix (a, b, c, dst) untuk item dalam 1 permintaan dengan quantity > 1
     */
    public static function generate(
        string $categoryId,
        string $locationId,
        \DateTime|string $purchaseDate,
        int $itemIndex = 1,
        ?int $sequentialNumber = null
    ): string {
        // Get category code
        $category = MasterAssetsCategory::find($categoryId);
        $categoryCode = $category ? strtoupper($category->kode) : 'UNKN';

        // Get location code
        $location = MasterAssetsLocation::find($locationId);
        $locationCode = $location ? str_pad($location->kode, 2, '0', STR_PAD_LEFT) : '00';

        // Parse date
        if (is_string($purchaseDate)) {
            $purchaseDate = new \DateTime($purchaseDate);
        }

        $day = $purchaseDate->format('d');
        $month = $purchaseDate->format('m');
        $year = $purchaseDate->format('Y');

        // Get sequential number for the year (if not provided)
        if ($sequentialNumber === null) {
            $sequentialNumber = self::getYearlySequentialNumber($year);
        }

        // Convert item index to letter (1=a, 2=b, etc.)
        $suffix = self::indexToLetter($itemIndex);

        // Build the asset number
        $assetNumber = sprintf(
            '%s.00.%s.%04d.%s.%s.%s.%s',
            $categoryCode,
            $locationCode,
            $sequentialNumber,
            $day,
            $month,
            $year,
            $suffix
        );

        return $assetNumber;
    }

    /**
     * Generate multiple asset numbers for a request with quantity > 1
     * All items share the same sequential number (same faktur/permintaan)
     */
    public static function generateMultiple(
        string $categoryId,
        string $locationId,
        \DateTime|string $purchaseDate,
        int $quantity
    ): array {
        $assetNumbers = [];

        // Parse date for getting year
        if (is_string($purchaseDate)) {
            $date = new \DateTime($purchaseDate);
        } else {
            $date = $purchaseDate;
        }

        // Get ONE sequential number for this entire request/faktur
        $sequentialNumber = self::getYearlySequentialNumber($date->format('Y'));

        for ($i = 1; $i <= $quantity; $i++) {
            $assetNumbers[] = self::generate($categoryId, $locationId, $purchaseDate, $i, $sequentialNumber);
        }

        return $assetNumbers;
    }

    /**
     * Get the next sequential number for purchases in a given year
     * Based on unique faktur/permintaan, not individual items
     */
    private static function getYearlySequentialNumber(string $year): int
    {
        // Count distinct assetrequest_id in purchases this year
        $purchaseCount = AssetPurchase::whereYear('purchase_date', $year)
            ->distinct('assetrequest_id')
            ->count('assetrequest_id');

        return $purchaseCount + 1;
    }

    /**
     * Convert numeric index to letter (1=a, 2=b, ... 26=z, 27=aa, etc.)
     */
    private static function indexToLetter(int $index): string
    {
        $result = '';

        while ($index > 0) {
            $index--;
            $result = chr(97 + ($index % 26)) . $result;
            $index = intval($index / 26);
        }

        return $result ?: 'a';
    }

    /**
     * Preview generated asset numbers without saving
     */
    public static function preview(
        string $categoryId,
        string $locationId,
        \DateTime|string $purchaseDate,
        int $quantity
    ): array {
        return self::generateMultiple($categoryId, $locationId, $purchaseDate, $quantity);
    }
}
