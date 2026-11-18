<?php

namespace Beerfranz\ExecutionContext\AppTest\Command;

use Beerfranz\ExecutionContext\AppTest\Service\TenantService;
use Beerfranz\ExecutionContext\Command\CommandInterface;
use Beerfranz\ExecutionContext\Command\CommandTrait;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tenant:create-trait')]
class CreateTenantTraitCommand extends Command implements ExecutionContextCommandInterface
{
    use CommandTrait;

	public function __construct(
        protected TenantService $service,
        protected readonly ExecutionContext $executionContext,
	)
    {
        parent::__construct();
    }

	protected function configure(): void
    {
        $this
            ->setDescription('Create a new tenant in the name of.')
            ->addArgument('name', InputArgument::REQUIRED, 'name')
        ;

        $this->configureUserInput()
             ->configureDryrunInput();

        parent::configure();
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->initializeExecutionContext($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
    	try {
            $name = $input->getArgument('name');
            $tenant = $this->service->create($name);

    		$output->writeln('Tenant ' . $name . ' created');
    		return Command::SUCCESS;
    	} catch (\Exception $e) {
            $output->writeln($e->getMessage());
    		return Command::FAILURE;
    	}
    }
}