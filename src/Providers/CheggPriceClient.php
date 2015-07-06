<?php namespace Packback\Isbns\Prices\Clients;

use Packback\Infrastructure\Clients\CurlHttpClient as HttpClient,
    Packback\Isbns\Prices\Price,
    Packback\Isbns\TypesFacade as IsbnTypes;

class CheggPriceClient implements PriceClientInterface
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
        // http://api.chegg.com/rent.svc?KEY=5c830075a9f5e00f71d140833b0f304d&PW=4582615&R=XML&V=2.0&isbn=".$chegg_isbn_13."&with_pids=1
        $this->client = new HttpClient($config['api_url']);
        $this->resource = $config['isbn_resource'];
        $this->query['KEY'] = $config['access_key'];
        $this->query['PW'] = $config['secret_key'];
        $this->query['R'] = 'XML';
        $this->query['V'] = '2.0';
        $this->query['with_pids'] = '1';
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
                if ($this->isValidCheggResponse($payload)) {
                    $prices = $this->processPayloadPrices($payload);
                    $results = array_merge($prices, $results);
                }
            }
        }
        return $results;
    }

    private function buildQuerywithIsbn($isbn = null)
    {
        $query = $this->query;
        if ($isbn) {
            $query['isbn'] = $isbn;
        }
        return $query;
    }

    /**
     * Attempt to process the Chegg API response payload
     *
     * @param  mixed  $payload API response payload
     *
     * @return void
     */
    private function processPayloadPrices($payload)
    {
        $prices = [];
        if ($this->isValidCheggResponse($payload)) {
            foreach ($payload->Items->Item->Terms->Term as $term) {
                $price = new Price;
                $price->isbn13 = $payload->Items->Item->EAN;
                $price->retailer = IsbnTypes::sellerChegg();
                $price->price = $term->Price;
                $price->shipping_price = $this->setCheggShippingPrice($payload->Items->Item);
                $base_url = "http://chggtrx.com/click.track?CID=267582&AFID=304350&ADID=1088031&SID=&isbn_ean=";
                $price->url = $base_url . $price->isbn13;
                $price->condition = IsbnTypes::conditionGood();
                if (isset($term)) {
                    $price->term = IsbnTypes::getTermFromString($term->Term);
                }
                if ($term->Term != 'SUMMER') {
                    $prices[] = $price;
                }
            }
        }
        return $prices;
    }

    private function setCheggShippingPrice($item)
    {
        if (isset($item->ShippingPrices)
            && isset($item->ShippingPrices->ShippingPrice)) {
            foreach ($item->ShippingPrices->ShippingPrice as $shipping_price) {
                $prices[] = $shipping_price->Cost_first;
            }
            return min($prices);
        }
        return null;
    }

    private function isValidCheggResponse($payload)
    {
        if (is_object($payload)
            && isset($payload->Items)
            && isset($payload->Items->Item)
            && isset($payload->Items->Item->Terms)
            && isset($payload->Items->Item->Terms->Term)) {

            return true;
        }
        return false;
    }
}
