<?php

namespace Beerfranz\ExecutionContext\AppTest\Service;

use Beerfranz\ExecutionContext\AppTest\Entity\Tenant;
use Beerfranz\ExecutionContext\AppTest\Repository\TenantRepository;
use Beerfranz\ExecutionContext\AppTest\Voter\TenantVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;


class TenantService
{
	public function __construct(
		private EntityManagerInterface $em,
		private Security $security,
		private TenantRepository $repo,
	) { }

	public function create(string $name): Tenant
	{
		$decision = $this->security->getAccessDecision(TenantVoter::WRITE, null);
        if (!$decision->isGranted) {
			throw new AccessDeniedException($decision->getMessage());
        }

		$tenant = (new Tenant())
			->setName($name)
			->setUuid(Uuid::v7());

		$this->em->persist($tenant);
		$this->em->flush();

		return $tenant;
	}

	public function find(): ?Array
	{
		$decision = $this->security->getAccessDecision(TenantVoter::READ, null);
        if (!$decision->isGranted) {
			throw new AccessDeniedException($decision->getMessage());
        }

        return $this->repo->findAll();
	}
}
