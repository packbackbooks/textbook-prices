<?php namespace Packback\Prices\Test;

use Packback\Prices\PriceDto;
use Mockery as m;

class PriceDtoTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dto = new PriceDto();
    }

    public function testItSetsValidParametersInPriceDto()
    {
        $isbn13 = uniqid();
        $this->dto->__set('isbn13', $isbn13);

        $this->assertEquals($isbn13, $this->dto->isbn13);
    }

    public function testItThrowsExceptionIfSetInvalidParametersInPriceDto()
    {
        $isbn13 = uniqid();
        $this->setExpectedException('OutOfRangeException');
        $this->dto->__set('isbn', $isbn13);
    }

    public function testItGetsValidParametersInPriceDto()
    {
        $isbn13 = uniqid();
        $this->dto->isbn13 = $isbn13;

        $this->assertEquals($isbn13, $this->dto->__get('isbn13'));
    }

    public function testItThrowsExceptionIfGetInvalidParametersInPriceDto()
    {
        $isbn13 = uniqid();
        $this->setExpectedException('OutOfRangeException');
        $this->dto->__get('isbn');
    }

    public function testItReturnsTrueIfAttributeSet()
    {
        $isbn13 = uniqid();
        $this->dto->isbn13 = $isbn13;

        $this->assertTrue($this->dto->__isset('isbn13'));
    }
}
