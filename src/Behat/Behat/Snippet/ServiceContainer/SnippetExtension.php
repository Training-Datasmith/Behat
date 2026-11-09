<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet\Service_Container;

use Behat\Behat\Snippet\Cli\Snippets_Controller;
use Behat\Behat\Snippet\Printer\Console_Snippet_Printer;
use Behat\Behat\Snippet\Snippet_Registry;
use Behat\Behat\Snippet\Snippet_Writer;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides snippet generation, printing and appending functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Snippet_Extension implements Extension
{
    /*
     * Available services
     */
    public const REGISTRY_ID = 'snippet.registry';
    public const WRITER_ID = 'snippet.writer';
    /*
     * Available extension points
     */
    public const GENERATOR_TAG = 'snippet.generator';
    public const APPENDER_TAG = 'snippet.appender';
    private readonly Service_Processor $processor;
    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }
    public function get_config_key(): string
    {
        return 'snippets';
    }
    public function initialize(Extension_Manager $extension_manager)
    {
    }
    public function configure(Array_Node_Definition $builder)
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_controller($container);
        $this->load_registry($container);
        $this->load_writer($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_generators($container);
        $this->process_appenders($container);
    }
    protected function load_controller(Container_Builder $container)
    {
        $definition = new Definition(Console_Snippet_Printer::class, [new Reference(Cli_Extension::OUTPUT_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->set_definition('snippet.printer', $definition);
        $definition = new Definition(Snippets_Controller::class, [new Reference(self::REGISTRY_ID), new Reference(self::WRITER_ID), new Reference('snippet.printer'), new Reference(Event_Dispatcher_Extension::DISPATCHER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 400]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.snippet', $definition);
    }
    protected function load_registry(Container_Builder $container)
    {
        $definition = new Definition(Snippet_Registry::class);
        $container->set_definition(self::REGISTRY_ID, $definition);
    }
    protected function load_writer(Container_Builder $container)
    {
        $definition = new Definition(Snippet_Writer::class);
        $container->set_definition(self::WRITER_ID, $definition);
    }
    protected function process_generators(Container_Builder $container)
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::GENERATOR_TAG);
        $definition = $container->get_definition(self::REGISTRY_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerSnippetGenerator', [$reference]);
        }
    }
    protected function process_appenders(Container_Builder $container)
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::APPENDER_TAG);
        $definition = $container->get_definition(self::WRITER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerSnippetAppender', [$reference]);
        }
    }
}