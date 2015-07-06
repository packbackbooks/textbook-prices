<?php namespace Packback\Isbns\Prices\Clients;

use CROSCON\CommissionJunction\Client;
use Packback\Isbns\Isbn,
    Packback\Isbns\Prices\Price;

class CommissionJunctionPriceClient
{
    /**
     * Parameters for request builder
     *
     * @var array
     */
    protected $query = [];

    /**
     * Client to do the talkin'
     *
     * @var Packback\Infrastructure\Clients\GuzzleHttpClient
     */
    protected $client;

    public function __construct($advertiser_id = null)
    {
        $website = '7204670';
        $key = '008c28785b1666203ed68acf382793d8b4cbd7cfdbcc540f89f4cc8318cb378cb17d82463f718cbfddaca59b77ab3dc9bd8d4f756dc56e24e3f90a93dd0d08bee1/2da34fed7911b016460dd503d7ff50652eab0b47eaf4c44a8ba7bc586a73a01fff4258a93be52c80d6b06708a3bc9f81d8b8bf00ad8a0df43a09b869f98ffe71';
        $this->client = new Client($key);
        $this->query['website-id'] = $website;
        $this->query['advertiser-ids'] = $advertiser_id;
    }

    protected function addParam($key, $value)
    {
        $this->query[$key] = $value;
        return $this;
    }

    protected function buildPricesFromResponse($response)
    {
        $prices = [];
        if ($this->responseHasProducts($response)) {
            if (is_array($response->products->product)) {
                foreach ($response->products->product as $product) {
                    $prices[] = $this->makePriceFromProduct($product);
                }
            } else {
                $prices[] = $this->makePriceFromProduct($response->products->product);
            }
        }
        return $prices;
    }

    private function makePriceFromProduct($product)
    {
        $price = new Price;
        $price->isbn13 = (Isbn::isValidIsbn13($product->isbn) ? $product->isbn : Isbn::formatIsbn13($product->isbn));
        $price->price = $product->price;
        $price->url = $product->{'buy-url'};
        $price->shipping_price = null;
        return $price;
    }

    protected function responseHasProducts($response)
    {
        try {
            if (is_object($response)) {
                if ($response->products->{'@attributes'}->{'records-returned'} > 0) {
                    return true;
                }
            }
        } catch (Execption $e) {
            \Log::error($e->getMessage());
        }
        return false;
    }

    protected function getCjResults($isbn = null)
    {
        try {
            return json_decode(json_encode($this->client->productSearch($this->query)), FALSE);
        } catch (\CROSCON\CommissionJunction\Exception $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }
}
