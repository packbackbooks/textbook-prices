<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\BookRenterPriceClient;
use Mockery as m;

class BookRenterPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
        $this->client = new BookRenterPriceClient($config);
        $this->client->client = m::mock('CROSCON\CommissionJunction\Client');
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
            $this->assertEquals('bookrenter', $book->retailer);
            $this->assertEquals('new', $book->condition);
            $this->assertEquals('semester', $book->term);
        }
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
