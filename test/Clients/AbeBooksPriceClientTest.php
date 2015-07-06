<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\AbeBooksPriceClient;
use Mockery as m;

class AbeBooksPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.php';
        $this->client = new AbeBooksPriceClient($config['abebooks']);
    }

    public function testGetPricesWhenListOfIsbnsProvided()
    {
        $isbns = [
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
        ];
        // $results = $this->client->getPricesForIsbns($isbns);
        // print_r($results); exit;
    }

    public function testItBuildsPriceCollectionFromValidXmlResponse()
    {
        $response = $this->generateResponse();
        $this->client->collection = [];

        $results = $this->client->addPricesToCollection($isbns);

    }

    private function generateResponse($params = [])
    {
        $xml = '
        <?xml version="1.0" encoding="UTF-8"?>
        <searchResults>
            <resultCount>495144</resultCount>
            <Book>
                <bookId>959356004</bookId>
                <isbn10>025536251X</isbn10>
                <isbn13>9780255362511</isbn13>
                <listingCondition>NOT NEW BOOK</listingCondition>
                <itemCondition>Very Good</itemCondition>
                <quantity>1</quantity>
                <vendorCurrency>GBP</vendorCurrency>
                <listingPrice>1.0</listingPrice>
                <firstBookShipCost>2.24</firstBookShipCost>
                <extraBookShipCost>0.0</extraBookShipCost>
                <minShipDays>0</minShipDays>
                <maxShipDays>0</maxShipDays>
                <totalListingPrice>3.24</totalListingPrice>
                <listingUrl>www.abebooks.com/servlet/BookDetailsPL?bi=959356004&amp;cm_ven=sws&amp;cm_cat=sws&amp;cm_pla=sws&amp;cm_ite=959356004</listingUrl>
                <author>Ray Robinson</author>
                <title>Efficiency and the National Health Service: A Case for Internal Markets (Health)</title>
                <publisherName>Institute of Economic Affairs (I</publisherName>
                <vendorName>The Orchard Bookshop.</vendorName>
                <vendorLocation>Hayes, United Kingdom</vendorLocation>
                <vendorId>676907</vendorId>
                <sellerRating>5</sellerRating>
                <keywords>SUBJECTS</keywords>
                <bindingType>S</bindingType>
            </Book>
            <Book>
                <bookId>1090464492</bookId>
                <isbn10>074602049X</isbn10>
                <isbn13>9780746020494</isbn13>
                <listingCondition>NOT NEW BOOK</listingCondition>
                <itemCondition>Good</itemCondition>
                <quantity>1</quantity>
                <vendorCurrency>GBP</vendorCurrency>
                <listingPrice>1.0</listingPrice>
                <firstBookShipCost>2.24</firstBookShipCost>
                <extraBookShipCost>0.0</extraBookShipCost>
                <minShipDays>0</minShipDays>
                <maxShipDays>0</maxShipDays>
                <totalListingPrice>3.24</totalListingPrice>
                <listingUrl>www.abebooks.com/servlet/BookDetailsPL?bi=1090464492&amp;cm_ven=sws&amp;cm_cat=sws&amp;cm_pla=sws&amp;cm_ite=1090464492</listingUrl>
                <author>Heather Amery</author>
                <title>Market Day (Farmyard Tales)</title>
                <publisherName>Usborne Publishing Ltd</publisherName>
                <vendorName>The Orchard Bookshop.</vendorName>
                <vendorLocation>Hayes, United Kingdom</vendorLocation>
                <vendorId>676907</vendorId>
                <sellerRating>5</sellerRating>
                <keywords>PICTURE BOOKS FARMYARD TALES FIRST READERS FICTION ENGLISH AGES 0 2 3 4 HARDCOVER</keywords>
                <bindingType>H</bindingType>
            </Book>
        </searchResults>';
    }

}
