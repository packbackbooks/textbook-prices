<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\AbeBooksPriceClient;
use Mockery as m;

class AbeBooksPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
        $this->client = new AbeBooksPriceClient($config['abebooks']);
        $this->client->client = m::mock('GuzzleHttp\Client');
    }

    public function testItBuildsPriceCollectionFromValidResponse()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Book'][$key]['isbn13'], $book->isbn13 );
            $this->assertEquals($response['Book'][$key]['listingPrice'], $book->price );
            $this->assertEquals($response['Book'][$key]['firstBookShipCost'], $book->shipping_price );
            $this->assertEquals('abebooks', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWithNewCondition()
    {
        $response = $this->generateResponse(1, ['condition' => 'new book']);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Book'][$key]['isbn13'], $book->isbn13 );
            $this->assertEquals($response['Book'][$key]['listingPrice'], $book->price );
            $this->assertEquals($response['Book'][$key]['firstBookShipCost'], $book->shipping_price );
            $this->assertEquals('abebooks', $book->retailer );
        }
    }

    public function testItBuildsPriceCollectionFromValidResponseWithEmptyCondition()
    {
        $response = $this->generateResponse(1, ['condition' => '']);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);
        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response['Book'][$key]['isbn13'], $book->isbn13 );
            $this->assertEquals($response['Book'][$key]['listingPrice'], $book->price );
            $this->assertEquals($response['Book'][$key]['firstBookShipCost'], $book->shipping_price );
            $this->assertEquals('abebooks', $book->retailer );
        }
    }

    public function testItDoesNotBuildPriceCollectionFromInvalidResponse()
    {
        $response = [];
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        $this->assertEquals([], $this->client->collection);
    }

    public function testItCreatesPriceCollectionFromApi()
    {
        $guzzleResponse = m::mock();
        $isbn = uniqid();
        $xmlResponse = $this->generateXmlResponse($isbn);

        $this->client->client->shouldReceive('get')
            ->andReturn($guzzleResponse);
        $guzzleResponse->shouldReceive('getStatusCode')
            ->andReturn('200');
        $guzzleResponse->shouldReceive('getBody')
            ->with(true)
            ->andReturn($xmlResponse);

        $results = $this->client->getPricesForIsbns([$isbn]);
        foreach ($results as $result) {
            $this->assertEquals($isbn, $result->isbn13);
        }
    }

    private function generateResponse($count = 3, $params = [])
    {
        $cc = 0;
        while ($cc < $count) {
            $book = [
                'bookId' => uniqid(),
                'isbn10' => uniqid(),
                'isbn13' => uniqid(),
                'quantity' => rand(1,10),
                'vendorCurrency' => uniqid(),
                'listingPrice' => uniqid(),
                'firstBookShipCost' => uniqid(),
                'extraBookShipCost' => uniqid(),
                'minShipDays' => uniqid(),
                'maxShipDays' => uniqid(),
                'totalListingPrice' => uniqid(),
                'listingUrl' => uniqid(),
                'author' => uniqid(),
                'title' => uniqid(),
                'publisherName' => uniqid(),
                'vendorName' => uniqid(),
                'vendorLocation' => uniqid(),
                'vendorId' => uniqid(),
                'sellerRating' => uniqid(),
                'keywords' => uniqid(),
                'bindingType' => uniqid(),
            ];
            if (isset($params['condition'])) {
                $book['listingCondition'] = $params['condition'];
            } else {
                $book['listingCondition'] = 'Very Good';
                $book['itemCondition'] = 'Very Good';
            }
            $results['Book'][] = $book;
            $cc++;
        }
        return $results;
    }

    private function generateXmlResponse($isbn, $count = 2)
    {
        $cc = 0;
        $results = '<?xml version="1.0" encoding="UTF-8"?><searchResults><resultCount>495144</resultCount>';
        while ($cc < $count) {
            $results .= $this->generateXmlBookObject($isbn);
            $cc++;
        }
        $results .= '</searchResults>';
        return $results;
    }

    private function generateXmlBookObject($isbn)
    {
        return '<Book>
            <bookId>959356004</bookId>
            <isbn10>025536251X</isbn10>
            <isbn13>'.$isbn.'</isbn13>
            <listingCondition>NOT NEW BOOK</listingCondition>
            <itemCondition>Very Good</itemCondition>
            <quantity>1</quantity>
            <vendorCurrency>GBP</vendorCurrency>
            <listingPrice>1.0</listingPrice>
            <firstBookShipCost>2.24</firstBookShipCost>
            <extraBookShipCost>0.0</extraBookShipCost>
            <minShipDays>0</minShipDays>
            <maxShipDays>0</maxShipDays>
            <totalListingPrice>3.24</totalListingPrice>
            <listingUrl>www.abebooks.com/servlet/BookDetailsPL?bi=959356004&amp;cm_ven=sws&amp;cm_cat=sws&amp;cm_pla=sws&amp;cm_ite=959356004</listingUrl>
            <author>Ray Robinson</author>
            <title>Efficiency and the National Health Service: A Case for Internal Markets (Health)</title>
            <publisherName>Institute of Economic Affairs (I</publisherName>
            <vendorName>The Orchard Bookshop.</vendorName>
            <vendorLocation>Hayes, United Kingdom</vendorLocation>
            <vendorId>676907</vendorId>
            <sellerRating>5</sellerRating>
            <keywords>SUBJECTS</keywords>
            <bindingType>S</bindingType>
        </Book>';
    }

}
