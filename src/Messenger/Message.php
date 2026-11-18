<?php

namespace Beerfranz\ExecutionContext\Messenger;

use Beerfranz\ExecutionContext\Messenger\MessageInterface;
use Beerfranz\ExecutionContext\Messenger\MessageTrait;

use Symfony\Component\Uid\Uuid;

class Message implements MessageInterface
{

  use MessageTrait;

}
