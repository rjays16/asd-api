<?php

namespace Pay\DataType;

/**
 * Emulate a custom string data type with varying restrictions
 *
 * @package Pay\DataType
 */
class LockedString {
    private $value = '';
    private $max_length = 0;
    private $restrict = null;
    private $not_empty = false;

    public $restrict_message = null;

    /**
     * LockedString constructor.
     *
     * @param int $max_length
     * @param null $regular_expression
     * @param bool $not_empty
     */
    public function __construct($max_length = 0, $regular_expression = null, $not_empty=false) {
        $this->max_length = $max_length;
        $this->restrict = $regular_expression;
        $this->not_empty = $not_empty;
    }

    /**
     * Apply locked string maximum length
     *
     * @param int $nv
     * @param bool $invoke
     * @return $this
     * @throws \Exception
     */
    public function max_length($nv = 0, $invoke = true) {
        if($this->max_length !== $nv) {
            $this->max_length = $nv;

            if($invoke) {
                $this->__invoke($this->value);
            }
        }

        return $this;
    }

    /**
     * Toggle whether locked can be empty
     *
     * @param bool $nv
     * @param bool $invoke
     * @return $this
     * @throws \Exception
     */
    public function not_empty($nv = false, $invoke = true) {
        if($this->not_empty !== $nv) {
            $this->not_empty = $nv;

            if($invoke) {
                $this->__invoke($this->value);
            }
        }

        return $this;
    }

    /**
     * Apply new restriction configuration
     *
     * Must be a regular expression pattern
     *
     * @param null $nv
     * @param bool $invoke
     * @return $this
     * @throws \Exception
     */
    public function restrict($nv = null, $invoke = true) {
        if($this->restrict !== $nv) {
            $this->restrict = $nv;

            if($invoke) {
                $this->__invoke($this->value);
            }
        }

        return $this;
    }

    /**
     * Execute value checks
     *
     * @param $new_string
     * @throws \Exception
     */
    public function __invoke($new_string) {
        $trimmed = preg_replace("/\s/", '', $new_string);

        if(! is_string($new_string) && is_numeric($new_string)) {
            $new_string = strval($new_string);
        }

        if(! is_string($new_string)) {
            throw new \Exception('Invalid value data type: ' . gettype($new_string));
        } else if($this->not_empty) {
            if($trimmed === '') {
                throw new \Exception('New string value cannot be empty');
            }
        } else if($this->max_length !== 0 && strlen($new_string) > $this->max_length) {
            throw new \Exception('New string value `' . $new_string . '` exceeds maximum length ' . $this->max_length);
        } else if(is_string($this->restrict) && $this->restrict !== '' && $trimmed !== '' && preg_match('/' . $this->restrict . '/', $new_string) !== 1) {
            if($this->restrict_message) {
                throw new \Exception('New string value `' . $new_string . '` advise: ' . $this->restrict_message);
            } else {
                throw new \Exception('New string value `' . $new_string . '` does not follow restriction ' . substr($this->restrict, 1, -1));
            }
        }

        $this->value = $new_string;
    }

    /**
     * Check if current value passes requirements
     *
     * @return bool
     * @throws \Exception
     */
    public function is_valid() {
        try {
            $this->__invoke($this->value);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Return string value
     *
     * @return string
     */
    public function __toString() {
        return $this->value;
    }
}