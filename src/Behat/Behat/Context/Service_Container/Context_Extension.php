<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Service_Container;

use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Argument\Composite_Argument_Resolver_Factory;
use Behat\Behat\Context\Cli\Context_Snippets_Controller;
use Behat\Behat\Context\Context_Class\Simple_Class_Generator;
use Behat\Behat\Context\Context_Factory;
use Behat\Behat\Context\Environment\Handler\Context_Environment_Handler;
use Behat\Behat\Context\Environment\Reader\Context_Environment_Reader;
use Behat\Behat\Context\Reader\Annotated_Context_Reader;
use Behat\Behat\Context\Reader\Attribute_Context_Reader;
use Behat\Behat\Context\Reader\Context_Reader_Cached_Per_Context;
use Behat\Behat\Context\Reader\Context_Reader_Cached_Per_Suite;
use Behat\Behat\Context\Reader\Translatable_Context_Reader;
use Behat\Behat\Context\Snippet\Appender\Context_Snippet_Appender;
use Behat\Behat\Context\Snippet\Generator\Context_Snippet_Generator;
use Behat\Behat\Context\Suite\Setup\Suite_With_Contexts_Setup;
use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Snippet\Service_Container\Snippet_Extension;
use Behat\Testwork\Argument\Service_Container\Argument_Extension;
use Behat\Testwork\Autoloader\Service_Container\Autoloader_Extension;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Filesystem\Service_Container\Filesystem_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Suite\Service_Container\Suite_Extension;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat context extension.
 *
 * Extends Behat with context services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Context_Extension implements Extension
{
    /**
     * Available services.
     */
    public const FACTORY_ID = 'context.factory';
    public const CONTEXT_SNIPPET_GENERATOR_ID = 'snippet.generator.context';
    public const AGGREGATE_RESOLVER_FACTORY_ID = 'context.argument.aggregate_resolver_factory';
    private const ENVIRONMENT_HANDLER_ID = Environment_Extension::HANDLER_TAG . '.context';
    private const ENVIRONMENT_READER_ID = Environment_Extension::READER_TAG . '.context';
    private const SUITE_SETUP_ID = Suite_Extension::SETUP_TAG . '.suite_with_contexts';
    private const ANNOTATED_CONTEXT_READER_ID = self::READER_TAG . '.annotated';
    private const ATTRIBUTED_CONTEXT_READER_ID = self::READER_TAG . '.attributed';
    /*
     * Available extension points
     */
    public const CLASS_RESOLVER_TAG = 'context.class_resolver';
    public const ARGUMENT_RESOLVER_TAG = 'context.argument_resolver';
    public const INITIALIZER_TAG = 'context.initializer';
    public const READER_TAG = 'context.reader';
    public const ANNOTATION_READER_TAG = 'context.annotation_reader';
    public const ATTRIBUTE_READER_TAG = 'context.attribute_reader';
    public const CLASS_GENERATOR_TAG = 'context.class_generator';
    public const SUITE_SCOPED_RESOLVER_FACTORY_TAG = 'context.argument.suite_resolver_factory';
    public const DOC_BLOCK_HELPER_ID = 'context.docblock_helper';
    private Service_Processor $processor;

    /**
     * Initializes compiler pass.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function get_config_key(): string
    {
        return 'contexts';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_factory($container);
        $this->load_argument_resolver_factory($container);
        $this->load_environment_handler($container);
        $this->load_environment_reader($container);
        $this->load_suite_setup($container);
        $this->load_snippet_appender($container);
        $this->load_snippet_generators($container);
        $this->load_snippets_controller($container);
        $this->load_default_class_generators($container);
        $this->load_default_context_readers($container);
        $this->load_docblock_helper($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_class_resolvers($container);
        $this->process_argument_resolver_factories($container);
        $this->process_argument_resolvers($container);
        $this->process_context_initializers($container);
        $this->process_context_readers($container);
        $this->process_class_generators($container);
        $this->process_annotation_readers($container);
        $this->process_attribute_readers($container);
    }

    /**
     * Loads context factory.
     */
    private function load_factory(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Factory::class, [new Reference(Argument_Extension::CONSTRUCTOR_ARGUMENT_ORGANISER_ID)]);
        $container->setDefinition(self::FACTORY_ID, $definition);
    }

    /**
     * Loads argument resolver factory used in the environment handler.
     */
    private function load_argument_resolver_factory(ContainerBuilder $container): void
    {
        $definition = new Definition(Composite_Argument_Resolver_Factory::class);
        $container->setDefinition(self::AGGREGATE_RESOLVER_FACTORY_ID, $definition);
    }

    /**
     * Loads context environment handlers.
     */
    private function load_environment_handler(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Environment_Handler::class, [new Reference(self::FACTORY_ID), new Reference(self::AGGREGATE_RESOLVER_FACTORY_ID)]);
        $definition->addTag(Environment_Extension::HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ENVIRONMENT_HANDLER_ID, $definition);
    }

    /**
     * Loads context environment readers.
     */
    private function load_environment_reader(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Environment_Reader::class);
        $definition->addTag(Environment_Extension::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ENVIRONMENT_READER_ID, $definition);
    }

    /**
     * Loads context environment setup.
     */
    private function load_suite_setup(ContainerBuilder $container): void
    {
        $definition = new Definition(Suite_With_Contexts_Setup::class, [new Reference(Autoloader_Extension::CLASS_LOADER_ID), new Reference(Filesystem_Extension::LOGGER_ID)]);
        $definition->addTag(Suite_Extension::SETUP_TAG, ['priority' => 20]);
        $container->setDefinition(self::SUITE_SETUP_ID, $definition);
    }

    /**
     * Loads context snippet appender.
     */
    private function load_snippet_appender(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Snippet_Appender::class, [new Reference(Filesystem_Extension::LOGGER_ID)]);
        $definition->addTag(Snippet_Extension::APPENDER_TAG, ['priority' => 50]);
        $container->setDefinition(Snippet_Extension::APPENDER_TAG . '.context', $definition);
    }

    /**
     * Loads context snippet generators.
     */
    private function load_snippet_generators(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Snippet_Generator::class, [new Reference(Definition_Extension::PATTERN_TRANSFORMER_ID)]);
        $definition->addTag(Snippet_Extension::GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(self::CONTEXT_SNIPPET_GENERATOR_ID, $definition);
    }

    private function load_snippets_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Context_Snippets_Controller::class, [new Reference(self::CONTEXT_SNIPPET_GENERATOR_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 410]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.context_snippets', $definition);
    }

    /**
     * Loads default context class generators.
     */
    private function load_default_class_generators(ContainerBuilder $container): void
    {
        $definition = new Definition(Simple_Class_Generator::class);
        $definition->addTag(self::CLASS_GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(self::CLASS_GENERATOR_TAG . '.simple', $definition);
    }

    /**
     * Loads default context readers.
     */
    private function load_default_context_readers(ContainerBuilder $container): void
    {
        $this->load_annotated_context_reader($container);
        $this->load_attributed_context_reader($container);
        $this->load_translatable_context_reader($container);
    }

    /**
     * Loads Annotated_Context_Reader.
     */
    private function load_annotated_context_reader(ContainerBuilder $container): void
    {
        $definition = new Definition(Annotated_Context_Reader::class, [new Reference(self::DOC_BLOCK_HELPER_ID)]);
        $container->setDefinition(self::ANNOTATED_CONTEXT_READER_ID, $definition);
        $definition = new Definition(Context_Reader_Cached_Per_Context::class, [new Reference(self::ANNOTATED_CONTEXT_READER_ID)]);
        $definition->addTag(self::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ANNOTATED_CONTEXT_READER_ID . '.cached', $definition);
    }

    /**
     * Loads AttributedContextReader.
     */
    private function load_attributed_context_reader(ContainerBuilder $container): Definition
    {
        $definition = new Definition(Attribute_Context_Reader::class);
        $container->setDefinition(self::ATTRIBUTED_CONTEXT_READER_ID, $definition);
        $definition = new Definition(Context_Reader_Cached_Per_Context::class, [new Reference(self::ATTRIBUTED_CONTEXT_READER_ID)]);
        $definition->addTag(self::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ATTRIBUTED_CONTEXT_READER_ID . '.cached', $definition);

        return $definition;
    }

    /**
     * Loads Translatable_Context_Reader.
     */
    private function load_translatable_context_reader(ContainerBuilder $container): void
    {
        $definition = new Definition(Translatable_Context_Reader::class, [new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->setDefinition(self::READER_TAG . '.translatable', $definition);
        $definition = new Definition(Context_Reader_Cached_Per_Suite::class, [new Reference(self::READER_TAG . '.translatable')]);
        $definition->addTag(self::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::READER_TAG . '.translatable.cached', $definition);
    }

    /**
     * Loads Doc_Block_Helper.
     */
    private function load_docblock_helper(ContainerBuilder $container): void
    {
        $definition = new Definition(Doc_Block_Helper::class);
        $container->setDefinition(self::DOC_BLOCK_HELPER_ID, $definition);
    }

    /**
     * Processes all class resolvers.
     */
    private function process_class_resolvers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::CLASS_RESOLVER_TAG);
        $definition = $container->getDefinition(self::ENVIRONMENT_HANDLER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_class_resolver', [$reference]);
        }
    }

    /**
     * Processes all argument resolver factories.
     */
    private function process_argument_resolver_factories(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::SUITE_SCOPED_RESOLVER_FACTORY_TAG);
        $definition = $container->getDefinition(self::AGGREGATE_RESOLVER_FACTORY_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_factory', [$reference]);
        }
    }

    /**
     * Processes all argument resolvers.
     */
    private function process_argument_resolvers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::ARGUMENT_RESOLVER_TAG);
        $definition = $container->getDefinition(self::FACTORY_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_argument_resolver', [$reference]);
        }
    }

    /**
     * Processes all context initializers.
     */
    private function process_context_initializers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::INITIALIZER_TAG);
        $definition = $container->getDefinition(self::FACTORY_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_context_initializer', [$reference]);
        }
    }

    /**
     * Processes all context readers.
     */
    private function process_context_readers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::READER_TAG);
        $definition = $container->getDefinition(self::ENVIRONMENT_READER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_context_reader', [$reference]);
        }
    }

    /**
     * Processes all class generators.
     */
    private function process_class_generators(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::CLASS_GENERATOR_TAG);
        $definition = $container->getDefinition(self::SUITE_SETUP_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_class_generator', [$reference]);
        }
    }

    /**
     * Processes all annotation readers.
     */
    private function process_annotation_readers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::ANNOTATION_READER_TAG);
        $definition = $container->getDefinition(self::ANNOTATED_CONTEXT_READER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_annotation_reader', [$reference]);
        }
    }

    /**
     * Processes all attribute readers.
     */
    private function process_attribute_readers(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::ATTRIBUTE_READER_TAG);
        $definition = $container->getDefinition(self::ATTRIBUTED_CONTEXT_READER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_attribute_reader', [$reference]);
        }
    }
}
