<?php

namespace Beerfranz\ExecutionContext\Exception;

class DryRunException extends \Exception
{
	public function __construct(
		protected mixed $data = null,
	) {}

	public function getData(): mixed
	{
		return $this->data;
	}
}
