<?php

namespace Beerfranz\ExecutionContext\Command;

use Beerfranz\ExecutionContext\Command\ExecutionContextCommandTrait;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Usage
 * Class MyCommand extends Command {
 * 		public function configure() {
 * 			parent::configure();
 * 		}
 * }
 **/
abstract class AbstractCommand extends SymfonyCommand
{
	use ExecutionContextCommandTrait;

	public function __construct(
		protected readonly ExecutionContext $executionContext,
		protected readonly UserProviderInterface $userProvider,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->configureUserInput()
			 ->configureDryrunInput();
	}
}
