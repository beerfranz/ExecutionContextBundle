<?php

namespace Beerfranz\ExecutionContext\AppTest\Controller;

use Beerfranz\ExecutionContext\AppTest\Service\TenantService;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{

    #[Route('/tenants', name: 'postTenant', methods: ['POST'])]
    public function postTenant(Request $request, TenantService $service): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $tenant = $service->create($payload['name']);
        return new JsonResponse([
            'uuid' => $tenant->getUuid()->toString(),
            'name' => $tenant->getName()
        ]);
    }

    #[Route('/tenants', name: 'getTenant', methods: ['GET'])]
    public function getTenant(Request $request, TenantService $service): JsonResponse
    {
        $datas = $service->find();
        return new JsonResponse([
            'data' => $datas,
        ]);
    }

    #[Route('/whoami', name: 'whoami', methods: ['GET'])]
    public function getPublic(Request $request, ExecutionContext $executionContext): JsonResponse
    {
        return new JsonResponse([
            'userIdentifier' => $executionContext->getUser()->getUserIdentifier(),
            'context' => $executionContext->getContext(),
            'dryrun' => $executionContext->isDryRun(),
        ]);
    }
}
