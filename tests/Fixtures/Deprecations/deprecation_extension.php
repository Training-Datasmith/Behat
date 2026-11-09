<?php

declare(strict_types=1);

use Behat\Testwork\Deprecation\Deprecation_Collector;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Deprecation_Extension implements Extension
{
    public function get_config_key(): string
    {
        return 'deprecation_extension';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(Extension_Manager $extensionManager): void
    {
        Deprecation_Collector::trigger('This extension triggers a deprecation during initialization');
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        @trigger_error('This extension triggers a deprecation during load', E_USER_DEPRECATED);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new Deprecation_Extension();
