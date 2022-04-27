<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Billing;

class BillingTest extends TestCase
{
	public function testBillingIsEmpty()
	{
		$billing = new Billing();

		$this->assertNull($billing->address);
		$this->assertNull($billing->country);
		$this->assertNull($billing->state);
		$this->assertNull($billing->city);
		$this->assertNull($billing->zip);
		$this->assertNull($billing->phone);
	}

	public function testSetBillingModel()
	{
		$billing = new Billing();

		$billing->address = 'Bo Andes';
		$billing->country = 'HN';
		$billing->state = 'Cortes';
		$billing->city = 'Puerto Vallarta';
		$billing->zip = '48290';
		$billing->phone = '3221002040';

		$this->assertNotNull($billing->address);
		$this->assertNotNull($billing->country);
		$this->assertNotNull($billing->state);
		$this->assertNotNull($billing->city);
		$this->assertNotNull($billing->zip);
		$this->assertNotNull($billing->phone);

		$this->assertEquals('Bo Andes', $billing->address);
		$this->assertEquals('HN', $billing->country);
		$this->assertEquals('Cortes', $billing->state);
		$this->assertEquals('Puerto Vallarta', $billing->city);
		$this->assertEquals('48290', $billing->zip);
		$this->assertEquals('3221002040', $billing->phone);
	}
}
