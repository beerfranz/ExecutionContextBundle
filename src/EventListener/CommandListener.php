<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandListener implements EventSubscriberInterface
{
    public function __construct(
        private ExecutionContext $executionContext,
        private readonly ?string $userClass = null,

    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND   => 'onCommand'
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if (! $command instanceof ExecutionContextCommandInterface)
            return;

        $input   = $event->getInput();
        $userId = $input->getOption('user');

        $user = $this->executionContext->resolveUserId($userId, ExecutionContext::COMMAND);

        $this->executionContext->init(
            ExecutionContext::COMMAND,
            $user,
            (bool) $input->getOption('dryrun'),
        );
    }
}
