<?php

namespace App\Helpers;

class NumberToWords
{
    /**
     * Convert currency amount to Indian Words format
     * e.g. 2499.00 -> Rupees Two Thousand Four Hundred Ninety-Nine Only
     */
    public static function convert(float $amount): string
    {
        $amount = round($amount, 2);
        $integerPart = (int) floor($amount);
        $paise = (int) round(($amount - $integerPart) * 100);

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];

        if ($integerPart === 0) {
            $result = 'Zero Rupees';
        } else {
            $result = 'Rupees ' . self::convertIndianGroup($integerPart, $words);
        }

        if ($paise > 0) {
            $result .= ' and ' . self::convertIndianGroup($paise, $words) . ' Paise';
        }

        return trim($result) . ' Only';
    }

    private static function convertIndianGroup(int $num, array $words): string
    {
        if ($num === 0) {
            return '';
        }

        $crores = (int) floor($num / 10000000);
        $remainder = $num % 10000000;

        $lakhs = (int) floor($remainder / 100000);
        $remainder = $remainder % 100000;

        $thousands = (int) floor($remainder / 1000);
        $remainder = $remainder % 1000;

        $hundreds = (int) floor($remainder / 100);
        $units = $remainder % 100;

        $parts = [];

        if ($crores > 0) {
            $parts[] = self::convertIndianGroup($crores, $words) . ' Crore';
        }
        if ($lakhs > 0) {
            $parts[] = self::convertTwoDigits($lakhs, $words) . ' Lakh';
        }
        if ($thousands > 0) {
            $parts[] = self::convertTwoDigits($thousands, $words) . ' Thousand';
        }
        if ($hundreds > 0) {
            $parts[] = $words[$hundreds] . ' Hundred';
        }
        if ($units > 0) {
            $parts[] = self::convertTwoDigits($units, $words);
        }

        return implode(' ', array_filter($parts));
    }

    private static function convertTwoDigits(int $n, array $words): string
    {
        if ($n < 20) {
            return $words[$n];
        }
        $tens = (int) floor($n / 10) * 10;
        $units = $n % 10;
        return $words[$tens] . ($units > 0 ? ' ' . $words[$units] : '');
    }
}
