<?php namespace Packback\Prices\Clients;

class CengagePriceClient extends CommissionJunctionPriceClient
{
    const RETAILER = 'cengage';

    public function __construct($config = [])
    {
        $cj_config = array_merge($config['cj'], $config[self::RETAILER]);
        parent::__construct($cj_config);
    }

    public function getPricesForIsbns($isbns = [])
    {
        foreach ($isbns as $isbn) {
            $response = $this->addParam('upc', $isbn)->send();
            $this->addPricesToCollection($response);
        }
        $this->addMetaToPrices($this->collection);
        return $this->collection;
    }

    private function addMetaToPrices($prices = [])
    {
        foreach($prices as $i => $price) {
            $prices[$i]->retailer = self::RETAILER;
            $prices[$i]->condition = parent::CONDITION_NEW;
            $prices[$i]->term = parent::TERM_SEMESTER;
        }
        return $prices;
    }
}
