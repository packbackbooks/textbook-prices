<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\AmazonPriceClient;
use Mockery as m;

class AmazonPriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        include 'config.example.php';
        $this->client = new AmazonPriceClient($config['amazon']);
        $this->client->conf = m::mock('ApaiIO\Configuration\GenericConfiguration');
        $this->client->container = m::mock('ApaiIO\ApaiIO');
        $this->client->search = m::mock('ApaiIO\Operations\Search');
    }

    public function testItSetsConfigurationWithSuccess()
    {
        include 'config.example.php';

        $this->client->conf->shouldReceive('setCountry')
            ->with('com')
            ->once()
            ->andReturn($this->client->conf);
        $this->client->conf->shouldReceive('setAccessKey')
            ->with($config['amazon']['access_key'])
            ->once()
            ->andReturn($this->client->conf);
        $this->client->conf->shouldReceive('setSecretKey')
            ->with($config['amazon']['secret_key'])
            ->once()
            ->andReturn($this->client->conf);
        $this->client->conf->shouldReceive('setAssociateTag')
            ->with($config['amazon']['associate_tag'])
            ->once()
            ->andReturn($this->client->conf);
        $this->client->conf->shouldReceive('setResponseTransformer')
            ->with('\ApaiIO\ResponseTransformer\XmlToSimpleXmlObject')
            ->once()
            ->andReturn($this->client->conf);

        $this->client->setConfiguration($config['amazon']);
    }

    public function testItSetsConfigurationWithException()
    {
        include 'config.example.php';

        $this->client->conf->shouldReceive('setCountry')
            ->with('com')
            ->once()
            ->andThrow('Exception');
        $this->client->conf->shouldReceive('setResponseTransformer')
            ->with('\ApaiIO\ResponseTransformer\XmlToSimpleXmlObject')
            ->once()
            ->andReturn($this->client->conf);

        try {
            $this->client->setConfiguration($config['amazon']);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testSendSuccessfulApiRequest()
    {
        $response = $this->generateApiResponse();

        $this->client->container->shouldReceive('runOperation')
            ->with($this->client->search)
            ->andReturn($response);

        $results = $this->client->send();

        $this->assertEquals(json_decode(json_encode($response)), $results);
    }

    public function testSendFailedApiRequest()
    {
        $this->client->container->shouldReceive('runOperation')
            ->with($this->client->search)
            ->once()
            ->andThrow('Exception');

        try {
            $this->client->send();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetPricesForIsbnList()
    {
        $items = rand(1,4);
        $isbns = [
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
            '9780000000644',
        ];
        $response = $this->generateApiResponse($items);

        $this->client->search->shouldReceive('setCondition')
            ->with('New')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setIdType')
            ->with('ISBN')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setItemId')
            ->with($isbns)
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setCondition')
            ->with('All')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setSearchIndex')
            ->with('Books')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setOperation')
            ->with('ItemLookup')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setVersion')
            ->with('2011-08-01')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setResponseGroup')
            ->with(['Large'])
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setService')
            ->with('AWSECommerceService')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setMerchantId')
            ->andReturn($this->client->search);
        $this->client->container->shouldReceive('runOperation')
            ->with($this->client->search)
            ->andReturn($response);

        $results = $this->client->getPricesForIsbns($isbns);

        $this->assertEquals($items*2, count($results));
    }

    public function testGetPricesForIsbnListWithMultipleOffers()
    {
        $items = rand(1,4);
        $isbns = [
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
            '9780000000644',
        ];
        $response = $this->generateApiResponse($items, 2);

        $this->client->search->shouldReceive('setCondition')
            ->with('New')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setIdType')
            ->with('ISBN')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setItemId')
            ->with($isbns)
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setCondition')
            ->with('All')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setSearchIndex')
            ->with('Books')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setOperation')
            ->with('ItemLookup')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setVersion')
            ->with('2011-08-01')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setResponseGroup')
            ->with(['Large'])
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setService')
            ->with('AWSECommerceService')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setMerchantId')
            ->andReturn($this->client->search);
        $this->client->container->shouldReceive('runOperation')
            ->with($this->client->search)
            ->andReturn($response);

        $results = $this->client->getPricesForIsbns($isbns);

        $this->assertEquals($items*6, count($results));
    }

    public function testGetPricesForIsbnListWithBatchSizeGreaterThanTen()
    {
        $items = rand(11,12);
        $isbns = [
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
            '9780000000644',
            '9780000000187',
            '9780001381889',
            '9780001831728',
            '9780002005098',
            '9780000000644',
            '9780000000187',
        ];
        $isbn_groups = array_chunk($isbns, 10);
        $response = $this->generateApiResponse($items);

        $this->client->search->shouldReceive('setCondition')
            ->with('New')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setIdType')
            ->with('ISBN')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setItemId')
            ->with($isbn_groups[0])
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setItemId')
            ->with($isbn_groups[1])
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setCondition')
            ->with('All')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setSearchIndex')
            ->with('Books')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setOperation')
            ->with('ItemLookup')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setVersion')
            ->with('2011-08-01')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setResponseGroup')
            ->with(['Large'])
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setService')
            ->with('AWSECommerceService')
            ->andReturn($this->client->search);
        $this->client->search->shouldReceive('setMerchantId')
            ->andReturn($this->client->search);
        $this->client->container->shouldReceive('runOperation')
            ->with($this->client->search)
            ->andReturn($response);

        $results = $this->client->getPricesForIsbns($isbns);

        $this->assertEquals($items*4, count($results));
    }

    public function testPopulatePriceDataWithEan()
    {
        $eisbn = uniqid();
        $merchant_id = uniqid();
        $response = $this->generateApiResponse(1);
        $item = $response->Items->Item[0];
        $item->ItemAttributes->EAN = null;
        $item->ItemAttributes->EISBN = $eisbn;
        $offer = $item->Offers->Offer;

        $results = $this->client->populatePriceData($offer, $item, $merchant_id);

        $this->assertEquals($eisbn, $results->isbn13);
    }

    private function generateApiResponse($count = 3, $offers = 1)
    {
        $cc = 0;
        while($cc < $count) {
            $items[] = $this->generateApiItem($offers);
            $cc++;
        }

        $results = (object) [
            'Items' => (object) [
                'Item' => $items,
                'Request' => (object) [
                    'IsValid' => true,
                ],
            ],
        ];

        return $results;
    }

    private function generateApiItem($offers = 1)
    {
        $offers = $this->generateOffers($offers);

        return (object) [
            'DetailPageURL' => uniqid(),
            'ItemAttributes' => (object) [
                'Author' => 'Jonathan Kellerman',
                'Binding' => 'Paperback',
                'EAN' => '9780000000187',
                'ISBN' => '0000000183',
                'EISBN' => '9780000000187',
                'Label' => 'Harpercollins Publisher',
                'Manufacturer' => 'Harpercollins Publisher',
                'PackageQuantity' => '1',
                'ProductGroup' => 'Book',
                'ProductTypeName' => 'ABIS_BOOK',
                'PublicationDate' => '1988-01-01',
                'Publisher' => 'Harpercollins Publisher',
                'Studio' => 'Harpercollins Publisher',
                'Title' => 'Survival of the Fittest',
            ],
            'Offers' => (object) [
                'Offer' => $offers
            ]
        ];
    }

    private function generateOffers($offers = 1)
    {
        $singleOffer = (object) [
            'OfferAttributes' => (object) [
                'Condition' => ['New', 'Poor'][rand(0,1)]
            ],
            'OfferListing' => (object) [
                'OfferListingId' => uniqid(),
                'Price' => (object) [
                    'Amount' => '963',
                    'CurrencyCode' => 'USD',
                    'FormattedPrice' => '$9.63'
                ],
                'Availability' => 'Usually ships in 2-3 business days',
                'AvailabilityAttributes' => (object) [
                        'AvailabilityType' => 'now',
                        'MinimumHours' => '48',
                        'MaximumHours' => '72',
                ],
                'IsEligibleForSuperSaverShipping' => 0,
            ],
        ];
        if ($offers > 1) {
            $cc = 0;
            while ($cc <= $offers) {
                $allOffers[] = $singleOffer;
                $cc++;
            }
            return $allOffers;
        } else {
            return $singleOffer;
        }
    }
}
