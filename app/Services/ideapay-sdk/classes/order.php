<?php

namespace Pay\Classes;

/**
 * Order information class
 *
 * @package Pay\Classes
 */
class Order {

    /**
     * @var array $items
     */
    private $items = array();

    /**
     * @var int $subtotal
     */
    private $subtotal = 0;

    /**
     * Order constructor.
     *
     * @param array $items
     */
    public function __construct($items = array()) {
        foreach($items as $item) {
            if($item instanceof Item) {
                $this->add($item);
            }
        }
    }

    /**
     * Add item to order
     *
     * @param Item $item
     */
    public function add(Item $item) {
        // add value to subtotal
        $item_info = $item->get();
        $this->subtotal += $item_info['value'] * $item_info['quantity'];

        $this->items[] = $item;
    }

    /**
     * Fetch items in order
     *
     * @return array
     */
    private function _items() {
        $items = array();

        foreach($this->items as $item) {
            $items[] = $item->get();
        }

        return $items;
    }

    /**
     * Fetch order information
     *
     * @return array
     */
    public function get() {
        return array(
            'items'     =>  $this->_items(),
            'subtotal'  =>  Utilities::format($this->subtotal)
        );
    }
}