<?php

namespace PixelPay\Sdk\Resources;

use PixelPay\Sdk\Exceptions\IllegalStateException;

class Locations
{
	/**
	 * Countries resource repository
	 *
	 * @var array
	 */
	private static $countries_repository;

	/**
	 * States resource repository
	 *
	 * @var array
	 */
	private static $states_repository;

	/**
	 * Prevent implicit public contructor
	 */
	public function __construct()
	{
		throw new IllegalStateException('Utility class');
	}

	/**
	 * Verify respository is available
	 */
	private static function checkRepository()
	{
		if (empty(Locations::$countries_repository) || empty(Locations::$states_repository)) {
			$file_countries = file_get_contents(__DIR__ . '/../../assets/countries.json');
			$file_states = file_get_contents(__DIR__ . '/../../assets/states.json');

			self::$countries_repository = json_decode($file_countries, true);
			self::$states_repository = json_decode($file_states, true);
		}
	}

	/**
	 * Return a list of countries
	 *
	 * @return array
	 */
	public static function countriesList()
	{
		self::checkRepository();

		return self::$countries_repository;
	}

	/**
	 * Get states list by country ISO code
	 *
	 * @param string $country_code
	 * @return array|null
	 */
	public static function statesList(string $country_code)
	{
		self::checkRepository();

		return self::$states_repository[$country_code] ?? null;
	}
}
