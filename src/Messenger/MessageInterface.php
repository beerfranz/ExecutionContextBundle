<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Symfony\Component\Uid\Uuid;

interface MessageInterface
{
	public function getEventId(): ?string;
	public function getCorrelationId(): ?string;
	public function getCausationId(): ?string;
	public function getUserId(): ?string;
	public function getTenantId(): ?string;
	public function isDrunRun(): bool;
}
