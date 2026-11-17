<?php

namespace Beerfranz\ExecutionContext\AppTest\Voter;

use Beerfranz\ExecutionContext\AppTest\Entity\Tenant;
use Beerfranz\ExecutionContext\ExecutionContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TenantVoter extends Voter
{
    const WRITE = 'TENANT_WRITE';
    const READ = 'TENANT_READ';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
        private ExecutionContext $executionContext,
        private LoggerInterface $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute);
    }
    
    public function supportsAttribute($attribute): bool
    { 
        return in_array($attribute, [self::WRITE, self::READ], true);
    }

    /**
     * @param string $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return match($attribute) {
            self::READ => $this->canRead($subject, $vote),
            self::WRITE => $this->canWrite($subject, $vote),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    protected function canRead(mixed $subject, ?Vote $vote) {

        $context = $this->executionContext->getContext();
        $user = $this->executionContext->getUser();

        if (in_array('ROLE_USER', $user->getRoles()))
            return true;

        $reason = 'ROLE_USER not assigned';
        $vote?->addReason($reason);
        $message = sprintf("Access denied: Read tenant, user %s in context %s. %s", $user->getUserIdentifier(), $context, $reason);
        $this->logger->warning($message);

        return false;
    }

    protected function canWrite(mixed $subject, ?Vote $vote)
    {
        $context = $this->executionContext->getContext();
        $user = $this->executionContext->getUser();

        if ($this->executionContext->isInternalUser()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles()))
            return true;

        $reason = 'ROLE_ADMIN not assigned';
        $vote?->addReason($reason);

        $message = sprintf("Access denied: Write tenant, user %s in context %s. %s", $user->getUserIdentifier(), $context, $reason);
        $this->logger->warning($message);

        return false;
    }
}
