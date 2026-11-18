<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Beerfranz\ExecutionContext\Messenger\Message;
use Beerfranz\ExecutionContext\Messenger\MessageInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

trait ExecutionContextMessengerTrait
{
	protected ?MessageInterface $currentMessage = null;


	public function getCurrentMessage(): ?MessageInterface
    {
        return $this->currentMessage;
    }

    public function setCurrentMessage(?MessageInterface $currentMessage): self
    {
        $this->logger->info('set current message eventId: '.$currentMessage?->getEventId());
        $this->currentMessage = $currentMessage;
        return $this;
    }

    public function newMessage(?string $messageClass = null): MessageInterface
    {
        if ($messageClass)
            $message = new $messageClass();
        else
            $message = new Message();

        if (!$message instanceof MessageInterface)
        	throw new \Exception('Only message implementing Beerfranz\ExecutionContext\Messenger\MessageInterface can be generated.');
        
        if ($this->currentMessage !== null) {
            $message->setParent($this->currentMessage);
            $this->logger->info('new message with parent '.$message::class.' id '.$message->getEventId().' with causation '.$message->getCausationId().'  correlation: ' . $message->getCorrelationId());
            return $message;
        }
        $this->logger->info('new message without parent '.$message::class.' id '.$message->getEventId().' with causation '.$message->getCausationId().'  correlation: ' . $message->getCorrelationId());
        $message->setUserId($this->user?->getUserIdentifier());
        $message->setTenantId($this->tenantId);
        return $message;
    }

    public function initFromEvent(WorkerMessageReceivedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if ($message instanceof MessageInterface)
        {
            $userId = $message->getUserId();

            $this->init(
                self::MESSENGER,
                $this->resolveUserId($userId, self::MESSENGER),
                $message->isDrunRun(),
            );
            $this->setCurrentMessage($message);
        } else {
            $this->init(
                self::MESSENGER,
                null,
                null,
            );
            $this->setCurrentMessage($message);
        }
    }

    protected function messengerReset(): void
    {
    	$this->setCurrentMessage(null);
    	$this->logger->info('reset messenger context. correlationId: '. $this->currentMessage?->getCorrelationId());
    }
}
