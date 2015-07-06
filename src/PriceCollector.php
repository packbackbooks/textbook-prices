<?php namespace Packback\Prices;

use Packback\Prices\Providers\AbeBooksPriceClient;

class PriceCollector
{
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->abeBooks = new AbeBooksPriceClient($config['abebooks']);
    }

    public function getAllPrices($isbns = [])
    {
        $prices = [];
        $prices['abebooks'] = $this->getAbeBooksPrices($isbns);
        return $prices;
    }

    public function getAbeBooksPrices($isbns = [])
    {
        return $this->abeBooks->getPricesForIsbns($isbns);
    }
}
