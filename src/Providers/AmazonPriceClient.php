<?php namespace Packback\Isbns\Prices\Clients;

use Packback\Isbns\TypesFacade as IsbnTypes,
    Packback\Isbns\Prices\Price;
use ApaiIO\ApaiIO,
    ApaiIO\Configuration\GenericConfiguration,
    ApaiIO\Operations\Search;

class AmazonPriceClient implements PriceClientInterface
{
    protected $conf;
    protected $container;
    protected $search;

    public function __construct($config = [])
    {
        $this->conf = new GenericConfiguration();

        // These credentials would, of course, be stored in a dotfile in a real project instead of committed to a repository;
        // I'm including them here for your convenience
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
        $this->container = new ApaiIO($this->conf);
        $this->search = new Search();
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
            $marketplace_collection = $this->generateAmazonObjects($response, IsbnTypes::sellerAmazon());
            // Get response for only Amazon books
            $response = $this->addParam('MerchantId', 'Amazon')->send();
            $amazon_collection = $this->generateAmazonObjects($response, IsbnTypes::sellerAmazonMarketplace());

            $collection = array_merge($amazon_collection, $marketplace_collection);
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
        $price->condition = IsbnTypes::getConditionFromString($offer->OfferAttributes->Condition);
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
     */
    public function addParam($key, $value)
    {
        $this->search->{'set'.$key}($value);
        return $this;
    }

    /**
     * Execute request and return object
     *
     * @return stdClass|false Reponse object
     */
    public function send()
    {
        try {
            $response = $this->container->runOperation($this->search);
            return json_decode(
                json_encode(
                    $response
                )
            );
        } catch (Exception $e) {
            return false;
        }

    }
}
