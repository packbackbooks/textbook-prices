<?php namespace Packback\Isbns\Prices\Clients;

use Packback\Infrastructure\Clients\CurlHttpClient as HttpClient,
    Packback\Isbns\Prices\Price,
    Packback\Isbns\TypesFacade as IsbnTypes;

class ValoreBooksPriceClient implements PriceClientInterface
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

    private $resource;

    public function __construct($config = [])
    {
        $this->client = new HttpClient($config['api_url']);
        $this->query['SiteID'] = $config['site_id'];
        $this->resource = $config['isbn_resource'];
    }

    /**
     * Fetch collection of Isbns\Price models with data from remote API
     *
     * @param  array $isbns Isbns to collect prices for
     *
     * @return array Collection of Isbn\Price models
     */
    public function getPricesForIsbns($isbns = [])
    {
        $results = [];
        foreach ($isbns as $isbn) {
            $query = $this->buildQuerywithIsbn($isbn);
            $response = $this->client->sendRequest($this->resource, $query);
            if ($response->getStatus() == 200) {
                $payload = $response->getPayload();
                //print_r($payload);
                $rental_prices = $this->processRentalPrices($payload);
                $sales_prices = $this->processSalePrices($payload);
                $results = array_merge($results, $rental_prices, $sales_prices);
            }
        }
        return $results;
    }

    private function buildQuerywithIsbn($isbn = null)
    {
        $query = $this->query;
        if ($isbn) {
            $query['ProductCode'] = $isbn;
        }
        return $query;
    }

    /**
     * Attempt to process the Valorebooks API response payload for rental prices
     *
     * @param  mixed  $payload API response payload
     *
     * @return void
     */
    private function processRentalPrices($payload)
    {
        $prices = [];
        if ($this->hasRentalPrice($payload)) {
            $rental = $payload->{'rental-offer'};
            $isbn = $payload->{'product-code'};
            $retailer = IsbnTypes::sellerValoreBooks();
            $condition = $this->getConditionFromOffer($rental);
            $shipping_price = $this->getLowestShippingPriceFromOffer($rental);
            $link = $this->getLinkFromOffer($rental);

            if (isset($rental->{'ninty-day-price'})) {
                $price = new Price;
                $price->isbn13 = $isbn;
                $price->retailer = $retailer;
                $price->price = $rental->{'ninty-day-price'};
                $price->term = IsbnTypes::rentalTypeQuarter();
                $price->condition = $condition;
                $price->shipping_price = $shipping_price;
                $price->url = $link;
                $prices[] = $price;
            }
            if (isset($rental->{'semester-price'})) {
                $price = new Price;
                $price->isbn13 = $isbn;
                $price->retailer = $retailer;
                $price->price = $rental->{'semester-price'};
                $price->term = IsbnTypes::rentalTypeSemester();
                $price->condition = $condition;
                $price->shipping_price = $shipping_price;
                $price->url = $link;
                $prices[] = $price;
            }
        }
        return $prices;
    }

    /**
     * Attempt to process the Valorebooks API response payload for sale prices
     *
     * @param  mixed  $payload API response payload
     *
     * @return void
     */
    private function processSalePrices($payload)
    {
        $prices = [];
        if ($this->hasSalePrice($payload)) {
            $sale = $payload->{'sale-offer'};
            $isbn = $payload->{'product-code'};
            $retailer = IsbnTypes::sellerValoreBooks();
            $condition = $this->getConditionFromOffer($sale);
            $shipping_price = $this->getLowestShippingPriceFromOffer($sale);
            $link = $this->getLinkFromOffer($sale);

            $price = new Price;
            $price->isbn13 = $isbn;
            $price->retailer = $retailer;
            $price->price = $sale->price;
            $price->term = IsbnTypes::rentalTypePerpetual();
            $price->condition = $condition;
            $price->shipping_price = $shipping_price;
            $price->url = $link;
            $prices[] = $price;
        }
        return $prices;
    }

    /**
     * Helper method to detect if payload has rental price
     *
     * @param  mixed   $payload API response payload
     *
     * @return boolean          It has rental prices
     */
    private function hasRentalPrice($payload = null)
    {
        return isset($payload->{'rental-offer'});
    }

    /**
     * Helper method to detect if payload has sale price
     *
     * @param  mixed   $payload API response payload
     *
     * @return boolean          It has sale prices
     */
    private function hasSalePrice($payload = null)
    {
        return isset($payload->{'sale-offer'});
    }

    /**
     * Helper method to detect condition from offer node
     *
     * @param  mixed $offer Valore payload offer node
     *
     * @return string       Normalized condition
     */
    private function getConditionFromOffer($offer = null)
    {
        if (isset($offer->condition)) {
            return IsbnTypes::getConditionFromString($offer->condition);
        } else {
            return IsbnTypes::conditionGood();
        }
    }

    /**
     * Helper method to detect link from offer node
     *
     * @param  mixed       $offer Valore payload offer node
     *
     * @return string|null        Link, if available
     */
    private function getLinkFromOffer($offer = null)
    {
        return isset($offer->link) ? $offer->link : null;
    }

    /**
     * Helper method to detect lowest shipping cost from offer node
     *
     * @param  mixed       $offer Valore payload offer node
     *
     * @return string|null        Shipping price, if available
     */
    private function getLowestShippingPriceFromOffer($offer = null)
    {
        if (isset($offer->{'shipping-options'}) && is_array($offer->{'shipping-options'}->shipping)) {
            foreach ($offer->{'shipping-options'}->shipping as $option) {
                if ($option->method == 'Standard') {
                    return $option->{'price-first'};
                }
            }
        }
        return null;
    }
}
