<?php

namespace Beerfranz\ExecutionContext\AppTest\Command;

use Beerfranz\ExecutionContext\AppTest\Service\TenantService;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tenant:create')]
class CreateTenantCommand extends Command
{

	public function __construct(
        protected TenantService $service,
        protected ExecutionContext $executionContext,
	)
    {
        parent::__construct();
    }

	protected function configure(): void
    {
        $this
            ->setDescription('Create a new tenant.')
            ->addArgument('name', InputArgument::REQUIRED, 'name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
    	try {
            $this->executionContext->init(ExecutionContext::COMMAND);
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