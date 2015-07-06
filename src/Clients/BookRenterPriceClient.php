<?php namespace Packback\Isbns\Prices\Clients;

use Packback\Infrastructure\Clients\CurlHttpClient as HttpClient,
    Packback\Isbns\Prices\Price,
    Packback\Isbns\TypesFacade as IsbnTypes;

class BookRenterPriceClient extends CommissionJunctionPriceClient
{
    public function __construct($config = [])
    {
        parent::__construct($config['cj_advertiser_id']);
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
            $response = $this->addParam('isbn', $isbn)->getCjResults($isbn);
            if ($response) {
                if ($this->responseHasProducts($response)) {
                    $prices = $this->buildPricesFromResponse($response);
                    $prices = $this->addBookRenterMetaToPrices($prices);
                    $results = array_merge($results, $prices);
                }
            }
        }
        return $results;
    }

    private function addBookRenterMetaToPrices($prices = [])
    {
        for ($i = 0; $i < count($prices); $i++) {
            $prices[$i]->retailer = IsbnTypes::sellerBookRenter();
            $prices[$i]->condition = IsbnTypes::conditionNew();
            $prices[$i]->term = IsbnTypes::rentalTypeSemester();
        }
        return $prices;
    }
}
