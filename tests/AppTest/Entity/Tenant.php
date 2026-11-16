<?php

namespace Beerfranz\ExecutionContext\AppTest\Entity;

use Beerfranz\ExecutionContext\AppTest\DoctrineListener\TenantListener;
use Beerfranz\ExecutionContext\AppTest\Repository\TenantRepository;
use Beerfranz\ExecutionContext\Entity\ExecutionContextTenantInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\Table(name: 'tenant')]
#[ORM\EntityListeners([TenantListener::class])]
class Tenant implements ExecutionContextTenantInterface
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    protected ?Uuid $uuid = null;

    #[ORM\Column(length: 50, nullable: false, unique: true)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTenantIdentifier(): string
    {
        return $this->uuid->toString();
    }
}
