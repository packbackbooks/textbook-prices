<?php namespace Packback\Prices\Clients;

use CROSCON\CommissionJunction\Client;
use Packback\Prices\Clients\PriceClient;

class CommissionJunctionPriceClient extends PriceClient
{
    public function __construct($config = [])
    {
        $this->client = new Client($config['key']);
        $this->query['website-id'] = $config['website'];
        $this->query['advertiser-ids'] = $config['cj_advertiser_id'];
    }

    public function getPricesForIsbns($isbns = [])
    {
        foreach ($isbns as $isbn) {
            $response = $this->addParam('isbn', $isbn)->send();
            $this->addPricesToCollection($response);
        }

        return $this->collection;
    }

    public function addPricesToCollection($response)
    {
        if ($this->responseHasProducts($response)) {
            if (is_array($response->products->product)) {
                foreach ($response->products->product as $product) {
                    $this->collection[] = $this->makePriceFromProduct($product);
                }
            } else {
                $this->collection[] = $this->makePriceFromProduct($response->products->product);
            }
        }
        return $this->collection;
    }

    public function makePriceFromProduct($product)
    {
        $price = $this->createNewPrice();
        $price->isbn13 = $product->isbn;
        $price->price = $product->price;
        $price->url = $product->{'buy-url'};
        $price->shipping_price = null;
        return $price;
    }

    public function responseHasProducts($response)
    {
        if (is_object($response) && isset($response->products)
            && isset($response->products->{'@attributes'})
            && isset($response->products->{'@attributes'}->{'records-returned'})
            && $response->products->{'@attributes'}->{'records-returned'} > 0) {
                return true;
        }
        return false;
    }

    public function send()
    {
        try {
            return json_decode(json_encode($this->client->productSearch($this->query)), FALSE);
        } catch ( \Exception $e) {
            // Return error messaging
            return $e->getMessage();
        }
    }
}
