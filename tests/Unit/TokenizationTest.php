<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Billing;
use PixelPay\Sdk\Models\Card;
use PixelPay\Sdk\Models\Settings;
use PixelPay\Sdk\Requests\CardTokenization;
use PixelPay\Sdk\Responses\ErrorResponse;
use PixelPay\Sdk\Responses\InputErrorResponse;
use PixelPay\Sdk\Responses\SuccessResponse;
use PixelPay\Sdk\Services\Tokenization;

class TokenizationTest extends TestCase
{
	private function getCardModel(): Card
	{
		$card = new Card();

		$card->cardholder = 'Jhon Doe';
		$card->cvv2 = '009';
		$card->expire_month = 12;
		$card->expire_year = 2025;
		$card->number = '4684668223050294';

		return $card;
	}

	private function getBillingModel(): Billing
	{
		$billing = new Billing();
		$billing->address = 'Bo Andes';
		$billing->city = 'San Pedro Sula';
		$billing->country = 'HN';
		$billing->phone = '999-9999999';
		$billing->state = 'HN-CR';
		$billing->zip = '48338';

		return $billing;
	}

	private function setupService(): Tokenization
	{
		$settings = new Settings();
		$settings->setupSandbox();

		return new Tokenization($settings);
	}

	public function testFailRequestForInvalidMerchant()
	{
		$response = $this->setupService()->showCard('T-34e382e0-6f48-433b-b66d-cd2b5575939b');

		$this->assertFalse($response->success);
		$this->assertEquals(400, $response->getStatus());
		$this->assertTrue($response instanceof ErrorResponse);
	}

	public function testVaultCardFail()
	{
		$request = new CardTokenization();
		$card = $this->getCardModel();

		$request->setCard($card);

		$response = $this->setupService()->vaultCard($request);

		$this->assertFalse($response->success);
		$this->assertEquals(422, $response->getStatus());
		$this->assertTrue($response instanceof InputErrorResponse);
	}

	public function testVaultCardSuccess()
	{
		$request = new CardTokenization();
		$card = $this->getCardModel();
		$billing = $this->getBillingModel();

		$request->setCard($card);
		$request->setBilling($billing);

		$response = $this->setupService()->vaultCard($request);

		$this->assertTrue($response->success);
		$this->assertEquals(200, $response->getStatus());
		$this->assertTrue($response instanceof SuccessResponse);
	}

	public function testUpdateCardSuccess()
	{
		$request = new CardTokenization();

		$card = $this->getCardModel();
		$billing = $this->getBillingModel();

		$request->setCard($card);
		$request->setBilling($billing);

		$response = $this->setupService()->vaultCard($request);

		$update = new CardTokenization();

		$biling = new Billing();
		$biling->city = 'Choloma';

		$update->setBilling($billing);

		$response = $this->setupService()->updateCard($response->data['token'], $update);

		$this->assertTrue($response->success);
		$this->assertEquals(200, $response->getStatus());
		$this->assertTrue($response instanceof SuccessResponse);
	}

	public function testDeleteCard()
	{
		$request = new CardTokenization();

		$card = $this->getCardModel();
		$billing = $this->getBillingModel();

		$request->setCard($card);
		$request->setBilling($billing);

		$response = $this->setupService()->vaultCard($request);
		$response = $this->setupService()->deleteCard($response->data['token']);

		$this->assertTrue($response->success);
		$this->assertEquals(200, $response->getStatus());
		$this->assertTrue($response instanceof SuccessResponse);
	}
}
