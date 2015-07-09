<?php namespace Packback\Prices\Test;

use Packback\Prices\PriceCollector;
use Mockery as m;

class PriceCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
        $this->collector = new PriceCollector($config);
        $this->collector->abebooks = m::mock('Packback\Prices\Clients\AbeBooksPriceClient');
        $this->collector->amazon = m::mock('Packback\Prices\Clients\AmazonPriceClient');
        $this->collector->chegg = m::mock('Packback\Prices\Clients\CheggPriceClient');
        $this->collector->valore = m::mock('Packback\Prices\Clients\ValoreBooksPriceClient');
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
        $this->collector->abebooks->shouldReceive('getPricesForIsbns')
            ->with($isbns)
            ->once()
            ->andReturn([
                0 => $this->generatePriceResult()
            ]);
        $this->collector->amazon->shouldReceive('getPricesForIsbns')
            ->with($isbns)
            ->once()
            ->andReturn([
                0 => $this->generatePriceResult()
            ]);
        $this->collector->chegg->shouldReceive('getPricesForIsbns')
            ->with($isbns)
            ->once()
            ->andReturn([
                0 => $this->generatePriceResult()
            ]);
        $this->collector->valore->shouldReceive('getPricesForIsbns')
            ->with($isbns)
            ->once()
            ->andReturn([
                0 => $this->generatePriceResult()
            ]);

        $results = $this->collector->getAllPrices($isbns);
    }

    private function generatePriceResult()
    {
        return [
            'condition' => uniqid(),
            'isbn13' => uniqid(),
            'price' => uniqid(),
            'shipping_price' => uniqid(),
            'url' => uniqid(),
            'retailer' => uniqid(),
            'term' => uniqid(),
        ];
    }

}
