<?php

namespace Beerfranz\ExecutionContext\AppTest\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Beerfranz\ExecutionContext\AppTest\DoctrineListener\AuditListener;
use Beerfranz\ExecutionContext\AppTest\Repository\AuditRepository;
use Beerfranz\ExecutionContext\Entity\ExecutionContextTenantInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuditRepository::class)]
#[ORM\EntityListeners([AuditListener::class])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'tenant' => 'exact',
    'subject' => 'exact',
    'actor' => 'exact',
    'subjectKind' => 'exact',
])]
class Audit implements ExecutionContextTenantInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: true)]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subjectKind = null;

    #[ORM\Column(type: 'uuid', nullable: false)]
    protected ?Uuid $subject = null;

    #[ORM\Column(type: 'uuid', nullable: false)]
    protected ?Uuid $actor = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datetime = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $correlationId = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $causationId = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $tenantId = null;

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

    public function getSubject(): ?Uuid
    {
        return $this->subject;
    }

    public function setSubject(Uuid $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getActor(): ?Uuid
    {
        return $this->actor;
    }

    public function setActor(Uuid $actor): static
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

    public function getCorrelationId(): ?Uuid
    {
        return $this->correlationId;
    }

    public function setCorrelationId(?Uuid $correlationId): static
    {
        $this->correlationId = $correlationId;

        return $this;
    }

    public function getCausationId(): ?Uuid
    {
        return $this->causationId;
    }

    public function setCausationId(?Uuid $causationId): static
    {
        $this->causationId = $causationId;

        return $this;
    }

    public function getTenantId(): ?Uuid
    {
        return $this->tenantId;
    }

    public function setTenantId(?Uuid $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    public function getTenantIdentifier(): string
    {
        return $this->tenantId->toString();
    }
}
