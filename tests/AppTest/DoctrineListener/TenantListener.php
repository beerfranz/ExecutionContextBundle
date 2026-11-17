<?php

namespace Beerfranz\ExecutionContext\AppTest\DoctrineListener;

use Beerfranz\ExecutionContext\AppTest\Entity\Tenant;
use Beerfranz\ExecutionContext\AppTest\Message\AuditMessage;
use Beerfranz\ExecutionContext\Exception\DryRunException;
use Beerfranz\ExecutionContext\ExecutionContext;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener]
class TenantListener
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected ExecutionContext $executionContext,
    ) { }

    public function postPersist(Tenant $tenant, PostPersistEventArgs $event)
    {
        $message = new AuditMessage();

        $message->auditSubjectKind = 'tenant';
        $message->auditSubjectId = $tenant->getUuid();
        $message->source = 'tenant';
        $message->setTenantId($tenant->getTenantIdentifier());
        $message->eventType = 'tenant.created';
        $message->auditAction = 'create';
        $message->setUserId($this->executionContext->getUser()->getUserIdentifier());
        $this->bus->dispatch($message);
    }

    public function prePersist(Tenant $tenant, PrePersistEventArgs $event)
    {
        if ($this->executionContext->isDryRun())
            throw new DryRunException([ 'tenant' => $tenant, 'dryrun' => true]);
    }
}
