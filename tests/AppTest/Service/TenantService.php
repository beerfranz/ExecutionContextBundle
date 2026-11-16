<?php

namespace Beerfranz\ExecutionContext\AppTest\Service;

use Beerfranz\ExecutionContext\AppTest\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TenantService
{
	public function __construct(
		private EntityManagerInterface $em
	) { }

	public function create(string $name): Tenant
	{
		$tenant = (new Tenant())
			->setName($name)
			->setUuid(Uuid::v7());

		$this->em->persist($tenant);
		$this->em->flush();

		return $tenant;
	}
}
