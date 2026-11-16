<?php

namespace Beerfranz\ExecutionContext\AppTest\MessageHandler;

use Beerfranz\ExecutionContext\AppTest\Entity\Audit;
use Beerfranz\ExecutionContext\AppTest\Message\AuditMessage;
use Beerfranz\ExecutionContext\AppTest\Repository\AuditRepository;
use Beerfranz\ExecutionContext\ExecutionContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

/**
 * Save audit logs in the database
 **/
class AuditHandler
{

  public function __construct(
    protected AuditRepository $repo,
    protected ExecutionContext $executionContext,
    protected LoggerInterface $logger,
  ) { }

  #[AsMessageHandler]
  public function auditMessage(AuditMessage $message)
  {

    $datetime = $message->getTimestamp();
    $actor = $this->executionContext->getUser()->getUserIdentifier();

    $audit = (new Audit())
      ->setActor(Uuid::fromString($actor))
      ->setAction($message->auditAction)
      ->setSubjectKind($message->auditSubjectKind)
      ->setSubject($message->auditSubjectId)
      ->setDatetime($datetime)
      ->setCausationId($message->getCausationId())
      ->setCorrelationId($message->getCorrelationId())
    ;

    $this->repo->getEntityManager()->persist($audit);
    $this->repo->getEntityManager()->flush();

    $this->logger->info('{actor} {action} {kind}', [
      'actor' => $actor,
      'action' => $message->auditAction,
      'kind' => $message->auditSubjectKind,
    ]);

  }
}
