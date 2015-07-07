<?php namespace Packback\Prices;

use Packback\Prices\Clients\AbeBooksPriceClient;
use Packback\Prices\Clients\AmazonPriceClient;
use Packback\Prices\Clients\CheggPriceClient;

class PriceCollector
{
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->abeBooks = new AbeBooksPriceClient($config['abebooks']);
        $this->amazon = new AmazonPriceClient($config['amazon']);
        $this->chegg = new CheggPriceClient($config['chegg']);
    }

    public function getAllPrices($isbns = [])
    {
        $prices = [];
        $prices['abebooks'] = $this->getAbeBooksPrices($isbns);
        $prices['amazon'] = $this->getAmazonPrices($isbns);
        $prices['chegg'] = $this->getCheggPrices($isbns);
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

    public function getCheggPrices($isbns = [])
    {
        return $this->chegg->getPricesForIsbns($isbns);
    }
}
