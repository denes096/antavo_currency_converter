<?php


class Currencies
{
	public const CURRENCIES = [
		'USD',
		'HUF',
		'EUR',
		'GBP',
	];

	public static function isValid(string $currency):bool {
		return in_array($currency, self::CURRENCIES);
	}
}