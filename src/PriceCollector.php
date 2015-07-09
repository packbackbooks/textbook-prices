<?php namespace Packback\Prices;

use Packback\Prices\Clients\AbeBooksPriceClient;
use Packback\Prices\Clients\AmazonPriceClient;
use Packback\Prices\Clients\CheggPriceClient;
use Packback\Prices\Clients\ValoreBooksPriceClient;

class PriceCollector
{
    private $config;
    public $prices = [];

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->abebooks = new AbeBooksPriceClient($config['abebooks']);
        $this->amazon = new AmazonPriceClient($config['amazon']);
        $this->chegg = new CheggPriceClient($config['chegg']);
        $this->valore = new ValoreBooksPriceClient($config['valore']);
    }

    public function getAllPrices($isbns = [])
    {
        $this->prices['abebooks'] = $this->abebooks->getPricesForIsbns($isbns);
        $this->prices['amazon'] = $this->amazon->getPricesForIsbns($isbns);
        $this->prices['chegg'] = $this->chegg->getPricesForIsbns($isbns);
        $this->prices['valore'] = $this->valore->getPricesForIsbns($isbns);
        return $this->prices;
    }
}
