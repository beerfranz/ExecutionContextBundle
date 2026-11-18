<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Beerfranz\ExecutionContext\Messenger\MessageInterface;
use Symfony\Component\Uid\Uuid;

trait MessageTrait
{

	// Uniq ID of the message
	protected ?string $eventId = null;

	// Message timestamp
	protected ?string $timestamp = null;

	// Root parent message
	protected ?string $correlationId = null;

	// Direct message parent
	protected ?string $causationId = null;

	// User identifier
	protected ?string $userId = null;

	// Tenant identifier
	protected ?string $tenantId = null;

	// The Id of the producer of the message
	protected ?string $producerId = null;

	// Message version
	protected int $version = 1;

	protected bool $dryrun = false;
	
	public function __construct(?string $eventId = null) {
		if ($eventId === null)
			$eventId = Uuid::v7()->toString();
	    $this->eventId = $eventId;
	    $this->timestamp = new \DateTimeImmutable()->format('c');
	    $this->version = 1;
	    $this->correlationId = $eventId;
	    $this->causationId = null;
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

	public function getEventId(): ?string
	{
		return $this->eventId;
	}

	public function getCorrelationId(): ?string
	{
		return $this->correlationId;
	}

	public function getCausationId(): ?string
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

	public function getTimestamp(): ?string
	{
		return $this->timestamp;
	}

	public function getProducerId(): ?string
	{
		return $this->producerId;
	}

	public function setProducerId(?string $producerId): self
	{
		$this->producerId = $producerId;
		return $this;
	}
}
