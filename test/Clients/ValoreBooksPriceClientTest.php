<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\ValoreBooksPriceClient;
use Mockery as m;

class ValoreBooksPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.example.php';
        $this->client = new ValoreBooksPriceClient($config['valore']);
        $this->client->client = m::mock('GuzzleHttp\Client');
    }

    public function testTheWholeThing()
    {
        include 'config.php';
        $this->client = new ValoreBooksPriceClient($config['valore']);
        $isbns = [
            '9780000000071',
            '9780001203020',
            '9780007205707',
        ];
        // $results = $this->client->getPricesForIsbns($isbns);
        // print_r($results); exit;
    }

    public function testItAddsSalePriceToCollection()
    {
        $response = $this->generateResponse([
            'rental' => false,
            'sale' => true
        ]);
        $this->client->collection = [];

        $this->client->processSalePrices($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['product-code'], $book->isbn13 );
            $this->assertEquals($response['sale-offer']['price'], $book->price );
            $this->assertEquals('valorebooks', $book->retailer );
        }
    }

    public function testItAddsRentalPriceToCollection()
    {
        $response = $this->generateResponse([
            'rental' => true,
            'sale' => false
        ]);
        $this->client->collection = [];

        $this->client->processRentalPrices($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['product-code'], $book->isbn13 );
            if ($book->{'price'} === $response['rental-offer']['ninty-day-price']) {
                $this->assertEquals($response['rental-offer']['ninty-day-price'], $book->{'price'} );
            } elseif ($book->{'price'} === $response['rental-offer']['semester-price']) {
                $this->assertEquals($response['rental-offer']['semester-price'], $book->{'price'} );
            } else {
                $this->assertEquals($response['sale-offer']['price'], $book->price );
            }
            $this->assertEquals('valorebooks', $book->retailer );
        }
    }

    public function testItAddsRentalAndSalePriceToCollection()
    {
        $response = $this->generateResponse([
            'rental' => true,
            'sale' => true,
            'shipping' => true
        ]);
        $this->client->collection = [];

        $this->client->addPricesToCollection($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['product-code'], $book->isbn13 );
            if ($book->{'price'} === $response['rental-offer']['ninty-day-price']) {
                $this->assertEquals($response['rental-offer']['ninty-day-price'], $book->{'price'} );
            } elseif ($book->{'price'} === $response['rental-offer']['semester-price']) {
                $this->assertEquals($response['rental-offer']['semester-price'], $book->{'price'} );
            } else {
                $this->assertEquals($response['sale-offer']['price'], $book->price );
            }
            $this->assertEquals('valorebooks', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponse()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['product-code'], $book->isbn13 );
            $this->assertEquals($response['sale-offer']['price'], $book->price );
            $this->assertEquals('valorebooks', $book->retailer );
        }
    }

    public function testItDoesNotBuildPriceCollectionWithInvalidResponse()
    {
        $response = [uniqid()];
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);
        $this->assertEquals([], $this->client->collection);
    }

    public function testItCreatesPriceCollectionFromApi()
    {
        $guzzleResponse = m::mock();
        $isbn = uniqid();
        $xmlResponse = $this->generateXmlResponse();

        $this->client->client->shouldReceive('get')
            ->andReturn($guzzleResponse);
        $guzzleResponse->shouldReceive('getStatusCode')
            ->andReturn('200');
        $guzzleResponse->shouldReceive('getBody')
            ->with(true)
            ->andReturn($xmlResponse);

        $results = $this->client->getPricesForIsbns([$isbn]);
    }

    private function generateXmlResponse()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
             <product>
             <product-code>9780000000071</product-code>

             <sale-offer>
             <price>0.01</price>
             <condition>Like New</condition>
             <alternate-edition>false</alternate-edition>
             <link>http://www.valorebooks.com/listing?product_id=5791026&amp;sellerMarket_id=1196849012&amp;a_listing=false&amp;site_id=Og3W1I</link>
             <quantity>1</quantity>
             <shipping-options>
             <shipping>
             <method>Standard</method>
             <price-first>3.95</price-first>
             </shipping>
             <shipping>
             <method>Expedited</method>
             <price-first>6.95</price-first>
             </shipping>
             </shipping-options>
             </sale-offer></product><?xml version="1.0" encoding="UTF-8"?>
             <product>
             <product-code>9780001203020</product-code></product><?xml version="1.0" encoding="UTF-8"?>
             <product>
             <product-code>9780007205707</product-code>

             <sale-offer>
             <price>7.92</price>
             <condition>New</condition>
             <alternate-edition>false</alternate-edition>
             <link>http://www.valorebooks.com/listing?product_id=9504430&amp;sellerMarket_id=1400024232&amp;a_listing=false&amp;site_id=Og3W1I</link>
             <quantity>50</quantity>
             <shipping-options>
             <shipping>
             <method>Standard</method>
             <price-first>3.95</price-first>
             </shipping>
             </shipping-options>
             </sale-offer></product>';
    }

    private function generateResponse($options = [])
    {
        $book = [
            'product-code' => uniqid(),
        ];
        if (isset($options['sale']) && $options['sale'] === true) {
            $book['sale-offer'] = [
                'price' => '0.01',
                'alternate-edition' => 'false',
                'link' => 'http://www.valorebooks.com/listing?product_id=5791026&sellerMarket_id=1196849012&a_listing=false&site_id=Og3W1I',
                'quantity' => '1',
            ];
            if (isset($options['condition'])) {
                $book['sale-offer']['condition'] = $options['condition'];
            }
            if (isset($options['shipping']) && $options['shipping'] === true) {
                $book['sale-offer']['shipping-options'] = [
                    'shipping' => [
                        0 => [
                            'method' => 'Standard',
                            'price-first' => '3.95',
                        ],
                        1 => [
                            'method' => 'Expedited',
                            'price-first' => '6.95',
                        ],
                    ],
                ];
            }
        }
        if (isset($options['rental']) && $options['rental'] === true) {
            $book['rental-offer'] = [
                'ninty-day-price' => uniqid(),
                'semester-price' => uniqid(),
                'condition' => 'Like New',
                'alternate-edition' => 'false',
                'link' => 'http://www.valorebooks.com/listing?product_id=5791026&sellerMarket_id=1196849012&a_listing=false&site_id=Og3W1I',
                'quantity' => '1',
                'shipping-options' => [
                    'shipping' => [
                        1 => [
                            'method' => 'Expedited',
                            'price-first' => '6.95',
                        ],
                    ],
                ],
            ];
            if (isset($options['condition'])) {
                $book['rental-offer']['condition'] = $options['condition'];
            }
        }
        return $book;
    }
}
