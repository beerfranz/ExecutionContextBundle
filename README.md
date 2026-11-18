# ExecutionContext Bundle


The `ExecutionContext` service hold this info, whatever the context:
- user
- current execution context: (`http`, `messenger` or `command`)
- Dryrun mode (implementing dryrun is on your hands)

## What this bundle do

Use this bundle if you want to:
- have identification to your commands or messages.
- use Symfony voters during message or console command.


## What this bundle do NOT

This bundle do NOT:
- add authentication, aka verification of the identity (for HTTP context, this bundle use the Symfony firewall).
- add a dryrun mode to your application: it only provide a way to know if the request expect a dryrun.

## Usage

You can see code examples in [test folder](./tests/AppTest/README.md).

Inject the `Beerfranz\ExecutionContext\ExecutionContext` service to your class to have access to its informations:

```php
use Beerfranz\ExecutionContext\ExecutionContext;

final class MyService
{
    public function __construct(private readonly ExecutionContext $executionContext)
    {
    }

    public function doSomething(): void
    {
        if ($this->executionContext->isInteractive()) {
            // ...
        }
    }
}
```

Available contexts are exposed as constants on the class:

- `ExecutionContext::HTTP`
- `ExecutionContext::MESSENGER`
- `ExecutionContext::COMMAND`

If the user is not defined, the user is a `ExecutionContextInternalUser`, implementing `UserInterface`, with `getRoles() = []` and `getUserIdentifier() = <config.yml: beerfranz_execution_context.fallback_user_identifiers.[context]>`.

Available methods:
- `init(string $context = self::HTTP, ?UserInterface $user = null, bool $dryrun = false, ?string $tenantId = null)`
- `reset()`: clean current context.
- `getContext(): string`
- `getUser(): UserInterface`
- `isInternalUser(?string $userId = null): bool`: if user is faked by this bundle
- `isHttp(): bool`: true if context is HTTP
- `isMessenger(): bool`: true if context is messenger
- `isCommand(): bool`: true if context is command
- `isInteractive(): bool`: true if context is HTTP or command
- `setDryRun(bool $dryrun): self`
- `isDryRun(): bool`
- `getTenantId(): ?string`
- `setTenantId(?string $tenantId): self`


**Tenant**
The bundle provide an interface `Beerfranz\ExecutionContext\Entity\ExecutionContextTenantInterface`
Implement it on your entities to have a common way to get the tenant ID:

```php
$this->executionContext->setTenantId($entity->getTenantIdentifier());
```


### HTTP context

Set dryrun using HTTP header `X-Dry-Run: "true"`.

### Command context

Extend your commands by `Beerfranz\ExecutionContext\Command\AbstractCommand`.

```php
use Beerfranz\ExecutionContext\Command\AbstractCommand;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand extends AbstractCommand
{
    public function __construct(
        ExecutionContext $executionContext,
    )
    {
        parent::__construct($executionContext);
    }

    protected function configure(): void
    {
        parent::configure();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Your code
    }
}
```

The `configure()` add to your command:
- `--user <userId>`: define the user.
- `--dryrun`: is dryrun enabled.

The `ExecutionContext` class have specific methods:
- `initFromEventCommand(ConsoleCommandEvent $event): void`: used by command event listener.
- `initFromCommand(?string $userId = null, bool $dryrun = false): void`: used by `AbstractCommand:initialize()`.

### Messenger context

For this context, messages should implement `Beerfranz\ExecutionContext\Messenger\MessageInterface` to add this informations:
- `getEventId(): ?string`: uniq ID of the message.
- `getCorrelationId(): ?string`: ID of the root message parent in case of chaining messages.
- `getCausationId(): ?string`: ID of the parent message in case of chaining messages.
- `getProducerId(): ?string`: ID of the producer of the message.
- `getUserId(): ?string`: return the `UserInterface->getUserIdentifier()`.
- `getTenantId(): ?string`: tenant identifier.
- `isDrunRun(): bool`: true if dryrun is expected.

Use this helper to generate a message, based on the current context:
```
$message = $this->executionContext->newMessage(MyMessage::class [, eventId]);
```
Don't set `eventId`, let the code generate one for you. It's only for easier testing.

The `ExecutionContext` class have specific methods:
- `getCurrentMessage(): ?MessageInterface`: the message the produce the current code iteration.
- `setCurrentMessage(?MessageInterface $currentMessage): self`: Setter of the current message.
- `newMessage(?string $messageClass = null, ?string $eventId = null): MessageInterface`: helper to generate a new message that will have references of the current message. Never set parameter `$eventId`, except for testing usage.
- `initFromEvent(WorkerMessageReceivedEvent $event): void`: Used by the message event subscriber.


## Installation

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Beerfranz\ExecutionContext\BeerfranzExecutionContextBundle::class => ['all' => true],
];
```

Register the listeners on event kernel in `config/services.yaml`:

```yaml
services:
    Beerfranz\ExecutionContext\EventListener\HttpListener:
        tags:
            - kernel.event_subscriber

    Beerfranz\ExecutionContext\EventListener\MessengerListener:
        tags:
            - kernel.event_subscriber

    Beerfranz\ExecutionContext\EventListener\CommandListener:
        tags:
            - kernel.event_subscriber

```

Your User entity must implement `Symfony\Component\Security\Core\User\UserInterface`
Your User repository must implement `Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface`

Warning:
```
security:
    providers:
        app_user_provider:
            entity:
                class: Beerfranz\ExecutionContext\AppTest\Entity\User
                property: username  => disable loadUserByIdentifier from repo
```

Finally, configure the service:
```yaml
beerfranz_execution_context:
    user_class: 'App\Entity\User'
    fallback_user_identifiers:
        default: default
        http: default-http
        command: default-command
        messenger: default-messenger
        # Or if you prefer UUID format, this is an example with UUIDv8
        # default: 00000000-0000-8000-a000-000000000000
        # http: 00000000-0000-8000-a000-000000000001
        # command: 00000000-0000-8000-a000-000000000002
        # messenger: 00000000-0000-8000-a000-000000000003
```

## Known issue

- Console event listener is not triggered: need to implement set/reset ExecutionContext at command level.

## TODO

- check comportment when configuration looks like:
    ```yaml
    security:
        providers:
            app_user_provider:
                entity:
                    class: Beerfranz\ExecutionContext\AppTest\Entity\User
                    property: uuid # Uuid is not a string
    ```
- forbid usage of `ExecutionContextInternalUser` (force user to be applicative user). :warning: should be bypassable for some commands like app initialization, or migration.

## Tests

```
docker compose run --rm php composer update
docker compose up
docker compose run --rm php vendor/bin/phpunit --filter testCommandUser1
```
