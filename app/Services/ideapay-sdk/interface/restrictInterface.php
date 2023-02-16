<?php

namespace Pay\Interfaces;

/**
 * Interface RestrictInterface
 *
 * @package Pay\Interfaces
 */
interface RestrictInterface {
    /**
     * @return mixed
     */
    public function _initialize_strings();

    /**
     * @param string $property
     * @param array $args
     */
    public function __call($property, $args);

    /**
     * @return array
     */
    public function get();
}