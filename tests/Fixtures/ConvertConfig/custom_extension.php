<?php

declare(strict_types=1);

use Behat\Behat\Output\Printer\Console_Output_Factory;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\Output_Printer;
use Behat\Testwork\Output\Printer\Stream_Output_Printer;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Service_Container\Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CustomExtension implements Extension
{
    public function getConfigKey()
    {
        return 'custom_extension';
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder->useAttributeAsKey('name')->prototype('variable');
    }

    public function initialize(Behat\Testwork\Service_Container\Extension_Manager $extensionManager)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $outputPrinterDefinition = new Definition(Stream_Output_Printer::class, [
            new Definition(Console_Output_Factory::class),
        ]);
        $container->setDefinition(Stream_Output_Printer::class, $outputPrinterDefinition);

        $formatterDefinition = $container->register(CustomFormatter::class, CustomFormatter::class);
        $formatterDefinition->addArgument(new Reference(Stream_Output_Printer::class));
        $formatterDefinition->addTag(Output_Extension::FORMATTER_TAG);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}

class CustomFormatter implements Formatter
{
    public function __construct(
        private Output_Printer $outputPrinter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [];
    }

    public function getName()
    {
        return 'custom_formatter';
    }

    public function getDescription()
    {
    }

    public function getOutputPrinter()
    {
        return $this->outputPrinter;
    }

    public function setParameter($name, $value)
    {
    }

    public function getParameter($name)
    {
    }
}

return new CustomExtension();
