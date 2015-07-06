<?php namespace Packback\Isbns\Prices\Clients;

use Packback\Infrastructure\Clients\CurlHttpClient as HttpClient,
    Packback\Isbns\Prices\Price,
    Packback\Isbns\TypesFacade as IsbnTypes;

class AbeBooksPriceClient implements PriceClientInterface
{
    protected $query = [];
    protected $resource;
    protected $client;

    public function __construct($config = [])
    {
        $this->client = new HttpClient($config['api_url']);
        $this->query['clientkey'] = $config['access_key'];
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
        return $this->getBookDataByIsbns($isbns);
    }

    /**
     * Collect book data from Amazon API
     *
     * @param  array                                          $isbns  ISBNs in need of data
     *
     * @return Packback\Infrastructure\Dtos\BookDtoCollection         Hydrated collection of Dtos
     */
    public function getBookDataByIsbns($isbns = [])
    {
        $collection = [];

        foreach ($isbns as $isbn) {
            $response = $this->addParam('isbn', $isbn)->send();
            $collection = $this->generateAbeBooksObjects($response, IsbnTypes::sellerAbeBooks());
        }

        return $collection;
    }


    /**
     * Helper method to parse amazon response payload and create collection
     *
     * @param  mixed                                         $response    Amazon response payload
     * @param  string                                        $merchant_id Desired retailer enum
     *
     * @return ackback\Infrastructure\Dtos\BookDtoCollection              Hydrated collection of Dtos
     */
    private function generateAbeBooksObjects($response, $merchant_id = null)
    {
        $collection = [];
        // Check response, populate price object, send collection back
        if (isset($response->Book)) {
            foreach($response->Book as $offer) {
                $price = new Price;
                if (isset($offer->listingPrice)) {
                    if (isset($offer->listingCondition) && strtolower($offer->listingCondition) == 'new book') {
                        $price->condition = IsbnTypes::conditionNew();
                    } elseif (isset($offer->itemCondition)) {
                        $price->condition = IsbnTypes::getConditionFromString($offer->itemCondition);
                    }
                    if (empty($price->condition)) {
                        $price->condition = IsbnTypes::conditionGood();
                    }
                    $price->isbn13 = $offer->isbn13;
                    $price->price = $offer->listingPrice;
                    $price->shipping_price = $offer->firstBookShipCost;
                    $price->url = 'http://affiliates.abebooks.com/c/74871/77797/2029?u='.urlencode($offer->listingUrl);
                    $price->retailer = $merchant_id;
                    $price->term = IsbnTypes::rentalTypePerpetual();
                }
                $collection[] = $price;
            }
        }
        return $collection;
    }

    public function populatePriceData($offer, $item, $merchant_id)
    {
        // New price model object
        $price = new Price;
        // Set retailer
        $price->retailer = $merchant_id;
        // Populate ISBN 13
        if (isset($item->ItemAttributes->EAN) ) {
            $price->isbn13 = $item->ItemAttributes->EAN;
        } elseif (isset($item->ItemAttributes->EISBN) ) {
            $price->isbn13 = $item->ItemAttributes->EISBN;
        }
        // Populate URL
        if (isset($item->DetailPageURL)) {
            $price->url = $item->DetailPageURL;
        }
        // Populate condition
        $price->condition = $price->getConditionFromString($offer->OfferAttributes->Condition);
        // Populate term
        $price->term = IsbnTypes::rentalTypePerpetual();
        // Populate Price
        $price->price = ((float)$offer->OfferListing->Price->Amount)/100;
        return $price;
    }

    /**
     * Add search criteria to search object
     *
     * @param string                                                  $key   Search criteria key
     * @param string                                                  $value Search criteria value
     *
     * @return Packback\Infrastructure\Clients\ProductClientInterface        Updated ProductClient
     */
    public function addParam($key, $value)
    {
        $this->query[$key] = $value;
        return $this;
    }

    /**
     * Execute request and return object
     *
     * @return stdClass|false Reponse object
     */
    public function send()
    {
        $response = $this->client->sendRequest($this->resource, $this->query);
        if ($response->getStatus() == 200) {
            return $response->getPayload();
        }
        return false;
    }
}
