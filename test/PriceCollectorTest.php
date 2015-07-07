<?php namespace Packback\Prices\Test;

use Packback\Prices\PriceCollector;
use Mockery as m;

class PriceCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
        $this->collector = new PriceCollector($config);
    }

    public function testItAll()
    {
        $isbns = [
            '9780000002006',
            '9780000336040',
            '9780002005555',
            '9780000000644',
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
        ];
        // $results = $this->collector->getCheggPrices($isbns);
        // print_r($results); exit;
    }

}
