<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Beerfranz\ExecutionContext\Command\ExecutionContextCommandInterface;
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

        $dryrun = false;

        if ($command instanceof ExecutionContextCommandInterface)
        {
            $input   = $event->getInput();
            $userId = $input->getOption('user');
            $dryrun = (bool) $input->getOption('dryrun');
        } else {
            $userId = null;
        }

        $user = $this->executionContext->resolveUserId($userId, ExecutionContext::COMMAND);

        $this->executionContext->init(
            ExecutionContext::COMMAND,
            $user,
            $dryrun,
        );
    }
}
