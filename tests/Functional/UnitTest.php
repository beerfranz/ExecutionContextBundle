<?php

namespace Beerfranz\ExecutionContext\Tests\Functional;

use Beerfranz\ExecutionContext\Entity\ExecutionContextInternalUser;
use Beerfranz\ExecutionContext\Messenger\Message;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UnitTest extends KernelTestCase
{
	protected function setUp(): void
    {
        $kernel = self::bootKernel();
        parent::setUp();
    }

    public function testInternalUser(): void
    {
    	$internalUser = new ExecutionContextInternalUser('unit-test');
    	$this->assertEquals('unit-test', $internalUser->getUserIdentifier());
    	$this->assertEquals([], $internalUser->getRoles());

    }

    public function testBadExecutionContext(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use context "BAD_CONTEXT", available contexts are: ');

        $container = static::getContainer();
        $executionContext = $container->get(ExecutionContext::class);
        $executionContext->init('BAD_CONTEXT');
    }

    public function testExecutionContext(): void
    {
    	$container = static::getContainer();
        $executionContext = $container->get(ExecutionContext::class);

        $executionContext->init();

        $this->assertEquals(false, $executionContext->isDryRun());
        $executionContext->setDryRun(true);
        $this->assertEquals(true, $executionContext->isDryRun());

        $this->assertEquals(null, $executionContext->getTenantId());
        $executionContext->setTenantId('unit-test');
        $this->assertEquals('unit-test', $executionContext->getTenantId());

        $this->assertEquals(null, $executionContext->getCurrentMessage());

        $message = $executionContext->newMessage();
        $this->assertEquals(true, $message instanceof Message);
        
    }

    public function testMessage(): void
    {
        $message = new Message();

        $this->assertEquals(1, $message->getVersion());
        $this->assertEquals(null, $message->getProducerId());
        $this->assertEquals(1, $message->getPayloadVersion());

        $message->setProducerId('me');
        $this->assertEquals('me', $message->getProducerId());
        $message->setPayload(['data' => 'foo']);
        $this->assertEquals(['data' => 'foo'], $message->getPayload());
        $message->setEventType('tenent.created');
        $this->assertEquals('tenent.created', $message->getEventType());
        $message->setPayloadVersion(2);
        $this->assertEquals(2, $message->getPayloadVersion());


        $childMessage = new Message();
        $childMessage->setParent($message);
        $this->assertEquals($message->getEventId(), $childMessage->getCorrelationId());
        $this->assertEquals($message->getEventId(), $childMessage->getCausationId());

        $subChildMessage = new Message();
        $subChildMessage->setParent($childMessage);
        $this->assertEquals($message->getEventId(), $subChildMessage->getCorrelationId());
        $this->assertEquals($childMessage->getEventId(), $subChildMessage->getCausationId());

    }
}