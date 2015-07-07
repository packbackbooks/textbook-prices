<?php namespace Packback\Prices\Clients;

use Packback\Prices\Clients\PriceClient;
use Packback\Prices\PriceDto;
use ApaiIO\Configuration\GenericConfiguration,
    ApaiIO\Operations\Search,
    ApaiIO\ApaiIO;

class AmazonPriceClient extends PriceClient
{
    const RETAILER = 'amazon';
    const RETAILER_MARKETPLACE = 'amazon-marketplace';

    public $conf;
    public $container;
    public $search;

    public function __construct($config = [])
    {
        $this->conf = new GenericConfiguration();
        $this->setConfiguration($config);
        $this->container = new ApaiIO($this->conf);
        $this->search = new Search();
    }

    public function setConfiguration($config = [])
    {
        try {
            $this->conf
                ->setCountry('com')
                ->setAccessKey($config['access_key'])
                ->setSecretKey($config['secret_key'])
                ->setAssociateTag($config['associate_tag']);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $this->conf->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToSimpleXmlObject');
    }

    public function getPricesForIsbns($isbns = [])
    {
        $isbn_groups = [];

        if (count($isbns) > 10) {
            $isbn_groups = array_chunk($isbns, 10);
        } else {
            array_push($isbn_groups, $isbns);
        }
        foreach ($isbn_groups as $isbn_group) {
            $response = $this->addParam('Condition', 'New')
                ->addParam('IdType', 'ISBN')
                ->addParam('ItemId', $isbn_group)
                ->addParam('Condition', 'All')
                ->addParam('SearchIndex', 'Books')
                ->addParam('Operation', 'ItemLookup')
                ->addParam('Version', '2011-08-01')
                ->addParam('ResponseGroup', ['Large'])
                ->addParam('Service', 'AWSECommerceService');

            // Get response for Marketplace books
            $response = $this->send();
            $marketplace_collection = $this->generateAmazonObjects($response, self::RETAILER_MARKETPLACE);
            // Get response for only Amazon books
            $response = $this->addParam('MerchantId', 'Amazon')->send();
            $amazon_collection = $this->generateAmazonObjects($response, self::RETAILER);

            $this->collection = array_merge($amazon_collection, $marketplace_collection);
        }

        return $this->collection;
    }

    private function generateAmazonObjects($response, $merchant_id = null)
    {
        $collection = [];
        if(isset($response->Items->Request->IsValid) && $response->Items->Request->IsValid) {
            if (isset($response->Items->Item) && isset($merchant_id)) {
                $items = $response->Items->Item;
                foreach ($items as $key => $item) {
                    // Create price objects for each offer
                    if (isset($item->Offers->Offer)) {
                        if (is_array($item->Offers->Offer)) {
                            foreach ($item->Offers->Offer as $offer) {
                                $collection[] = $this->populatePriceData($offer, $item, $merchant_id);
                            }
                        } else {
                            $collection[] = $this->populatePriceData($item->Offers->Offer, $item, $merchant_id);
                        }
                    }
                }
            }
        }
        return $collection;
    }

    public function populatePriceData($offer, $item, $merchant_id)
    {
        // New price model object
        $price = new PriceDto;
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
        $price->condition = parent::getConditionFromString($offer->OfferAttributes->Condition);
        // Populate term
        $price->term = parent::TERM_PERPETUAL;
        // Populate Price
        $price->price = ((float)$offer->OfferListing->Price->Amount)/100;
        return $price;
    }

    public function addParam($key, $value)
    {
        $this->search->{'set'.$key}($value);
        return $this;
    }

    public function send()
    {
        try {
            $response = $this->container->runOperation($this->search);
            return json_decode(
                json_encode(
                    $response
                )
            );
        } catch (\Exception $e) {
            return false;
        }

    }
}
