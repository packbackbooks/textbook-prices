<?php namespace Packback\Prices;

use Packback\Prices\Clients\AbeBooksPriceClient;
use Packback\Prices\Clients\AmazonPriceClient;

class PriceCollector
{
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->abeBooks = new AbeBooksPriceClient($config['abebooks']);
        $this->amazon = new AmazonPriceClient($config['amazon']);
    }

    public function getAllPrices($isbns = [])
    {
        $prices = [];
        $prices['abebooks'] = $this->getAbeBooksPrices($isbns);
        $prices['amazon'] = $this->getAmazonPrices($isbns);
        return $prices;
    }

    public function getAbeBooksPrices($isbns = [])
    {
        return $this->abeBooks->getPricesForIsbns($isbns);
    }

    public function getAmazonPrices($isbns = [])
    {
        return $this->amazon->getPricesForIsbns($isbns);
    }
}
