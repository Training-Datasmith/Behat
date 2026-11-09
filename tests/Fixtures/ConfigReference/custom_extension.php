<?php

declare(strict_types=1);

use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomExtension implements Extension
{
    public function get_config_key(): string
    {
        return 'custom_extension';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('child')->info('A child node')->end()
                ->booleanNode('test')->defaultTrue()->end()
            ->end();
    }

    public function initialize(Extension_Manager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new CustomExtension();
