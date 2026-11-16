<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Symfony\Component\Uid\Uuid;

interface MessageInterface
{
	public function getEventId(): ?Uuid;
	public function getCorrelationId(): ?Uuid;
	public function getCausationId(): ?Uuid;
	public function getUserId(): ?string;
	public function getTenantId(): ?string;
	public function isDrunRun(): bool;
}
