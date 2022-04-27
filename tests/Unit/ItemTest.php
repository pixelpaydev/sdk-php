<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Item;

class ItemTest extends TestCase
{
	public function testItemEmpty()
	{
		$item = new Item();

		$this->assertEquals(0.00, $item->price);
		$this->assertEquals(1, $item->qty);
		$this->assertEquals(0.00, $item->tax);
		$this->assertEquals(0.00, $item->total);

		$this->assertNull($item->code);
		$this->assertNull($item->title);
	}

	public function testSetItem()
	{
		$item = new Item();

		$item->code = 'ITEM-1';
		$item->title = 'Nintendo Swith';
		$item->price = 6799;
		$item->qty = 1;
		$item->tax = 1072;
		$item->totalize();

		$this->assertEquals(6799, $item->total);
		$item->qty = 2;
		$this->assertNotEquals(6799 * 2, $item->total);
		$item->totalize();
		$this->assertEquals(6799 * 2, $item->total);
	}
}
