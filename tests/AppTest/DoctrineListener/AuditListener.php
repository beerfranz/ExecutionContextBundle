<?php

namespace Beerfranz\ExecutionContext\AppTest\DoctrineListener;

use Beerfranz\ExecutionContext\AppTest\Entity\Audit;
use Beerfranz\ExecutionContext\AppTest\Message\AuditBackupMessage;
use Beerfranz\ExecutionContext\ExecutionContext;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;


#[AsEntityListener]
class AuditListener
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected ExecutionContext $executionContext,
        protected LoggerInterface $logger,
    ) { }

    public function postPersist(Audit $audit, PostPersistEventArgs $event)
    {
        $this->logger->info('audit created for '.$audit->getSubject());
        $message = $this->executionContext->newMessage(AuditBackupMessage::class, 'audit-'.$audit->getSubject());

        $message->auditSubjectKind = $audit->getSubjectKind();
        $message->auditSubjectId = $audit->getSubject();
        $message->setProducerId('audit');
        $message->setTenantId($audit->getTenantIdentifier());
        $message->setEventType('tenant.created');
        $message->auditAction = 'create';
        $message->setUserId($this->executionContext->getUser()->getUserIdentifier());
        $this->bus->dispatch($message);

    }
}
