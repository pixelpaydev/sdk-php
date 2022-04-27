<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Card;

class CardTest extends TestCase
{
	public function testCardIsEmpty()
	{
		$card = new Card();

		$this->assertNull($card->number);
		$this->assertNull($card->cvv2);
		$this->assertEquals(0, $card->expire_month);
		$this->assertEquals(0, $card->expire_year);
		$this->assertNull($card->cardholder);
	}

	public function testSetupCard()
	{
		$card = new Card();

		$card->number = '4111 4111 4111 4111';
		$card->cvv2 = '009';
		$card->expire_year = 2025;
		$card->expire_month = 12;
		$card->cardholder = 'Carlos Agaton';

		$this->assertNotNull($card->number);
		$this->assertNotNull($card->cvv2);
		$this->assertNotNull($card->expire_month);
		$this->assertNotNull($card->expire_year);
		$this->assertNotNull($card->cardholder);

		$this->assertEquals('4111 4111 4111 4111', $card->number);
		$this->assertEquals('009', $card->cvv2);
		$this->assertEquals(12, $card->expire_month);
		$this->assertEquals(2025, $card->expire_year);
		$this->assertEquals('Carlos Agaton', $card->cardholder);
	}

	public function testGetExpireFormat()
	{
		$card = new Card();

		$card->expire_month = 12;
		$card->expire_year = 2025;

		$this->assertEquals('2512', $card->getExpireFormat());
	}
}
