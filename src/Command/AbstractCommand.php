<?php

namespace Beerfranz\ExecutionContext\Command;

use Beerfranz\ExecutionContext\Command\CommandTrait;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Usage
 * Class MyCommand extends AbstractCommand {
 * 		public function configure() {
 * 			parent::configure();
 * 		}
  * 	public function initialize(InputInterface $input, OutputInterface $output) {
 * 			parent::initialize($input, $output);
 * 		}
 * }
 **/
abstract class AbstractCommand extends Command
{
	use CommandTrait;

	public function __construct(
		protected readonly ExecutionContext $executionContext,
	) {
		parent::__construct();
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void
	{
        $this->initializeExecutionContext($input, $output);
    }

	protected function configure(): void
	{
		$this->configureUserInput()
			 ->configureDryrunInput();
	}
}
