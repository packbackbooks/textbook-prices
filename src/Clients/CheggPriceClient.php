<?php namespace Packback\Prices\Clients;

use Packback\Prices\PriceClient;

class CheggPriceClient extends PriceClient
{
    const RETAILER = 'chegg';

    public function __construct($config = [])
    {
        parent::__construct();
        $this->baseUrl = $config['api_url'];
        $this->query['KEY'] = $config['access_key'];
        $this->query['PW'] = $config['secret_key'];
        $this->query['R'] = 'XML';
        $this->query['V'] = '2.0';
        $this->query['with_pids'] = '1';
    }

    public function getPricesForIsbns($isbns = [])
    {
        foreach ($isbns as $isbn) {
            if ($isbn) {
                $this->query['isbn'] = $isbn;
                $response = $this->send();
                $this->addPricesToCollection($response);
            }
        }
        return $this->collection;
    }

    public function addPricesToCollection($payload)
    {
        if ($this->isValidCheggResponse($payload)) {
            foreach ($payload['Items']['Item']['Terms']['Term'] as $term) {
                $price = $this->createNewPrice();
                $price->isbn13 = $payload['Items']['Item']['EAN'];
                $price->retailer = self::RETAILER;
                $price->price = $term['Price'];
                $price->shipping_price = $this->setCheggShippingPrice($payload['Items']['Item']);
                $base_url = "http://chggtrx.com/click.track?CID=267582&AFID=304350&ADID=1088031&SID=&isbn_ean=";
                $price->url = $base_url . $price->isbn13;
                $price->condition = parent::CONDITION_GOOD;
                if (isset($term)) {
                    $price->term = $this->getTermFromString($term['Term']);
                }
                if ($term['Term'] != 'SUMMER') {
                    $this->collection[] = $price;
                }
            }
        }
        return $this->collection;
    }

    public function setCheggShippingPrice($item)
    {
        if (isset($item['ShippingPrices'])
            && isset($item['ShippingPrices']['ShippingPrice'])) {
            foreach ($item['ShippingPrices']['ShippingPrice'] as $shipping_price) {
                $prices[] = $shipping_price['Cost_first'];
            }
            return min($prices);
        }
        return null;
    }

    public function isValidCheggResponse($payload)
    {
        if (is_array($payload)
            && isset($payload['Items'])
            && isset($payload['Items']['Item'])
            && isset($payload['Items']['Item']['Terms'])
            && isset($payload['Items']['Item']['Terms']['Term'])) {

            return true;
        }
        return false;
    }
}
