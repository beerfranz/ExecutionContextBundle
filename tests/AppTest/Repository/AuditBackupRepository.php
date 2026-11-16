<?php

namespace Beerfranz\ExecutionContext\AppTest\Repository;

use Beerfranz\ExecutionContext\AppTest\Entity\AuditBackup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditBackup>
 */
class AuditBackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditBackup::class);
    }
}
