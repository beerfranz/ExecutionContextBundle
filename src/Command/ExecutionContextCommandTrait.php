<?php

namespace Beerfranz\ExecutionContext\Command;

use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

trait ExecutionContextCommandTrait {

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

	// Usage: $this->setExecutionContext($this->executionContext, $input);
	protected function setExecutionContext(ExecutionContext $context, InputInterface $input): void
	{
		$context->reset();

		$user = null;
		$userId = $input->getOption('user');
		$dryrun = $input->getOption('dryrun');

		$user = $this->executionContext->resolveUserId($userId, ExecutionContext::COMMAND);

        $this->executionContext->init(
            ExecutionContext::COMMAND,
            $user,
            $dryrun,
        );
	}
}
