<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Beerfranz\ExecutionContext\Messenger\MessageInterface;
use Symfony\Component\Uid\Uuid;

trait MessageTrait
{

	// Uniq ID of the message
	protected ?Uuid $eventId = null;

	// Message timestamp
	protected ?\DateTimeImmutable $timestamp = null;

	// Root parent message
	protected ?Uuid $correlationId = null;

	// Direct message parent
	protected ?Uuid $causationId = null;

	// User identifier
	protected ?string $userId = null;

	// Tenant identifier
	protected ?string $tenantId = null;

	// Message version
	protected int $version = 1;

	protected bool $dryrun = false;
	
	public function __construct() {
	    $this->eventId = Uuid::v7();
	    $this->timestamp = new \DateTimeImmutable();
	    $this->version = 1;
	    $this->correlationId = Uuid::v7();
  	}

	public function setParent(MessageInterface $parent)
  	{
	    $this->correlationId = $parent->getCorrelationId();
	    $this->causationId = $parent->getEventId();
	    $this->userId = $parent->getUserId();
	    $this->tenantId = $parent->getTenantId();
  	}

	public function getUserId(): ?string
	{
		return $this->userId;
	}

	public function setUserId(?string $userId): self
	{
		$this->userId = $userId;
		return $this;
	}

	public function getTenantId(): ?string
	{
		return $this->tenantId;
	}

	public function setTenantId(?string $tenantId): self
	{
		$this->tenantId = $tenantId;
		return $this;
	}

	public function getEventId(): ?Uuid
	{
		return $this->eventId;
	}

	public function getCorrelationId(): ?Uuid
	{
		return $this->correlationId;
	}

	public function getCausationId(): ?Uuid
	{
		return $this->causationId;
	}

	public function setDryRun(bool $dryrun): self
	{
		$this->dryrun = $dryrun;
		return $this;
	}

	public function isDrunRun(): bool
	{
		return $this->dryrun;
	}

	public function getVersion(): ?int
	{
		return $this->version;
	}

	public function getTimestamp(): ?\DateTimeImmutable
	{
		return $this->timestamp;
	}
}
