<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

class MessengerListener implements EventSubscriberInterface
{

    public function __construct(
        private ExecutionContext $executionContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => ['onMessageReceived', 100],
        ];
    }

    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $this->executionContext->initFromEvent($event);
    }
}
