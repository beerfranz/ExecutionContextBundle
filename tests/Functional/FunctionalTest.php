<?php

namespace Beerfranz\ExecutionContext\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Beerfranz\ExecutionContext\AppTest\Entity\Tenant;
use Beerfranz\ExecutionContext\AppTest\Entity\User;
use Beerfranz\ExecutionContext\AppTest\Message\AuditMessage;
use Beerfranz\ExecutionContext\AppTest\Repository\AuditRepository;
use Beerfranz\ExecutionContext\AppTest\Repository\TenantRepository;
use Beerfranz\ExecutionContext\ExecutionContext;
use Beerfranz\ExecutionContext\Tests\TestKernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class FunctionalTest extends ApiTestCase
{
    protected $em = null;

    protected $user1 = null;
    protected $user2 = null;
    protected $tenant = null;

    use InteractsWithMessenger;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);

        $schemaTool->dropDatabase();
        $schemaTool->createSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );

        $this->loadFixtures();

        parent::setUp();
    }

    public function tearDown(): void
    {
        try {
            $this->em->close();
            $this->em = null;
            parent::tearDown();

        } catch(\Exception $e) {
            echo $e->getMessage();
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function loadFixtures(): void
    {
        $this->tenant = (new Tenant())
            ->setName('tenant1')
            ->setUuid(Uuid::v7())
        ;
        $this->em->persist($this->tenant);

        $this->user1 = (new User())
            ->setUsername('user1')
            ->setUuid(Uuid::v7())
            ->setRoles(['ROLE_ADMIN'])
        ;
        $this->em->persist($this->user1);

        $this->user2 = (new User())
            ->setUsername('user2')
            ->setUuid(Uuid::v7())
            ->setRoles(['ROLE_ADMIN'])
        ;
        $this->em->persist($this->user2);

        $this->em->flush();

        $this->transport('audit')->process();
        $this->transport('auditBackup')->process();
    }

    private static function getFallbackUserIdentifier($context): string
    {
        return self::getContainer()->getParameter('beerfranz_execution_context.fallback_user_identifiers')[$context];
    }

    // Just to test if everything is OK to run tests
    public function testEnvironment(): void
    {
        $repo = self::getContainer()->get(TenantRepository::class);
        $this->assertEquals(1, count($repo->findAll()));

        $this->transport('audit')->process();

        $auditRepo = self::getContainer()->get(AuditRepository::class);
        $audits = $auditRepo->findAll();
        $this->assertEquals(1, count($audits));
        
        $this->assertEquals(self::getFallbackUserIdentifier('messenger'), $audits[0]->getActor());
    }

    public function testCommandUnknownActor(): void
    {
        // Create tenant via command console without identity
        $tenantName = 'newTenant';
        $application = new Application(self::$kernel);
        $command = $application->find('app:tenant:create');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name' => $tenantName,
        ]);

        // Check command succeed
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Tenant newTenant created', $output);

        $this->assertFlow($tenantName, self::getFallbackUserIdentifier('messenger'));
    }

    public function testCommandUser1(): void
    {
        // Create tenant via command console without identity
        $tenantName = 'newTenant';
        $application = new Application(self::$kernel);
        $command = $application->find('app:tenant:create-extended');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name' => $tenantName,
            '--user' => $this->user1->getUserIdentifier(),
        ]);

        // Check command succeed
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Tenant newTenant created', $output);

        $this->assertFlow($tenantName, $this->user1->getUserIdentifier());
    }

    // Test HTTP request + message
    public function testHttp(): void
    {

        static::createClient()->request(
            'POST',
            '/tenants',
            [
                'json' => [ 'name' => 'httpTenant' ],
                'headers' => [ 'x-token-username' => $this->user1->getUsername() ]
            ]
        );
        $this->assertResponseStatusCodeSame(200);

        $this->assertFlow('httpTenant', $this->user1->getUserIdentifier());
    }

    // The fallback user must be "unknown"
    public function testMessengerWithFakeUser(): void
    {
        $userId = Uuid::v7();
        $subject = Uuid::v7();
        $message = new AuditMessage();
        $message->setUserId($userId);
        $message->setTenantId(Uuid::v7());
        $message->auditAction = 'test';
        $message->auditSubjectKind = 'test';
        $message->auditSubjectId = $subject;

        $this->transport('audit')->send($message);
        $this->transport('audit')->queue()->assertCount(1);

        $this->transport('audit')->process();
        // Check audit
        $auditRepo = self::getContainer()->get(AuditRepository::class);
        $audit = $auditRepo->findOneBy(['subject' => $subject]);
        $this->assertEquals(self::getFallbackUserIdentifier('messenger'), $audit->getActor());

        // Check auditBackup
        $this->transport('auditBackup')->queue()->assertCount(1);
        $messages = $this->transport('auditBackup')->queue()->messages();
        $auditBackupMessage = $messages[0];
        $this->assertEquals(self::getFallbackUserIdentifier('messenger'), $auditBackupMessage->getUserId());
    }

    protected function assertFlow(
        string $tenantName,
        $expectedActor,
    ): void
    {
        // Get tenant
        $tenantRepo = self::getContainer()->get(TenantRepository::class);
        $tenant = $tenantRepo->findOneBy(['name' => $tenantName ]);
        $this->assertNotNull($tenant);

        // Assert tenant have been created by the right user
        $this->transport('audit')->queue()->assertCount(1);
        $messages = $this->transport('audit')->queue()->messages();
        $auditMessage = $messages[0];
        $this->assertEquals($expectedActor, $auditMessage->getUserId());

        // process message
        $this->transport('audit')->process();

        // Check audit
        $auditRepo = self::getContainer()->get(AuditRepository::class);
        $audit = $auditRepo->findOneBy(['subject' => $auditMessage->auditSubjectId]);
        $this->assertEquals($tenant->getUuid(), $audit->getSubject());
        $this->assertEquals($expectedActor, $audit->getActor());

        // Check auditBackup
        $this->transport('auditBackup')->queue()->assertCount(1);
        $messages = $this->transport('auditBackup')->queue()->messages();
        $auditBackupMessage = $messages[0];
        $this->assertEquals($expectedActor, $auditBackupMessage->getUserId());
    }

}