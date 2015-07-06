<?php namespace Packback\Prices\Providers;

use GuzzleHttp\Client as GuzzleClient;

class AbeBooksPriceClient
{
    protected $query = [];
    protected $client;

    public function __construct($config = [])
    {
        $this->client = new GuzzleClient();
        $this->baseUrl = $config['api_url'];
        $this->query['clientkey'] = $config['access_key'];
    }

    public function getPricesForIsbns($isbns = [])
    {
        return $this->getBookDataByIsbns($isbns);
    }

    public function getBookDataByIsbns($isbns = [])
    {
        $collection = [];

        foreach ($isbns as $isbn) {
            $response = $this->addParam('isbn', $isbn)->send();
            $collection[] = $response;
        }

        return $collection;
    }

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

    public function addParam($key, $value)
    {
        $this->query[$key] = $value;
        return $this;
    }

    public function send()
    {
        $querystring = http_build_query($this->query);
        try {
            $response = $this->client->get($this->baseUrl.'?'.$querystring);
            if ($response->getStatusCode() == '200') {
                return $this->decodeXml($response->getBody(true));
            }
        } catch ( \Exception $e) {
            // Return error messaging
            return $e->getMessage();
        }
        return null;
    }

    public function decodeXml($string)
    {
        return json_decode(
            json_encode(
                simplexml_load_string(
                    $string
                )
            ),
            true
        );
    }
}
