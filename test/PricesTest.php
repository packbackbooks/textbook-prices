<?php namespace Packback\Prices\Test;

use Packback\Prices\PriceCollector;
use Mockery as m;

class PriceCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->collector = new PriceCollector();
    }

    public function testItAll()
    {
        $results = $this->collector->getAllPrices();
        print_r($results); exit;
    }

}
