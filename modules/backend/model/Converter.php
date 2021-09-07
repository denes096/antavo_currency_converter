<?php


interface Converter
{
	public function convertCurrency(string $from, string $to, float $fromValue);

	public function getRatesFor($fromCurrency);
}