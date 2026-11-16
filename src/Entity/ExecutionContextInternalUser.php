<?php

namespace Beerfranz\ExecutionContext\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

final class ExecutionContextInternalUser implements UserInterface
{
	public function __construct(
		protected string $identifier
	) { }

	public function getUserIdentifier(): string
	{
		return $this->identifier;
	}

	public function getRoles(): array
	{
		return [];
	}
}
