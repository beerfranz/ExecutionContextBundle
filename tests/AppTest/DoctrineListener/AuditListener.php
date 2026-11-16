<?php

namespace Beerfranz\ExecutionContext\AppTest\DoctrineListener;

use Beerfranz\ExecutionContext\AppTest\Entity\Audit;
use Beerfranz\ExecutionContext\AppTest\Message\AuditBackupMessage;
use Beerfranz\ExecutionContext\ExecutionContext;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener]
class AuditListener
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected ExecutionContext $executionContext,
    ) { }

    public function postPersist(Audit $audit, PostPersistEventArgs $event)
    {
        $message = new AuditBackupMessage();

        $message->auditSubjectKind = $audit->getSubjectKind();
        $message->auditSubjectId = $audit->getSubject();
        $message->source = 'audit';
        $message->setTenantId($audit->getSubject());
        $message->eventType = 'tenant.created';
        $message->auditAction = 'create';
        $message->setUserId($this->executionContext->getUser()->getUserIdentifier());
        $this->bus->dispatch($message);

    }
}
