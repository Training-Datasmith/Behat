<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Translator\Service_Container;

use Behat\Behat\Translator\Cli\Gherkin_Translations_Controller;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
/**
 * Extends translator service with knowledge about gherkin translations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Gherkin_Translations_Extension implements Extension
{
    public function get_config_key(): string
    {
        return 'gherkin_translations';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_controller($container);
    }
    public function process(Container_Builder $container): void
    {
    }
    /**
     * Loads translator controller.
     */
    private function load_controller(Container_Builder $container): void
    {
        $definition = new Definition(Gherkin_Translations_Controller::class, [new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.gherkin_translations', $definition);
    }
}