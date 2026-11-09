<?php

declare(strict_types=1);

use Behat\Testwork\Call\Handler\Exception\Class_Not_Found_Handler;
use Behat\Testwork\Call\Handler\Exception\Method_Not_Found_Handler;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class NonExistentClassPrinter extends Class_Not_Found_Handler
{
    public function handleNonExistentClass($class): void
    {
        var_dump($class);
    }
}

class NonExistentMethodPrinter extends Method_Not_Found_Handler
{
    public function handleNonExistentMethod($callable): void
    {
        var_dump($callable);
    }
}

class CustomHandlers implements Extension
{
    public function getConfigKey(): string
    {
        return 'custom_handlers';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function initialize(Extension_Manager $extensionManager): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition('NonExistentClassPrinter', []);
        $definition->addTag(Call_Extension::EXCEPTION_HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(Call_Extension::EXCEPTION_HANDLER_TAG . '.class_printer', $definition);

        $definition = new Definition('NonExistentMethodPrinter', []);
        $definition->addTag(Call_Extension::EXCEPTION_HANDLER_TAG, ['priority' => 55]);
        $container->setDefinition(Call_Extension::EXCEPTION_HANDLER_TAG . '.method_printer', $definition);
    }
}

return new CustomHandlers();
