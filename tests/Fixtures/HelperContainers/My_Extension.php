<?php

declare(strict_types=1);

use Behat\Behat\HelperContainer\Service_Container\Helper_Container_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class My_Extension implements Extension
{
    public function getConfigKey(): string
    {
        return 'container_provider';
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
        $definition = new Definition('My_Container', []);
        $definition->addTag(Helper_Container_Extension::HELPER_CONTAINER_TAG);
        $definition->setPublic(true);

        if (method_exists($definition, 'setShared')) {
            $definition->setShared(false); // <- Starting Symfony 2.8
        } else {
            $definition->setScope(ContainerBuilder::SCOPE_PROTOTYPE); // <- Up to Symfony 2.8
        }

        $container->setDefinition('my_extension.container', $definition);
    }
}

return new My_Extension();
