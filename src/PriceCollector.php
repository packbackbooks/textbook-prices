<?php namespace Packback\Prices;

use GuzzleHttp\Client as GuzzleClient;

class PriceCollector
{
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = new GuzzleClient();
    }

    public function getAllPrices()
    {
        return ['test'];
    }
}
