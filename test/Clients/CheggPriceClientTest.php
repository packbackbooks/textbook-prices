<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\CheggPriceClient;
use Mockery as m;

class CheggPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.example.php';
        $this->client = new CheggPriceClient($config['chegg']);
        $this->client->client = m::mock('GuzzleHttp\Client');
    }

    public function testItBuildsPriceCollectionFromValidResponse()
    {
        $response = $this->generateResponse(['shipping' => true]);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Items']['Item']['EAN'], $book->isbn13 );
            $this->assertEquals($response['Items']['Item']['Terms']['Term'][0]['Price'], $book->price );
            $this->assertEquals('chegg', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWithoutShipping()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Items']['Item']['EAN'], $book->isbn13 );
            $this->assertEquals($response['Items']['Item']['Terms']['Term'][0]['Price'], $book->price );
            $this->assertEquals('chegg', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWith60DaysTerm()
    {
        $response = $this->generateResponse(['term' => 'rent for 60 days']);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Items']['Item']['EAN'], $book->isbn13 );
            $this->assertEquals($response['Items']['Item']['Terms']['Term'][0]['Price'], $book->price );
            $this->assertEquals('chegg', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWith45DaysTerm()
    {
        $response = $this->generateResponse(['term' => 'rent for 45 days']);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Items']['Item']['EAN'], $book->isbn13 );
            $this->assertEquals($response['Items']['Item']['Terms']['Term'][0]['Price'], $book->price );
            $this->assertEquals('chegg', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWith30DaysTerm()
    {
        $response = $this->generateResponse(['term' => 'rent for 30 days']);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Items']['Item']['EAN'], $book->isbn13 );
            $this->assertEquals($response['Items']['Item']['Terms']['Term'][0]['Price'], $book->price );
            $this->assertEquals('chegg', $book->retailer );
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

    private function generateResponse($options = [])
    {
        $book = [
            'EAN' => uniqid(),
            'Terms' => [
                'Term' => [
                    0 => [
                        'Price' => rand(10,200),
                        'Term' => isset($options['term']) ? $options['term'] : 'new',
                    ],
                ],
            ],
        ];
        if (isset($options['shipping']) && $options['shipping'] == true) {
            $book['ShippingPrices'] = [
                'ShippingPrice' => [
                    0 => [
                        'Cost_first' => rand(10,200),
                    ],
                ]
            ];
        }
        $results['Items']['Item'] = $book;
        return $results;
    }
}
