<?php

namespace Beerfranz\ExecutionContext\Exception;

class DryRunException
{
	public function __construct(
		protected mixed $data = null,
	)

	public function getData(): mixed
	{
		return $data;
	}
}
