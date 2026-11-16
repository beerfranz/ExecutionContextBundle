<?php

namespace Beerfranz\ExecutionContext\AppTest\MessageHandler;

use Beerfranz\ExecutionContext\AppTest\Entity\AuditBackup;
use Beerfranz\ExecutionContext\AppTest\Message\AuditBackupMessage;
use Beerfranz\ExecutionContext\AppTest\Repository\AuditBackupRepository;
use Beerfranz\ExecutionContext\ExecutionContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

/**
 * Save audit logs in the database
 **/
class AuditBackupHandler
{

  public function __construct(
    protected AuditBackupRepository $repo,
    protected ExecutionContext $executionContext,
    protected LoggerInterface $logger,
  ) { }

  #[AsMessageHandler]
  public function auditMessage(AuditBackupMessage $message)
  {

    $datetime = $message->getTimestamp();
    $actor = $this->executionContext->getUser()->getUserIdentifier();

    $audit = (new AuditBackup())
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
      'actor' => $message->getUserId(),
      'action' => $message->auditAction,
      'kind' => $message->auditSubjectKind,
    ]);

  }
}
