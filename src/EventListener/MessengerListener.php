<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Beerfranz\ExecutionContext\Messenger\MessageInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class MessengerListener implements EventSubscriberInterface
{

    public function __construct(
        private ExecutionContext $executionContext,
        private readonly ManagerRegistry $registry,
        private readonly ?string $userClass = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'onMessageReceived',
        ];
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();
        // $stamps = $envelope->all();
        // $transportName = $event->getReceiverName();

        $this->executionContext->reset();

        if ($message instanceof MessageInterface)
        {
            $userId = $message->getUserId();
            $user = $this->executionContext->resolveUserId($userId, ExecutionContext::MESSENGER);
            $dryrun = $message->isDrunRun();

            $this->executionContext->init(
                ExecutionContext::MESSENGER,
                $user,
                $dryrun,
            );
        } else {
            $this->executionContext->init(
                ExecutionContext::MESSENGER,
                null,
                null,
            );
        }
    }
}

