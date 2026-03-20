<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Service_Container;

use Behat\Behat\Context\Service_Container\Context_Extension;
use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Hook\Context\Annotation\Hook_Annotation_Reader;
use Behat\Behat\Hook\Context\Attribute\Hook_Attribute_Reader;
use Behat\Behat\Hook\Tester\Hookable_Feature_Tester;
use Behat\Behat\Hook\Tester\Hookable_Scenario_Tester;
use Behat\Behat\Hook\Tester\Hookable_Step_Tester;
use Behat\Behat\Tester\Service_Container\Tester_Extension;
use Behat\Testwork\Hook\Service_Container\Hook_Extension as BaseExtension;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Extends Testwork HookExtension with additional behat services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Hook_Extension extends Base_Extension
{
    public function load(Container_Builder $container, array $config): void
    {
        parent::load($container, $config);
        $this->load_annotation_reader($container);
        $this->load_attribute_reader($container);
    }
    /**
     * Loads hookable testers.
     */
    protected function load_hookable_testers(Container_Builder $container): void
    {
        parent::load_hookable_testers($container);
        $definition = new Definition(Hookable_Feature_Tester::class, [new Reference(Tester_Extension::SPECIFICATION_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::SPECIFICATION_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->set_definition(Tester_Extension::SPECIFICATION_TESTER_WRAPPER_TAG . '.hookable', $definition);
        $definition = new Definition(Hookable_Scenario_Tester::class, [new Reference(Tester_Extension::SCENARIO_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::SCENARIO_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->set_definition(Tester_Extension::SCENARIO_TESTER_WRAPPER_TAG . '.hookable', $definition);
        $definition = new Definition(Hookable_Scenario_Tester::class, [new Reference(Tester_Extension::EXAMPLE_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::EXAMPLE_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->set_definition(Tester_Extension::EXAMPLE_TESTER_WRAPPER_TAG . '.hookable', $definition);
        $definition = new Definition(Hookable_Step_Tester::class, [new Reference(Tester_Extension::STEP_TESTER_ID), new Reference(self::DISPATCHER_ID)]);
        $definition->add_tag(Tester_Extension::STEP_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->set_definition(Tester_Extension::STEP_TESTER_WRAPPER_TAG . '.hookable', $definition);
    }
    /**
     * Loads hook annotation reader.
     */
    private function load_annotation_reader(Container_Builder $container): void
    {
        $definition = new Definition(Hook_Annotation_Reader::class);
        $definition->add_tag(Context_Extension::ANNOTATION_READER_TAG, ['priority' => 50]);
        $container->set_definition(Context_Extension::ANNOTATION_READER_TAG . '.hook', $definition);
    }
    /**
     * Loads hook attribute reader.
     */
    private function load_attribute_reader(Container_Builder $container): void
    {
        $definition = new Definition(Hook_Attribute_Reader::class, [new Reference(Definition_Extension::DOC_BLOCK_HELPER_ID)]);
        $definition->add_tag(Context_Extension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->set_definition(Context_Extension::ATTRIBUTE_READER_TAG . '.hook', $definition);
    }
}