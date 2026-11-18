<?php

declare(strict_types=1);

namespace Beerfranz\ExecutionContext;

use Beerfranz\ExecutionContext\Entity\ExecutionContextInternalUser;
use Beerfranz\ExecutionContext\Messenger\ExecutionContextMessengerTrait;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Service\ResetInterface;

class ExecutionContext implements ResetInterface
{
    use ExecutionContextMessengerTrait;

    protected string $context;
    protected ?UserInterface $user;
    protected bool $dryrun;
    protected ?string $tenantId;

    // http: classic context from an HTTP request
    public const HTTP = 'http';
    // messenger: message handler, the user can be found in the message metadata
    public const MESSENGER = 'messenger';
    // command: from a symfony command
    public const COMMAND = 'command';

    public const AVAILABLE_CONTEXTS = [ self::HTTP, self::MESSENGER, self::COMMAND ];

    public function __construct(
        protected LoggerInterface $logger,
        private readonly ManagerRegistry $registry,
        private readonly ?string $userClass = null,
        private readonly array $fallbackIdentifiers = [],
    ) {
        $this->reset();
    }

    public function init(
        string $context = self::HTTP,
        ?UserInterface $user = null,
        bool $dryrun = false,
        ?string $tenantId = null,
    ): void
    {
        if (!in_array($context, self::AVAILABLE_CONTEXTS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot use context "%s", available contexts are: %s',
                $context,
                implode(', ', self::AVAILABLE_CONTEXTS),
            ));
        }
        $this->reset();

        $this->context = $context;
        $this->user = $user;
        if ($this->user === null)
        {
            $this->user = $this->resolveUserId(null, $context);
        }
        $this->setDryRun($dryrun)
            ->setTenantId($tenantId)
        ;
        $this->logger->debug('Execution context initiated', [
            'user' => $this->user->getUserIdentifier(),
            'context' => $context,
        ]);
    }

    public function reset(): void
    {
        $this->context = self::HTTP;
        $this->user = null;
        $this->setDryRun(false)
            ->setTenantId(null)
        ;
        $this->messengerReset();
        $this->logger->debug('Execution context reset');
    }

    public function resolveUserId(?string $userId = null, ?string $context = null): UserInterface
    {

        if ($userId !== null)
        {
            // Try to resolve from user repository
            $user = $this->registry->getRepository($this->userClass)->loadUserByIdentifier($userId);
            if ($user !== null)
                return $user;

            // Resolve from internal user
            $user = $this->findInternalUser($userId);
            if ($user !== null)
                return $user;
        }

        // Otherwise, try to initiate an user entity dynamically
        $user = new ExecutionContextInternalUser($this->getFallbackIdentifier($context));

        return $user;
    }

    public function getFallbackIdentifier(?string $context): string
    {
        if (isset($this->fallbackIdentifiers[$context]))
            return $this->fallbackIdentifiers[$context];
        return $this->fallbackIdentifiers['default'];
    }

    public function findInternalUser(string $userId): ?ExecutionContextInternalUser
    {
        $internalUserKey = array_search($userId, $this->fallbackIdentifiers, true);
        if ($internalUserKey === false)
            return null;

        return new ExecutionContextInternalUser($this->getFallbackIdentifier($internalUserKey));
    }

    public function isInternalUser(?string $userId = null): bool
    {
        if ($userId === null)
            $userId = $this->user->getUserIdentifier();
        return (bool) array_search($userId, $this->fallbackIdentifiers, true);
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getUser(): UserInterface
    {
        if ($this->user !== null)
        {
            return $this->user;
        }

        return $this->resolveUserId();
    }

    public function isHttp(): bool
    {
        return $this->context === self::HTTP;
    }

    public function isInteractive(): bool
    {
        return in_array($this->context, [ self::HTTP, self::COMMAND], true);
    }

    public function isMessenger(): bool
    {
        return $this->context === self::MESSENGER;
    }

    public function isCommand(): bool
    {
        return $this->context === self::COMMAND;
    }

    public function setDryRun(bool $dryrun): self
    {
        $this->dryrun = $dryrun;
        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->dryrun;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): self
    {
        $this->tenantId = $tenantId;
        return $this;
    }

}
