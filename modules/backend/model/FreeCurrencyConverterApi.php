<?php


class FreeCurrencyConverterApi implements Converter
{
	private CONST API_BASE_URL = 'https://free.currconv.com/api/v7/convert?apiKey=96e4197b55a7bfdd6fbc&q=';

	/**
	 * @param string $from
	 * @param string $to
	 * @param float $fromValue
	 * @throws Exception
	 */
	public function convertCurrency(string $from, string $to, float $fromValue): array
	{
		if (!Currencies::isValid($from) || !Currencies::isValid($to)) {
			return [
				'success' => false,
				'message' => 'Invalid currency please use :' . implode(', ', Currencies::CURRENCIES)
			];
		}

		$result = $this->getRate($from, $to);

		if (!$result || empty($result)) {
			// todo nincs
		}

		$rate = array_pop($result['rates'])['val'];

		// Todo check if value is valid

		return [
			'from' => $from,
			'to' => $to,
			'fromValue' => $fromValue,
			'result' =>  $rate * $fromValue
		];
	}

	/**
	 * @param $from
	 * @param string $to
	 * @return array|false|mixed|object
	 * @throws Exception
	 */
	public function getRate($from, string $to = '')
	{
		$queryString = "{$from}_{$to}";
		$result = [
			'name' => $from,
			'rates' => []
		];

		if (empty($to)) {

			$limit = 2;
			$currencyCount = count(Currencies::CURRENCIES);
			// Free version limitation hack
			if ($currencyCount > 1) {
				$queryString = '';
				$count = 0;

				foreach (Currencies::CURRENCIES as $index => $currency) {
					$queryString .= $from . "_" . $currency . ",";
					if (++$count >= $limit || $currencyCount - $index < $limit) {

						try {
							$response = Curl::load(trim(self::API_BASE_URL . $queryString, ','));

							$response = json_decode($response, true);

							// no response
							if (!$response) {
								return ['success' => false];
							}

							foreach ($response['results'] as $rate) {
								$result['rates'][] = [
									'currency' => $rate['to'],
									'rate' => $rate['val']
								];
							}

						} catch (Exception $e) {
							// log

							return ['success' => false];
						}

						$count = 0;
						$queryString = '';
					}
				}
			}
		} else {
			$response = Curl::load(self::API_BASE_URL . $queryString);

			$response = json_decode($response, true);
			// no response
			if (!$response) {
				return ['success' => false];
			}

			$result['rates'] = $response['results'];
		}

		return $result;
	}

	public function getRatesFor($fromCurrency)
	{
		if (!Currencies::isValid($fromCurrency)) {
			return [
				'success' => false,
				'message' => 'Invalid currency please use :' . implode(', ', Currencies::CURRENCIES)
			];
		}

		return $this->getRate($fromCurrency);
	}
}