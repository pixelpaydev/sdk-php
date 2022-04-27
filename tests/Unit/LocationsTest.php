<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Resources\Locations;

class LocationsTest extends TestCase
{
	public function testCountriesList()
	{
		$this->assertTrue(!empty(Locations::countriesList()));
		$this->assertEquals('Honduras', Locations::countriesList()['HN']);
	}

	public function testStatesList()
	{
		$this->assertTrue(!empty(Locations::statesList('HN')));
		$this->assertEquals('Cortes', Locations::statesList('HN')['HN-CR']);
	}
}
