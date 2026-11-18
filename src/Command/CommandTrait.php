<?php

namespace Beerfranz\ExecutionContext\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

trait CommandTrait {

	protected function configureUserInput(): self
	{
		return $this->addOption(
			'user',
			'u',
			InputOption::VALUE_REQUIRED,
			'user'
		);
	}

	protected function configureDryrunInput(): self
	{
		return $this->addOption(
			'dryrun',
			'd',
			InputOption::VALUE_NONE,
			'Enable dry-run mode'
		);
	}

	protected function initializeExecutionContext(InputInterface $input, OutputInterface $output)
	{
		$this->executionContext->initFromCommand(
            $input->getOption('user'),
            (bool) $input->getOption('dryrun'),
        );
	}
}
