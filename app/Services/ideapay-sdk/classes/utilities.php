<?php

namespace Pay\Classes;

/**
 * Miscellaneous methods
 *
 * @package Pay\Classes
 */
class Utilities {
    /**
     * Format numerical value with two decimal points
     *
     * @param string|int|float $value
     * @return string
     */
    public static function format($value) {
        return number_format(floatval($value), 2, '.', '');
    }
}