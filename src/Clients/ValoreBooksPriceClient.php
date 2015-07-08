<?php namespace Packback\Prices\Clients;

use Packback\Prices\Clients\PriceClient;

class ValoreBooksPriceClient extends PriceClient
{
    const RETAILER = 'valorebooks';

    public function __construct($config = [])
    {
        parent::__construct();
        $this->baseUrl = $config['api_url'];
        $this->query['SiteID'] = $config['site_id'];
    }

    public function getPricesForIsbns($isbns = [])
    {
        foreach ($isbns as $isbn) {
            $this->query['ProductCode'] = $isbn;
            $response = $this->send();
            $this->addPricesToCollection($response);
        }
        return $this->collection;
    }

    public function addPricesToCollection($payload)
    {
        if (isset($payload['rental-offer'])) {
            $this->processRentalPrices($payload);
        }
        if (isset($payload['sale-offer'])) {
            $this->processSalePrices($payload);
        }
        return $this->collection;
    }

    public function processRentalPrices($payload)
    {
        $rental = $payload['rental-offer'];
        $isbn = $payload['product-code'];
        $retailer = self::RETAILER;
        $condition = $this->getConditionFromOffer($rental);
        $shipping_price = $this->getLowestShippingPriceFromOffer($rental);
        $link = $this->getLinkFromOffer($rental);

        if (isset($rental['ninty-day-price'])) {
            $price = $this->createNewPrice();
            $price->isbn13 = $isbn;
            $price->retailer = $retailer;
            $price->price = $rental['ninty-day-price'];
            $price->term = parent::TERM_QUARTER;
            $price->condition = $condition;
            $price->shipping_price = $shipping_price;
            $price->url = $link;
            $this->collection[] = $price;
        }
        if (isset($rental['semester-price'])) {
            $price = $this->createNewPrice();
            $price->isbn13 = $isbn;
            $price->retailer = $retailer;
            $price->price = $rental['semester-price'];
            $price->term = parent::TERM_SEMESTER;
            $price->condition = $condition;
            $price->shipping_price = $shipping_price;
            $price->url = $link;
            $this->collection[] = $price;
        }
        return $this;
    }

    public function processSalePrices($payload)
    {
        $sale = $payload['sale-offer'];
        $isbn = $payload['product-code'];
        $retailer = self::RETAILER;
        $condition = $this->getConditionFromOffer($sale);
        $shipping_price = $this->getLowestShippingPriceFromOffer($sale);
        $link = $this->getLinkFromOffer($sale);

        $price = $this->createNewPrice();
        $price->isbn13 = $isbn;
        $price->retailer = $retailer;
        $price->price = $sale['price'];
        $price->term = parent::TERM_PERPETUAL;
        $price->condition = $condition;
        $price->shipping_price = $shipping_price;
        $price->url = $link;
        $this->collection[] = $price;
        return $this;
    }

    private function getConditionFromOffer($offer = null)
    {
        if (isset($offer['condition'])) {
            return $this->getConditionFromString($offer['condition']);
        } else {
            return parent::CONDITION_GOOD;
        }
    }

    private function getLinkFromOffer($offer = null)
    {
        return isset($offer['link']) ? $offer['link'] : null;
    }

    private function getLowestShippingPriceFromOffer($offer = null)
    {
        if (isset($offer['shipping-options']) && is_array($offer['shipping-options']['shipping'])) {
            foreach ($offer['shipping-options']['shipping'] as $option) {
                if ($option['method'] == 'Standard') {
                    return $option['price-first'];
                }
            }
        }
        return null;
    }
}
