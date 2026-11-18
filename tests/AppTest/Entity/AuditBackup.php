<?php

namespace Beerfranz\ExecutionContext\AppTest\Entity;

use Beerfranz\ExecutionContext\AppTest\Repository\AuditBackupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditBackupRepository::class)]
class AuditBackup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subjectKind = null;

    #[ORM\Column(nullable: false)]
    protected ?string $subject = null;

    #[ORM\Column(nullable: false)]
    protected ?string $actor = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datetime = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(nullable: true)]
    private ?string $correlationId = null;

    #[ORM\Column(nullable: true)]
    private ?string $causationId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubjectKind(): ?string
    {
        return $this->subjectKind;
    }

    public function setSubjectKind(string $subjectKind): static
    {
        $this->subjectKind = $subjectKind;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getActor(): ?string
    {
        return $this->actor;
    }

    public function setActor(string $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    public function getDatetime(): ?\DateTimeImmutable
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeImmutable $datetime): static
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(?string $correlationId): static
    {
        $this->correlationId = $correlationId;

        return $this;
    }

    public function getCausationId(): ?string
    {
        return $this->causationId;
    }

    public function setCausationId(?string $causationId): static
    {
        $this->causationId = $causationId;

        return $this;
    }
}
