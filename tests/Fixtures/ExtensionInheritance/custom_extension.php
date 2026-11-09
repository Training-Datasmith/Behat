<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\Context_Initializer;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomInitializer implements Context_Initializer
{
    public function supportsContext(Context $context): bool
    {
        return true;
    }

    public function initialize_context(Context $context): void
    {
        $context->addExtension('custom extension 1');
    }
}

class CustomExtension implements Extension
{
    public function get_config_key(): string
    {
        return 'custom_extension';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(Extension_Manager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = $container->register('custom_initializer', 'CustomInitializer');
        $definition->addTag('context.initializer', ['priority' => 100]);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

return new CustomExtension();
