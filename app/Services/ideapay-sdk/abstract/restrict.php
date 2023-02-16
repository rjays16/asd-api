<?php

namespace Pay\Abstracts;
use Pay\Interfaces\RestrictInterface;

/**
 * Base Mixin for various SDK classes.
 *
 * @package Pay\Abstracts
 */
abstract class Restrict implements RestrictInterface {

    /**
     * Supported class fields.
     *
     * @var array $fields
     */
    protected $fields = array();

    /**
     * Restrict constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = array()) {
        $this->_initialize_strings();

        foreach($args as $property => $value) {
            if(key_exists($property, $this->fields)) {
                $this->fields[$property]($value);
            }
        }
    }

    /**
     * _initialize_strings function placeholder.
     */
    public function _initialize_strings() {
        // do nothing
    }

    /**
     * Assign value to an existing class field.
     *
     * Will trigger callback function change() toggling various field configuration.
     *
     * @param string $property
     * @param array $args
     */
    public function __call($property, $args) {
        if(key_exists($property, $this->fields)) {
            if(is_string($args[0])) {
                $this->fields[$property]($args[0]);

                if(method_exists($this, 'change')) {
                    call_user_func(array($this, 'change'), $property);
                }
            }
        }
    }

    /**
     * Pull string value of field.
     *
     * Used in succession after _extract().
     *
     * @param array $carry
     * @param array $item
     * @return mixed
     */
    private function _combine($carry, $item) {
        $carry[$item[0]] = strval($item[1]);

        return $carry;
    }


    /**
     * Extract field key & field object.
     *
     * Used in get().
     *
     * @param array $item
     * @param string $k
     * @return array
     * @throws \Exception
     */
    private function _extract($item, $k) {
        try {
            $item->is_valid();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . ' for `' . $k . '`');
        }

        return [$k, $item];
    }

    /**
     * Retrieve fields as processed array.
     *
     * @return array
     */
    public function get() {
        return array_filter(array_reduce(array_map(array($this, '_extract'), $this->fields, array_keys($this->fields)), array($this, '_combine'), array()));
    }
}