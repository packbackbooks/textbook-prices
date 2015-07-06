<?php namespace Packback\Prices\Clients;

use Packback\Prices\PriceDto;
use GuzzleHttp\Client as GuzzleClient;

class PriceClient
{
    const CONDITION_NEW = 'new';
    const CONDITION_GOOD = 'good';
    const CONDITION_ACCEPTABLE = 'acceptable';
    const CONDITION_POOR = 'poor';

    const TERM_PERPETUAL = 'perpetual';
    const TERM_SEMESTER = 'semester';
    const TERM_QUARTER = 'quarter';
    const TERM_HALFQUARTER = 'half-quarter';
    const TERM_TWOMONTH = 'two-month';
    const TERM_ONEMONTH = 'one-month';
    const TERM_DAILY = 'daily';

    public $client;
    public $query = [];
    public $collection = [];
    public $baseUrl = '';

    public function __construct()
    {
        $this->client = new GuzzleClient();
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

    public function createNewPrice()
    {
        return new PriceDto();
    }

    public function addPriceToCollection($price)
    {
        $this->collection[] = $price;
        return $this;
    }

    public function getConditionFromString($string)
    {
        switch (strtolower($string)) {
            case 'new':
            case 'rent for 125 days':
            case 'rent for 90 days':
            case 'rent for 60 days':
            case 'rent for 45 days':
            case 'rent for 30 days':
                return self::CONDITION_NEW;
            case 'very good':
            case 'as new':
            case 'good':
            case 'used':
            case 'collectible':
                return self::CONDITION_GOOD;
            case 'fine':
            case 'fair':
            case 'acceptable':
                return self::CONDITION_ACCEPTABLE;
            case 'poor':
                return self::CONDITION_POOR;
            default:
                throw new \Exception("Unrecognized book condition", 1);
        }
    }

}
