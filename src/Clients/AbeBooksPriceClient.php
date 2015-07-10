<?php namespace Packback\Prices\Clients;

use Packback\Prices\PriceClient;

class AbeBooksPriceClient extends PriceClient
{
    const RETAILER = 'abebooks';

    public function __construct($config = [])
    {
        parent::__construct();
        $this->baseUrl = $config['api_url'];
        $this->query['clientkey'] = $config['access_key'];
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
        // Check response, populate price object, send collection back
        if (isset($response['Book'])) {
            foreach($response['Book'] as $offer) {
                $offer = (object) $offer;
                $price = $this->createNewPrice();
                if (isset($offer->listingPrice)) {
                    if (isset($offer->listingCondition) && strtolower($offer->listingCondition) == 'new book') {
                        $price->condition = parent::CONDITION_NEW;
                    } elseif (isset($offer->itemCondition)) {
                        $price->condition = $this->getConditionFromString($offer->itemCondition);
                    }
                    if (empty($price->condition)) {
                        $price->condition = parent::CONDITION_GOOD;
                    }
                    $price->isbn13 = $offer->isbn13;
                    $price->price = $offer->listingPrice;
                    $price->shipping_price = $offer->firstBookShipCost;
                    $price->url = 'http://affiliates.abebooks.com/c/74871/77797/2029?u='.urlencode($offer->listingUrl);
                    $price->retailer = self::RETAILER;
                    $price->term = parent::TERM_PERPETUAL;
                }
                $this->addPriceToCollection($price);
            }
        }
        return $this;
    }

}
