<?php

namespace Beerfranz\ExecutionContext\Command;

use Beerfranz\ExecutionContext\Command\CommandInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

trait ExecutionContextCommandTrait {

	public function initFromEventCommand(ConsoleCommandEvent $event): void
    {
		$command = $event->getCommand();

		if ($command instanceof CommandInterface)
        {
            $input   = $event->getInput();
            $userId = $input->getOption('user');
            $dryrun = (bool) $input->getOption('dryrun');
            $this->initFromCommand($userId, $dryrun);
        } else {
            $this->initFromCommand();
        }
    }

    public function initFromCommand(?string $userId = null, bool $dryrun = false): void
    {
    	$user = $this->resolveUserId($userId, self::COMMAND);

        $this->init(
            self::COMMAND,
            $user,
            $dryrun,
        );
    }
}
