<?php

namespace Beerfranz\ExecutionContext\AppTest\Authenticator;

use Beerfranz\ExecutionContext\AppTest\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class HeaderAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function supports(Request $request): ?bool
    {
        $usernameHeader = 'x-token-username';
        return $request->headers->has($usernameHeader);
    }

    public function authenticate(Request $request): Passport
    {
        $usernameHeader = 'x-token-username';
        $username = $request->headers->get($usernameHeader);
        if (null === $username) {
            throw new CustomUserMessageAuthenticationException('No identifier provided in the HTTP header ' . $usernameHeader);
        }

        $user = $this->userRepository->findOneBy([ 'username' => $username ]);

        if ($user === null)
        {
            throw new CustomUserMessageAuthenticationException('User '. $username .' not exists.');
        }

        return new SelfValidatingPassport(new UserBadge($username, function (string $userIdentifier): ?UserInterface {
                return $this->userRepository->findOneBy(['username' => $userIdentifier]);
            }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
