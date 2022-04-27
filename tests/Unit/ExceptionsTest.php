<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Base\Helpers;
use PixelPay\Sdk\Exceptions\IllegalStateException;
use PixelPay\Sdk\Exceptions\InvalidCredentialsException;
use PixelPay\Sdk\Exceptions\InvalidTransactionTypeException;
use PixelPay\Sdk\Models\Settings;
use PixelPay\Sdk\Requests\SaleTransaction;
use PixelPay\Sdk\Requests\StatusTransaction;
use PixelPay\Sdk\Services\Transaction;

class ExceptionsTest extends TestCase
{
	public function testInvalidMerchantCredentials()
	{
		$this->expectException(InvalidCredentialsException::class);

		$settings = new Settings();

		$request = new StatusTransaction();
		$request->payment_uuid = 'P-e21b135d-5605-4fc5-b31e-a9b77e1fe00';

		(new Transaction($settings))->getStatus($request);
	}

	public function testInvalidRequestType()
	{
		$this->expectException(InvalidTransactionTypeException::class);

		$settings = new Settings();
		$settings->setupSandbox();

		$request = new SaleTransaction();
		$request->withAuthenticationRequest();

		(new Transaction($settings))->doSale($request);
	}

	public function testIllegalState()
	{
		$this->expectException(IllegalStateException::class);

		(new Helpers());
	}
}
