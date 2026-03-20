<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Service_Container;

use Behat\Behat\Context\Service_Container\Context_Extension;
use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Transformation\Call\Filter\Definition_Arguments_Transformer;
use Behat\Behat\Transformation\Context\Annotation\Transformation_Annotation_Reader;
use Behat\Behat\Transformation\Context\Attribute\Transformation_Attribute_Reader;
use Behat\Behat\Transformation\Transformation_Repository;
use Behat\Behat\Transformation\Transformer\Repository_Argument_Transformer;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Provides definition arguments transformation functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation_Extension implements Extension
{
    /*
     * Available services
     */
    public const REPOSITORY_ID = 'transformation.repository';
    /*
     * Available extension points
     */
    public const ARGUMENT_TRANSFORMER_TAG = 'transformation.argument_transformer';
    protected const DEFINITION_ARGUMENT_TRANSFORMER_ID = Call_Extension::CALL_FILTER_TAG . '.definition_argument_transformer';
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
        return 'transformations';
    }
    public function initialize(Extension_Manager $extension_manager)
    {
    }
    public function configure(Array_Node_Definition $builder)
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_definition_arguments_transformer($container);
        $this->load_default_transformers($container);
        $this->load_annotation_reader($container);
        $this->load_attribute_reader($container);
        $this->load_repository($container);
    }
    public function process(Container_Builder $container): void
    {
        $this->process_arguments_transformers($container);
    }
    /**
     * Loads definition arguments transformer.
     */
    protected function load_definition_arguments_transformer(Container_Builder $container)
    {
        $definition = new Definition(Definition_Arguments_Transformer::class);
        $definition->add_tag(Call_Extension::CALL_FILTER_TAG, ['priority' => 200]);
        $container->set_definition(self::DEFINITION_ARGUMENT_TRANSFORMER_ID, $definition);
    }
    /**
     * Loads default transformers.
     */
    protected function load_default_transformers(Container_Builder $container)
    {
        $definition = new Definition(Repository_Argument_Transformer::class, [new Reference(self::REPOSITORY_ID), new Reference(Call_Extension::CALL_CENTER_ID), new Reference(Definition_Extension::PATTERN_TRANSFORMER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $definition->add_tag(self::ARGUMENT_TRANSFORMER_TAG, ['priority' => 50]);
        $container->set_definition(self::ARGUMENT_TRANSFORMER_TAG . '.repository', $definition);
    }
    /**
     * Loads transformation context annotation reader.
     */
    protected function load_annotation_reader(Container_Builder $container)
    {
        $definition = new Definition(Transformation_Annotation_Reader::class);
        $definition->add_tag(Context_Extension::ANNOTATION_READER_TAG, ['priority' => 50]);
        $container->set_definition(Context_Extension::ANNOTATION_READER_TAG . '.transformation', $definition);
    }
    /**
     * Loads transformation attribute reader.
     */
    private function load_attribute_reader(Container_Builder $container): void
    {
        $definition = new Definition(Transformation_Attribute_Reader::class, [new Reference(Definition_Extension::DOC_BLOCK_HELPER_ID)]);
        $definition->add_tag(Context_Extension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->set_definition(Context_Extension::ATTRIBUTE_READER_TAG . '.transformation', $definition);
    }
    /**
     * Loads transformations repository.
     */
    protected function load_repository(Container_Builder $container)
    {
        $definition = new Definition(Transformation_Repository::class, [new Reference(Environment_Extension::MANAGER_ID)]);
        $container->set_definition(self::REPOSITORY_ID, $definition);
    }
    /**
     * Processes all available argument transformers.
     */
    protected function process_arguments_transformers(Container_Builder $container)
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::ARGUMENT_TRANSFORMER_TAG);
        $definition = $container->get_definition(self::DEFINITION_ARGUMENT_TRANSFORMER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('registerArgumentTransformer', [$reference]);
        }
    }
    /**
     * Returns definition argument transformer service id.
     *
     *
     * @deprecated Use DEFINITION_ARGUMENT_TRANSFORMER_ID constant instead
     * @todo Remove method in next major version
     */
    protected function get_definition_argument_transformer_id(): string
    {
        return self::DEFINITION_ARGUMENT_TRANSFORMER_ID;
    }
}