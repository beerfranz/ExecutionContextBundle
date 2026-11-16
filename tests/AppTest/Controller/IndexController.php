<?php

namespace Beerfranz\ExecutionContext\AppTest\Controller;

use Beerfranz\ExecutionContext\AppTest\Service\TenantService;
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
}
