<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Service_Container;

use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Service_Container\Context_Extension;
use Behat\Behat\Definition\Cli\Available_Definitions_Controller;
use Behat\Behat\Definition\Cli\Unused_Definitions_Controller;
use Behat\Behat\Definition\Context\Annotation\Definition_Annotation_Reader;
use Behat\Behat\Definition\Context\Attribute\Definition_Attribute_Reader;
use Behat\Behat\Definition\Definition_Finder;
use Behat\Behat\Definition\Definition_Repository;
use Behat\Behat\Definition\Definition_Writer;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Pattern\Policy\Regex_Pattern_Policy;
use Behat\Behat\Definition\Pattern\Policy\Turnip_Pattern_Policy;
use Behat\Behat\Definition\Pattern\Simple_Step_Method_Name_Suggester;
use Behat\Behat\Definition\Printer\Console_Definition_Information_Printer;
use Behat\Behat\Definition\Printer\Console_Definition_List_Printer;
use Behat\Behat\Definition\Search\Repository_Search_Engine;
use Behat\Behat\Definition\Translator\Definition_Translator;
use Behat\Behat\Gherkin\Service_Container\Gherkin_Extension;
use Behat\Testwork\Argument\Service_Container\Argument_Extension;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
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
 * Extends Behat with definition services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Definition_Extension implements Extension
{
    /*
     * Available services
     */
    public const FINDER_ID = 'definition.finder';
    public const REPOSITORY_ID = 'definition.repository';
    public const PATTERN_TRANSFORMER_ID = 'definition.pattern_transformer';
    public const WRITER_ID = 'definition.writer';
    public const DEFINITION_TRANSLATOR_ID = 'definition.translator';
    public const STEP_METHOD_NAME_SUGGESTER_ID = 'definition.step_method_name_suggester_id';
    /*
     * Available extension points
     */
    public const SEARCH_ENGINE_TAG = 'definition.search_engine';
    public const PATTERN_POLICY_TAG = 'definition.pattern_policy';
    public const DOC_BLOCK_HELPER_ID = 'definition.doc_block_helper';
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
        return 'definitions';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder->addDefaultsIfNotSet()->children()->scalarNode('print_unused_definitions')->defaultFalse();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_finder($container);
        $this->load_repository($container);
        $this->load_writer($container);
        $this->load_pattern_transformer($container);
        $this->load_definition_translator($container);
        $this->load_default_search_engines($container);
        $this->load_step_method_name_suggester($container);
        $this->load_default_pattern_policies($container);
        $this->load_annotation_reader($container);
        $this->load_attribute_reader($container);
        $this->load_definition_printers($container);
        $this->load_controllers($container, $config['print_unused_definitions']);
        $this->load_docblock_helper($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_search_engines($container);
        $this->process_pattern_policies($container);
    }

    /**
     * Loads definition finder.
     */
    private function load_finder(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Finder::class);
        $container->setDefinition(self::FINDER_ID, $definition);
    }

    /**
     * Loads definition repository.
     */
    private function load_repository(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Repository::class, [new Reference(Environment_Extension::MANAGER_ID)]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads definition writer.
     */
    private function load_writer(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Writer::class, [new Reference(Environment_Extension::MANAGER_ID), new Reference(self::REPOSITORY_ID)]);
        $container->setDefinition(self::WRITER_ID, $definition);
    }

    /**
     * Loads definition pattern transformer.
     */
    private function load_pattern_transformer(ContainerBuilder $container): void
    {
        $definition = new Definition(Pattern_Transformer::class);
        $container->setDefinition(self::PATTERN_TRANSFORMER_ID, $definition);
    }

    /**
     * Loads definition translator.
     */
    private function load_definition_translator(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Translator::class, [new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $container->setDefinition(self::DEFINITION_TRANSLATOR_ID, $definition);
    }

    /**
     * Loads default search engines.
     */
    private function load_default_search_engines(ContainerBuilder $container): void
    {
        $definition = new Definition(Repository_Search_Engine::class, [new Reference(self::REPOSITORY_ID), new Reference(self::PATTERN_TRANSFORMER_ID), new Reference(self::DEFINITION_TRANSLATOR_ID), new Reference(Argument_Extension::PREG_MATCH_ARGUMENT_ORGANISER_ID)]);
        $definition->addTag(self::SEARCH_ENGINE_TAG, ['priority' => 50]);
        $container->setDefinition(self::SEARCH_ENGINE_TAG . '.repository', $definition);
    }

    private function load_step_method_name_suggester(ContainerBuilder $container): void
    {
        $definition = new Definition(Simple_Step_Method_Name_Suggester::class);
        $container->setDefinition(self::STEP_METHOD_NAME_SUGGESTER_ID, $definition);
    }

    /**
     * Loads default pattern policies.
     */
    private function load_default_pattern_policies(ContainerBuilder $container): void
    {
        $definition = new Definition(Turnip_Pattern_Policy::class, [new Reference(self::STEP_METHOD_NAME_SUGGESTER_ID)]);
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 50]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.turnip', $definition);
        $definition = new Definition(Regex_Pattern_Policy::class, [new Reference(self::STEP_METHOD_NAME_SUGGESTER_ID)]);
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 60]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.regex', $definition);
    }

    /**
     * Loads definition annotation reader.
     */
    private function load_annotation_reader(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Annotation_Reader::class);
        $definition->addTag(Context_Extension::ANNOTATION_READER_TAG, ['priority' => 50]);
        $container->setDefinition(Context_Extension::ANNOTATION_READER_TAG . '.definition', $definition);
    }

    /**
     * Loads definition Attribute reader.
     */
    private function load_attribute_reader(ContainerBuilder $container): void
    {
        $definition = new Definition(Definition_Attribute_Reader::class, [new Reference(self::DOC_BLOCK_HELPER_ID)]);
        $definition->addTag(Context_Extension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->setDefinition(Context_Extension::ATTRIBUTE_READER_TAG . '.definition', $definition);
    }

    /**
     * Loads definition printers.
     */
    private function load_definition_printers(ContainerBuilder $container): void
    {
        $definition = new Definition(Console_Definition_Information_Printer::class, [new Reference(Cli_Extension::OUTPUT_ID), new Reference(self::PATTERN_TRANSFORMER_ID), new Reference(self::DEFINITION_TRANSLATOR_ID), new Reference(Gherkin_Extension::KEYWORDS_ID)]);
        $container->setDefinition($this->get_information_printer_id(), $definition);
        $definition = new Definition(Console_Definition_List_Printer::class, [new Reference(Cli_Extension::OUTPUT_ID), new Reference(self::PATTERN_TRANSFORMER_ID), new Reference(self::DEFINITION_TRANSLATOR_ID), new Reference(Gherkin_Extension::KEYWORDS_ID)]);
        $container->setDefinition($this->get_list_printer_id(), $definition);
    }

    private function load_controllers(ContainerBuilder $container, bool $print_unused_definitions): void
    {
        $definition = new Definition(Available_Definitions_Controller::class, [new Reference(Suite_Extension::REGISTRY_ID), new Reference(self::WRITER_ID), new Reference($this->get_list_printer_id()), new Reference($this->get_information_printer_id())]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 500]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.available_definitions', $definition);
        $definition = new Definition(Unused_Definitions_Controller::class, [new Reference(self::REPOSITORY_ID), new Reference(Event_Dispatcher_Extension::DISPATCHER_ID), new Reference($this->get_information_printer_id()), $print_unused_definitions]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 300]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.unused_definitions', $definition);
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
     * Processes all search engines in the container.
     */
    private function process_search_engines(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::SEARCH_ENGINE_TAG);
        $definition = $container->getDefinition(self::FINDER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_search_engine', [$reference]);
        }
    }

    /**
     * Processes all pattern policies.
     */
    private function process_pattern_policies(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::PATTERN_POLICY_TAG);
        $definition = $container->getDefinition(self::PATTERN_TRANSFORMER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('register_pattern_policy', [$reference]);
        }
    }

    /**
     * returns list printer service id.
     */
    private function get_list_printer_id(): string
    {
        return 'definition.list_printer';
    }

    /**
     * Returns information printer service id.
     */
    private function get_information_printer_id(): string
    {
        return 'definition.information_printer';
    }
}
