<?php namespace Packback\Prices\Test;

use Packback\Prices\PriceCollector;
use Mockery as m;

class PriceCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = [
            'abebooks' => [
                'api_url' => 'http://search2.abebooks.com/search',
                'access_key' => '91dacdce-70dc-448d-9629-56d9fef89195'
            ]
        ];
        $this->collector = new PriceCollector($config);
    }

    public function testItAll()
    {
        $isbns = [
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
        ];
        $results = $this->collector->getAllPrices($isbns);
        print_r($results); exit;
    }

}
