<?php namespace Packback\Prices\Test;

use Packback\Prices\Clients\PriceClient;
use Mockery as m;

class PriceClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new PriceClient();
        $this->client->client = m::mock('GuzzleHttp\Client');
    }

    public function testAddParameterToQuery()
    {
        $parameter = [
            'key' => uniqid(),
            'value' => uniqid(),
        ];

        $results = $this->client->addParam($parameter['key'], $parameter['value']);

        $this->assertEquals($parameter['value'], $results->query[$parameter['key']]);
    }

    public function testSendGuzzleRequestFromValidQuery()
    {
        $key = uniqid();
        $value = uniqid();
        $params[$key] = $value;

        $guzzleResponse = m::mock();
        $this->client->query = $params;
        $xmlResponse = '<Element>'.$value.'</Element>';
        $queryString = '?'.http_build_query($this->client->query);

        $this->client->client->shouldReceive('get')
            ->with($queryString)
            ->andReturn($guzzleResponse);
        $guzzleResponse->shouldReceive('getStatusCode')
            ->andReturn('200');
        $guzzleResponse->shouldReceive('getBody')
            ->with(true)
            ->andReturn($xmlResponse);

        $results = $this->client->send();
        $this->assertEquals($value, $results[0]);
    }

    public function testSendGuzzleRequestFromValidQueryWith403()
    {
        $key = uniqid();
        $value = uniqid();
        $params[$key] = $value;

        $guzzleResponse = m::mock();
        $this->client->query = $params;
        $queryString = '?'.http_build_query($this->client->query);

        $this->client->client->shouldReceive('get')
            ->with($queryString)
            ->andReturn($guzzleResponse);
        $guzzleResponse->shouldReceive('getStatusCode')
            ->andReturn('403');

        $results = $this->client->send();
        $this->assertNull($results);
    }

    public function testSendGuzzleRequestFromInvalidQuery()
    {
        $key = uniqid();
        $value = uniqid();
        $params[$key] = $value;

        $guzzleResponse = m::mock();
        $this->client->query = $params;
        $queryString = '?'.http_build_query($this->client->query);

        $this->client->client->shouldReceive('get')
            ->with($queryString)
            ->andThrow('Exception');

        $results = $this->client->send();
    }

    public function testItCanGetConditionsFromString()
    {
        $conditions = [
            'new',
            'good',
            'acceptable',
            'poor',
        ];
        foreach ($conditions as $condition) {
            $result = $this->client->getConditionFromString($condition);
            $this->assertEquals($condition, $result);
        }

    }

    public function testItThrowsExceptionWhenConditionNotFound()
    {
        $string = uniqid();

        $this->setExpectedException("Exception");

        $this->client->getConditionFromString($string);
    }

}
