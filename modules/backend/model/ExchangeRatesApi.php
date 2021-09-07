<?php


class ExchangeRatesApi implements Converter
{
	private CONST API_BASE_URL = 'http://api.exchangeratesapi.io/v1/latest?access_key=a50360a2cbaae4482314bbdce3673406';

	/**
	 * @param string $from
	 * @param string $to
	 * @param float $fromValue
	 * @return array|false[]
	 */
	public function convertCurrency(string $from, string $to, float $fromValue)
	{
		if (!Currencies::isValid($from) || !Currencies::isValid($to)) {
			return [
				'success' => false,
				'message' => 'Invalid currency please use :' . implode(', ', Currencies::CURRENCIES)
			];
		}

		try {
			$response = Curl::load(self::API_BASE_URL . "&symbols={$to},{$from}");

			$response = json_decode($response, true);

			// no response
			if (!$response || !$response['success']) {
				return ['success' => false];
			}

			// free plan, only EUR can be the base
			$baseRatioToFrom = $response['rates'][$from];

			$rate = (1 / $baseRatioToFrom) * $response['rates'][$to];

			return [
				'from' => $from,
				'to' => $to,
				'fromValue' => $fromValue,
				'result' => $rate * $fromValue
			];
		} catch (Exception $e) {
			return ['success' => false];
		}
	}

	/**
	 * @param $fromCurrency
	 * @return array|false[]
	 */
	public function getRatesFor($fromCurrency)
	{
		if (!Currencies::isValid($fromCurrency)) {
			return [
				'success' => false,
				'message' => 'Invalid currency please use :' . implode(', ', Currencies::CURRENCIES)
			];
		}

		$result = [
			'name' => $fromCurrency,
			'rates' => []
		];

		try {
			$currencyList = implode(",", Currencies::CURRENCIES);

			$response = Curl::load(self::API_BASE_URL . "&symbols={$currencyList}");

			$response = json_decode($response, true);

			// no response
			if (!$response || !$response['success']) {
				return ['success' => false];
			}

			// free plan, only EUR can be the base
			$baseRatioToFrom = $response['rates'][$response['base']];

			$rate = (1 / $baseRatioToFrom) * $response['rates'][$fromCurrency];

			foreach (Currencies::CURRENCIES as $currency) {
				$result['rates'][] = [
					'currency' => $currency,
					'rate' => $rate / $response['rates'][$currency]
				];
			}

			return $result;
		} catch (Exception $e) {
			return ['success' => false];
		}
	}
}