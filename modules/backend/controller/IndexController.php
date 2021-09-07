<?php


class IndexController
{
	public function actionIndex()
	{
		return "Have fun!";
	}

	/**
	 * @return array available currencies
	 */
	public function actionCurrencies()
	{
		$result = [];

		foreach (Currencies::CURRENCIES as $currency) {
			$result[] = ['name' => $currency];
		}

		return $result;
	}

	/**
	 * @param string $fromRate from currency to all available values
	 * @return mixed
	 */
	public function actionRates(string $fromRate)
	{
		$this->filterRequestType(['GET']);

		// no data available
		return $this->retryTemplate('getRatesFor', [strtoupper($fromRate)]);
	}

	function actionConvert(string $from, string $to, float $fromValue)
	{
		$this->filterRequestType(['POST']);

		if (empty($from) || empty($to) || empty($fromValue)) {
			// TODO pls give valid currency
		}

		return $this->retryTemplate('convertCurrency', [strtoupper($from), strtoupper($to), $fromValue]);
	}

	private function filterRequestType(array $allowedTypes) {
		if (!empty($allowedTypes) && !in_array($_SERVER['REQUEST_METHOD'], $allowedTypes)) {
			http_response_code(405);
			die();
		}
	}

	private function retryTemplate(string $callFunction, array $params = [])
	{
		$result = [];

		//resources class name
		$resources = [
			'FreeCurrencyConverterApi',
			'ExchangeRatesApi'
		];

		/**
		 * For 3rd resource we can use some kind of cache like Redis, or a simple text file.
		 * When the resource is available, we write the results to the cache/file and when it is down
		 * we can just simply read from it.
		 */

		foreach ($resources as $resource) {
			if (!class_exists($resource)) {
				// TODO no class exist
			}

			$resource = new $resource();

			try {
				$result = call_user_func_array([$resource, $callFunction], $params);

				if (isset($result['success']) && $result['success'] !== false) {
					break;
				}
			} catch (Exception $exception) {
				// log resource is not available
			}
		}

		return $result;
	}
}