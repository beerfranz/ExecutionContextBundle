<?php

namespace Beerfranz\ExecutionContext\AppTest\Message;

use Beerfranz\ExecutionContext\Messenger\Message;
use Symfony\Component\Uid\Uuid;

class AuditBackupMessage extends Message
{
	// Emitter source
	public ?string $source = null;

	// ex: user.created, component.updated
	public ?string $eventType = null;

	public int $payloadVersion = 1;
	public array $payload = [];

	public ?string $auditAction = null;
	public ?string $auditSubjectKind = null;
	public ?Uuid $auditSubjectId = null;
	public ?\DatetimeImmutable $auditTimestamp = null;

}
