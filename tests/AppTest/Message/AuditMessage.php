<?php

namespace Beerfranz\ExecutionContext\AppTest\Message;

use Beerfranz\ExecutionContext\Messenger\Message;

class AuditMessage extends Message
{

	// ex: user.created, component.updated
	public ?string $eventType = null;
	
	public int $payloadVersion = 1;
	public array $payload = [];

	public ?string $auditAction = null;
	public ?string $auditSubjectKind = null;
	public ?string $auditSubjectId = null;
	public ?\DatetimeImmutable $auditTimestamp = null;
}
