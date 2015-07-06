<?php namespace Packback\Isbns\Prices\Clients;

interface PriceClientInterface
{
    /**
     * Fetch collection of Isbns\Price models with data from remote API
     *
     * @param  array $isbns Isbns to collect prices for
     *
     * @return array Collection of Isbn\Price models
     */
    public function getPricesForIsbns($isbns = []);
}
