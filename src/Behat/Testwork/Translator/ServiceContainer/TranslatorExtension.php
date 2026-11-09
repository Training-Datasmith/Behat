<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Translator\Service_Container;

use Behat\Behat\Definition\Translator\Translator;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Translator\Cli\Language_Controller;
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
use Symfony\Component\Translation\Loader\Array_Loader;
use Symfony\Component\Translation\Loader\Php_File_Loader;
use Symfony\Component\Translation\Loader\Xliff_File_Loader;
use Symfony\Component\Translation\Loader\Yaml_File_Loader;
/**
 * Provides translator service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Translator_Extension implements Extension
{
    /*
     * Available services
     */
    public const TRANSLATOR_ID = 'translator';
    public function get_config_key(): string
    {
        return 'translation';
    }
    public function initialize(Extension_Manager $extension_manager): void
    {
    }
    public function configure(Array_Node_Definition $builder): void
    {
        $default_language = $this->get_default_language() ?: 'en';
        $children_builder = $builder->add_defaults_if_not_set()->children();
        $children_builder->scalar_node('locale')->info('Sets output locale for the tester')->default_value($default_language);
        $children_builder->scalar_node('fallback_locale')->info('Sets fallback output locale for the tester')->default_value('en');
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->load_translator($container, $config['locale'], $config['fallback_locale']);
        $this->load_controller($container);
    }
    public function process(Container_Builder $container): void
    {
    }
    /**
     * Loads translator service.
     *
     * @param string           $locale
     * @param string           $fallbackLocale
     */
    private function load_translator(Container_Builder $container, $locale, $fallback_locale): void
    {
        $definition = new Definition(Translator::class, [$locale]);
        $container->set_definition(self::TRANSLATOR_ID, $definition);
        $definition->add_method_call('setFallbackLocales', [[$fallback_locale]]);
        $definition->add_method_call('addLoader', ['xliff', new Definition(Xliff_File_Loader::class)]);
        $definition->add_method_call('addLoader', ['yaml', new Definition(Yaml_File_Loader::class)]);
        $definition->add_method_call('addLoader', ['php', new Definition(Php_File_Loader::class)]);
        $definition->add_method_call('addLoader', ['array', new Definition(Array_Loader::class)]);
        $container->set_definition(self::TRANSLATOR_ID, $definition);
    }
    /**
     * Loads translator controller.
     */
    private function load_controller(Container_Builder $container): void
    {
        $definition = new Definition(Language_Controller::class, [new Reference(self::TRANSLATOR_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 800]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.translator', $definition);
    }
    /**
     * Tries to guess default user cli language.
     */
    private function get_default_language(): ?string
    {
        $default_language = null;
        if (($locale = getenv('LANG')) && preg_match('/^([a-z]{2})/', $locale, $matches)) {
            return $matches[1];
        }
        return $default_language;
    }
}