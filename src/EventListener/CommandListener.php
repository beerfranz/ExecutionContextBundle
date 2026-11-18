<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandListener implements EventSubscriberInterface
{
    public function __construct(
        private ExecutionContext $executionContext,

    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 100],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->executionContext->initFromEventCommand($event);
    }
}
