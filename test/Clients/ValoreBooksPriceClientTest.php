<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\ValoreBooksPriceClient;
use Mockery as m;

class ValoreBooksPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
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

    /*
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
        return '<?xml version="1.0" encoding="utf-8"?>
            <CheggAPIResponse>
                <Items><Item><BookInfo>
                    <Title>Abbreviated Injury Scale (Ais) 1990 - Update 98</Title>
                    <EditionNumber>1</EditionNumber>
                    <EditionType></EditionType>
                    <PubDate></PubDate>
                    <PubName>Association for the Advancement of Baltic Studies</PubName>
                    <ListPrice>235.29</ListPrice>
                    <Authors/>
                    <ImageThumb>http://c.cheggcdn.com/covers2/imagena.gif</ImageThumb>
                    <ImageSmall>http://c.cheggcdn.com/covers2/imagenalarge.gif</ImageSmall>
                    <ImageMedium>http://c.cheggcdn.com/covers2/imagenalarge.gif</ImageMedium>
                    <ImageLarge>http://c.cheggcdn.com/covers2/imagenalarge.gif</ImageLarge></BookInfo><ISBN>0000002003</ISBN><EAN>9780000002006</EAN><Terms><Term><Price>9.99</Price><Id>157</Id><Term>SEMESTER</Term><Name>Semester Rental</Name><Term_days>164</Term_days><Due_date>2015-12-18</Due_date><Orig_price>9.99</Orig_price><Discount_info>0</Discount_info><Pid>LBP-980139|867189a2-ef39-490e-9ed6-c42624bbdd16|1</Pid></Term><Term><Price>9.99</Price><Id>157</Id><Term>QUARTER</Term><Name>Quarter Rental</Name><Term_days>164</Term_days><Due_date>2015-12-18</Due_date><Orig_price>9.99</Orig_price><Discount_info>0</Discount_info><Pid>LBP-980139|867189a2-ef39-490e-9ed6-c42624bbdd16|1</Pid></Term><Term><Price>9.99</Price><Id>157</Id><Term>SUMMER</Term><Name>Summer Rental</Name><Term_days>164</Term_days><Due_date>2015-12-18</Due_date><Orig_price>9.99</Orig_price><Discount_info>0</Discount_info><Pid>LBP-980139|867189a2-ef39-490e-9ed6-c42624bbdd16|1</Pid></Term></Terms><Renting>1</Renting><ShippingPrices><ShippingPrice><Method_name>Ground Shipping</Method_name><Cost_first>5.99</Cost_first><Cost_each>2.99</Cost_each><Guarantee_date>07-15-2015</Guarantee_date></ShippingPrice><ShippingPrice><Method_name>Standard</Method_name><Cost_first>4.99</Cost_first><Cost_each>3.99</Cost_each><Guarantee_date>07-17-2015</Guarantee_date></ShippingPrice><ShippingPrice><Method_name>UPS 3rd Day</Method_name><Cost_first>5.99</Cost_first><Cost_each>2.99</Cost_each><Guarantee_date>07-11-2015</Guarantee_date></ShippingPrice><ShippingPrice><Method_name>UPS 2nd Day</Method_name><Cost_first>10.99</Cost_first><Cost_each>5.99</Cost_each><Guarantee_date>07-10-2015</Guarantee_date></ShippingPrice><ShippingPrice><Method_name>UPS Next Day Air</Method_name><Cost_first>16.99</Cost_first><Cost_each>10.99</Cost_each><Guarantee_date>07-08-2015</Guarantee_date>
                    </ShippingPrice></ShippingPrices>
                    </Item></Items>
            </CheggAPIResponse>';
    }
    */

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
