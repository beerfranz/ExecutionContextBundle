<?php

namespace Beerfranz\ExecutionContext\EventListener;

use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HttpListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private ExecutionContext $executionContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user === null)
        {
            $user = $this->executionContext->resolveUserId(null, ExecutionContext::HTTP);
        }

        $this->executionContext->reset();

        $request = $event->getRequest();
        $dryrun = $request->headers->get('X-Dry-Run', 'false') === 'true';
        if (!$dryrun)
        {
            $dryrun = $request->headers->get('X-Use-Condom', 'false') === 'true';
        }

        $this->executionContext->init(
            ExecutionContext::HTTP,
            $user,
            $dryrun,
        );
    }
}