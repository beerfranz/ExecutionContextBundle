<?php

namespace Beerfranz\ExecutionContext\Entity;

interface ExecutionContextTenantInterface
{
	public function getTenantIdentifier(): string;
}
