<?php

namespace PixelPay\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PixelPay\Sdk\Models\Settings;

class SettingsTest extends TestCase
{
	public function testSetupMerchant()
	{
		$settings = new Settings();
		$endpoint = 'https://pixel-pay.com';
		$this->assertEquals('https://pixelpay.app', $settings->endpoint);

		$settings->setupEndpoint($endpoint);
		$this->assertEquals('https://pixel-pay.com', $settings->endpoint);
	}

	public function testSetupCredentials()
	{
		$settings = new Settings();
		$merchant_key = '2212294583';
		$merchant_hash = 'c7dd7a6cf3e47417edb3456c22218a5d';

		$this->assertNull($settings->auth_key);
		$this->assertNull($settings->auth_hash);

		$settings->setupCredentials($merchant_key, $merchant_hash);

		$this->assertNotNull($settings->auth_key);
		$this->assertNotNull($settings->auth_hash);
	}

	public function testSetupPlatformUser()
	{
		$settings = new Settings();
		$merchant_user = 'c218697e71f1c8bb9efee480a9e3b90a930d8cfa662db3e3b78879ea38363dad7eefb9ebe8db582ba35cc46fa7d80baedf778195faecaea66d9222036793c937';

		$this->assertNull($settings->auth_user);
		$settings->setupPlatformUser($merchant_user);
		$this->assertNotNull($settings->auth_user);
	}

	public function testSetupEnvironment()
	{
		$settings = new Settings();

		$this->assertNull($settings->environment);

		$settings->setupEnvironment('test');
		$this->assertNotNull($settings->environment);
		$this->assertEquals('test', $settings->environment);
	}

	public function testSetupSandbox()
	{
		$settings = new Settings();

		$this->assertNotNull($settings->endpoint);
		$this->assertNull($settings->auth_key);
		$this->assertNull($settings->auth_hash);
		$this->assertNull($settings->environment);

		$settings->setupSandbox();
		$this->assertEquals('https://pixel-pay.com', $settings->endpoint);
		$this->assertEquals('1234567890', $settings->auth_key);
		$this->assertEquals('36cdf8271723276cb6f94904f8bde4b6', $settings->auth_hash);
		$this->assertEquals('sandbox', $settings->environment);
	}
}
