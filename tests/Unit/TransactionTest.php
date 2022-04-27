<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Entities\TransactionResult;
use PixelPay\Sdk\Exceptions\InvalidCredentialsException;
use PixelPay\Sdk\Models\Billing;
use PixelPay\Sdk\Models\Card;
use PixelPay\Sdk\Models\Item;
use PixelPay\Sdk\Models\Order;
use PixelPay\Sdk\Models\Settings;
use PixelPay\Sdk\Requests\SaleTransaction;
use PixelPay\Sdk\Responses\ErrorResponse;
use PixelPay\Sdk\Responses\FailureResponse;
use PixelPay\Sdk\Responses\NotFoundResponse;
use PixelPay\Sdk\Responses\PaymentDeclinedResponse;
use PixelPay\Sdk\Responses\PreconditionalResponse;
use PixelPay\Sdk\Responses\TimeoutResponse;
use PixelPay\Sdk\Services\Transaction;

class TransactionTest extends TestCase
{
	private function randomString(int $length)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string = '';

		for ($i = 0; $i < $length; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$random_string .= $characters[$index];
		}

		return $random_string;
	}

	private function getCardModel(): Card
	{
		$card = new Card();

		$card->cardholder = 'Jhon Doe';
		$card->cvv2 = '009';
		$card->expire_month = 12;
		$card->expire_year = 2025;
		$card->number = '4111111111111111';

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

	private function getOrderModel(float $amount): Order
	{
		$item = new Item();
		$item->code = '00001';
		$item->title = 'Example product';
		$item->price = 1.99;
		$item->qty = 2;

		$order = new Order();
		$order->id = $this->randomString(10);
		$order->amount = $amount;
		$order->currency = 'HNL';
		$order->customer_email = 'carlos@pixel.hn';
		$order->customer_name = 'Carlos Agaton';

		return $order;
	}

	private function proccessPreconditionalTest(int $case_number)
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel($case_number * 1.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(412, $response->getStatus());
		$this->assertTrue($response instanceof PreconditionalResponse);
	}

	public function testInvalidCredentials()
	{
		$this->expectException(InvalidCredentialsException::class);

		$settings = new Settings();
		$settings->setupSandbox();
		$settings->setupEnvironment('testing');
		$settings->auth_hash = '';

		$service = new Transaction($settings);
		$request = new SaleTransaction();

		$service->doSale($request);
	}

	public function testErrorValidationSaleTransaction()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(422, $response->getStatus());
	}

	public function testFailSaleByInvalidCardFields()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$card = new Card();

		$request->setOrder($this->getOrderModel(1.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($card);

		$response = $service->doSale($request);


		$this->assertFalse($response->success);
		$this->assertEquals(422, $response->getStatus());
		$this->assertTrue($response->inputHasError('card_number'));
		$this->assertTrue($response->inputHasError('card_cvv'));
		$this->assertTrue($response->inputHasError('card_expire'));
		$this->assertTrue($response->inputHasError('card_holder'));
	}

	public function testFailSaleByInvalidBillingFields()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$billing = new Billing();

		$request->setOrder($this->getOrderModel(1.00));
		$request->setCard($this->getCardModel());
		$request->setBilling($billing);

		$response = $service->doSale($request);


		$this->assertFalse($response->success);
		$this->assertEquals(422, $response->getStatus());
		$this->assertTrue($response->inputHasError('billing_address'));
		$this->assertTrue($response->inputHasError('billing_city'));
		$this->assertTrue($response->inputHasError('billing_state'));
		$this->assertTrue($response->inputHasError('billing_country'));
		$this->assertTrue($response->inputHasError('billing_phone'));
	}

	public function testFailSaleByInvalidOrderFields()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$order = new Order();

		$request->setOrder($order);
		$request->setCard($this->getCardModel());
		$request->setBilling($this->getBillingModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(422, $response->getStatus());
		$this->assertTrue($response->inputHasError('customer_name'));
		$this->assertTrue($response->inputHasError('customer_email'));
		$this->assertTrue($response->inputHasError('order_id'));
		$this->assertTrue($response->inputHasError('order_currency'));
		$this->assertTrue($response->inputHasError('order_amount'));
	}

	public function testSuccessSaleTransaction()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(1.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertTrue($response->success);
		$this->assertEquals(200, $response->getStatus());
		$this->assertEquals('sale', $response->getData('transaction_type'));
	}

	public function testSuccessSaleTransactionWithEntity()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();


		$request->setOrder($this->getOrderModel(1.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$result = TransactionResult::fromResponse($response);

		$this->assertTrue($result->response_approved);
		$this->assertTrue($service->verifyPaymentHash($result->payment_hash, $request->order_id, '@s4ndb0x-abcd-1234-n1l4-p1x3l'));
		$this->assertEquals('sale', $result->transaction_type);
	}

	public function testDeclinedSaleTransaction()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(2.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(402, $response->getStatus());
		$this->assertTrue($response instanceof PaymentDeclinedResponse);
	}

	public function testInvalidSetupMerchantSettings()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(3.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(400, $response->getStatus());
		$this->assertTrue($response instanceof ErrorResponse);
	}

	public function testFailedTransactionDueCardReport()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(4.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(402, $response->getStatus());
		$this->assertTrue($response instanceof PaymentDeclinedResponse);
	}

	public function testPaymentNotFount()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(5.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(406, $response->getStatus());
		$this->assertTrue($response instanceof NotFoundResponse);
	}

	public function testFailByLimitsExceeded()
	{
		$this->proccessPreconditionalTest(6);
	}

	public function testGeneralSystemFailure()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(7.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(500, $response->getStatus());
		$this->assertTrue($response instanceof FailureResponse);
	}

	public function testTimeoutError()
	{
		$settings = new Settings();
		$settings->setupSandbox();

		$service = new Transaction($settings);
		$request = new SaleTransaction();
		$request->setOrder($this->getOrderModel(8.00));
		$request->setBilling($this->getBillingModel());
		$request->setCard($this->getCardModel());

		$response = $service->doSale($request);

		$this->assertFalse($response->success);
		$this->assertEquals(408, $response->getStatus());
		$this->assertTrue($response instanceof TimeoutResponse);
	}

	public function testTransactionAmountExceeded()
	{
		$this->proccessPreconditionalTest(9);
	}

	public function testTransactionLimitExceeded()
	{
		$this->proccessPreconditionalTest(10);
	}

	public function testConfigurationLimits()
	{
		$this->proccessPreconditionalTest(11);
	}
}
