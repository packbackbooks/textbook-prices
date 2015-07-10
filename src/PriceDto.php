<?php namespace Packback\Prices;

class PriceDto
{
    /**
     * ISBN13 for this price
     *
     * @var string
     */
    public $isbn13;

    /**
     * Price amount
     *
     * @var string
     */
    public $price;

    /**
     * Shipping price amount
     *
     * @var string
     */
    public $shipping_price;

    /**
     * Url of store page to purchase book at this price
     *
     * @var string
     */
    public $url;

    /**
     * Retailer selling at this price
     *
     * @var string
     */
    public $retailer;

    /**
     * Term at which this price is available
     *
     * @var string
     */
    public $term;

    /**
     * Condition of the product
     *
     * @var string
     */
    public $condition;

    /**
     * Magic method to get protected property, if exists
     *
     * @param  string $name
     *
     * @return mixed
     * @throws OutOfRangeException
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }
        return $this->{$name};
    }

    /**
     * Magic method to set protected property, if exists
     *
     * @param  string $name
     * @param  mixed $value
     *
     * @return $this
     * @throws OutOfRangeException
     */
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }
        $this->{$name} = $value;
        return $this;
    }

    /**
     * Magic method to check if property is set
     *
     * @param  string $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return (property_exists($this, $name));
    }
}
