<?php

declare(strict_types=1);

namespace Beerfranz\ExecutionContext;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class BeerfranzExecutionContextBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
                    ->children()
                        ->scalarNode('user_class')
                            ->defaultValue('App\Entity\User')
                            ->info('The fully qualified class name of the user entity.')
                        ->end()
                        ->arrayNode('fallback_user_identifiers')
                            ->useAttributeAsKey('context') // clés libres
                            ->defaultValue([
                                'default' => 'unknown',
                                'messenger'=> 'unknown-messenger',
                                'command' => 'unknown-command',
                                'http' => 'unknown-http',
                            ])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('beerfranz_execution_context.user_class', $config['user_class'])
            ->set('beerfranz_execution_context.fallback_user_identifiers', $config['fallback_user_identifiers'])
        ;

        $container->import('../config/services.yaml');

    }
}
