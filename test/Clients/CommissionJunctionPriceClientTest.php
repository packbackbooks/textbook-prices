<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\CommissionJunctionPriceClient;
use Mockery as m;

class CommissionJunctionPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.example.php';
        $this->retailer = 'bookrenter';
        $cj_config = array_merge($config['cj'], $config[$this->retailer]);

        $this->client = new CommissionJunctionPriceClient($cj_config);
        $this->client->client = m::mock('CROSCON\CommissionJunction\Client');
    }

    public function testItBuildsPriceCollectionFromSingleValidResponse()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response->products->product->isbn, $book->isbn13 );
            $this->assertEquals($response->products->product->price, $book->price );
            $this->assertEquals($response->products->product->{'buy-url'}, $book->url );
        }
    }

    public function testItBuildsPriceCollectionFromMultipleValidResponse()
    {
        $count = rand(2,5);
        $response = $this->generateResponse($count);
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($response->products->product[$key]->isbn, $book->isbn13 );
            $this->assertEquals($response->products->product[$key]->price, $book->price );
            $this->assertEquals($response->products->product[$key]->{'buy-url'}, $book->url );
        }
        $this->assertEquals($count, count($this->client->collection));
    }

    public function testItDoesNotBuildPriceCollectionFromInvalidResponse()
    {
        $response = [];
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($response);

        $this->assertEquals([], $this->client->collection);
    }

    public function testResponseHasProductsWhenHasProducts()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->responseHasProducts($response);

        $this->assertTrue($results);
    }

    public function testResponseDoesNotHaveProductsWhenNoProducts()
    {
        $response = (object) [
            'products' => (object) [
                '@attributes' => (object) [

                ],
                'product' => (object) [

                ],
            ],
        ];
        $this->client->collection = [];

        $results = $this->client->responseHasProducts($response);

        $this->assertFalse($results);
    }

    public function testItCreatesPriceCollectionFromApi()
    {
        $isbn = uniqid();
        $clientResponse = $this->generateResponse(1, ['isbn' => $isbn]);

        $this->client->client->shouldReceive('productSearch')
            ->andReturn($clientResponse);

        $this->client->getPricesForIsbns([$isbn]);

        foreach ($this->client->collection as $key => $book) {
            $this->assertEquals($clientResponse->products->product->isbn, $book->isbn13 );
        }
    }

    public function testItFailsToCreatePriceCollectionFromApiWithBadResponse()
    {
        $isbn = uniqid();

        $this->client->client->shouldReceive('productSearch')
            ->andThrow('Exception');

        $this->client->getPricesForIsbns([$isbn]);
    }

    private function generateResponse($count = 1, $params = [])
    {
        $response = [
            'products' => [
                '@attributes' => [
                    'total-matched' => $count,
                    'records-returned' => $count,
                    'page-number' => '1',
                ],
            ]
        ];
        if ($count === 1) {
            $response['products']['product'] = $this->generateProductResponse($params);
        } else {
            $cc = 0;
            while ($cc < $count) {
                $response['products']['product'][] = $this->generateProductResponse($params);
                $cc++;
            }
        }
        return json_decode(json_encode($response));
    }

    private function generateProductResponse($params = [])
    {
        $product = [
            'ad-id' => uniqid(),
            'isbn' => uniqid(),
            'advertiser-id' => uniqid(),
            'advertiser-name' => uniqid(),
            'advertiser-category' => uniqid(),
            'buy-url' => uniqid(),
            'catalog-id' => uniqid(),
            'currency' => uniqid(),
            'description' => uniqid(),
            'image-url' => uniqid(),
            'in-stock' => uniqid(),
            'manufacturer-name' => [],
            'manufacturer-sku' => [],
            'name' => uniqid(),
            'price' => uniqid(),
            'retail-price' => [],
            'sale-price' => [],
            'sku' => uniqid(),
        ];
        $product = array_replace($product, $params);
        return $product;
    }

}
