<?php

namespace Beerfranz\ExecutionContext\AppTest\Command;

use Beerfranz\ExecutionContext\AppTest\Service\TenantService;
use Beerfranz\ExecutionContext\Command\AbstractCommand;
use Beerfranz\ExecutionContext\ExecutionContext;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[AsCommand(name: 'app:tenant:create-extended')]
class CreateTenantExtendedCommand extends AbstractCommand
{

	public function __construct(
        protected TenantService $service,
        ExecutionContext $executionContext,
        UserProviderInterface $userProvider,
	)
    {
        parent::__construct($executionContext, $userProvider);
    }

	protected function configure(): void
    {
        $this
            ->setDescription('Create a new tenant in the name of.')
            ->addArgument('name', InputArgument::REQUIRED, 'name')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setExecutionContext($this->executionContext, $input);
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