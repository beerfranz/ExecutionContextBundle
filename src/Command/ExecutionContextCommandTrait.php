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
		$userIdentifier = $input->getOption('user');

		if ($userIdentifier !== null) {
			try {
				$user = $this->userProvider->loadUserByIdentifier($userIdentifier);
			} catch (UserNotFoundException $e) {
				throw new \InvalidArgumentException(
					sprintf('User "%s" not found. Did the idenfitier corresponding to security.providers.app_user_provider.entity.property key or user repository method loadUserByIdentifier()?'),
					0,
					$e,
				);
			}
		} else {
			$user = $context->generateUser('ROOT');
		}

		$context->init(
			ExecutionContext::COMMAND,
			$user,
			(bool) $input->getOption('dryrun'),
		);
	}

}
