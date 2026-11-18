<?php

namespace Beerfranz\ExecutionContext\AppTest\Message;

use Beerfranz\ExecutionContext\Messenger\Message;

class AuditMessage extends Message
{
	public ?string $auditAction = null;
	public ?string $auditSubjectKind = null;
	public ?string $auditSubjectId = null;
	public ?\DatetimeImmutable $auditTimestamp = null;
}
