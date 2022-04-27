<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Item;
use PixelPay\Sdk\Models\Order;

class OrderTest extends TestCase
{
	public function testOrderIsEmpty()
	{
		$order = new Order();

		$this->assertNull($order->id);
		$this->assertNull($order->currency);
		$this->assertEquals(0.00, $order->amount);
		$this->assertEquals(0.00, $order->tax_amount);
		$this->assertEquals(0.00, $order->shipping_amount);
		$this->assertTrue(empty($order->content));
		$this->assertTrue(empty($order->extras));
		$this->assertNull($order->note);
		$this->assertNull($order->callback_url);
		$this->assertNull($order->customer_name);
		$this->assertNull($order->customer_email);
	}

	public function testSetOrder()
	{
		$order = new Order();

		$order->id = 'TEST-CASE';
		$order->currency = 'MXN';
		$order->amount = 10.00;
		$order->tax_amount = 5.00;
		$order->shipping_amount = 5.00;
		$order->note = 'Nota';
		$order->callback_url = 'https://www.pixel.hn/es';
		$order->customer_name = 'Jhon Doe';
		$order->customer_email = 'jhond@pixel.hn';

		$this->assertNotNull($order->id);
		$this->assertNotNull($order->currency);
		$this->assertNotNull($order->amount);
		$this->assertNotNull($order->tax_amount);
		$this->assertNotNull($order->shipping_amount);
		$this->assertTrue(empty($order->content));
		$this->assertTrue(empty($order->extras));
		$this->assertNotNull($order->note);
		$this->assertNotNull($order->callback_url);
		$this->assertNotNull($order->customer_name);
		$this->assertNotNull($order->customer_email);
		$this->assertEquals(10.00, $order->amount);

		$order->totalize();

		$this->assertEquals(10.00, $order->amount);
	}

	public function testOrderWithItems()
	{
		$order = new Order();
		$item = new Item();

		$item->code = 'XCTA-1u';
		$item->title = 'XCTA-TEST';
		$item->price = 5.00;
		$item->qty = 5;
		$item->tax = 10;

		$order->addItem($item);
		$order->totalize();

		$this->assertEquals($item->price * $item->qty, $order->amount);
		$this->assertEquals($item->tax * $item->qty, $order->tax_amount);
	}

	public function testOrderWithExtras()
	{
		$order = new Order();
		$order->addExtra('id', '123');

		$this->assertFalse(empty($order->extras));
		$this->assertFalse(array_key_exists('order', $order->extras));
		$this->assertTrue(array_key_exists('id', $order->extras));
	}
}
