<?php

namespace PixelPay\Sdk\Requests;

use PixelPay\Sdk\Base\RequestBehaviour;

class VoidTransaction extends RequestBehaviour
{
	/**
	 * Payment UUID
	 *
	 * @var string
	 */
	public $payment_uuid;

	/**
	 * Reason for void the order
	 *
	 * @var string
	 */
	public $void_reason;
}
