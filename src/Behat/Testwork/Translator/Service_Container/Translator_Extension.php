<?php

declare(strict_types=1);
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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

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

    public function configure(ArrayNodeDefinition $builder): void
    {
        $default_language = $this->get_default_language() ?: 'en';
        $children_builder = $builder->addDefaultsIfNotSet()->children();
        $children_builder->scalarNode('locale')->info('Sets output locale for the tester')->defaultValue($default_language);
        $children_builder->scalarNode('fallback_locale')->info('Sets fallback output locale for the tester')->defaultValue('en');
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_translator($container, $config['locale'], $config['fallback_locale']);
        $this->load_controller($container);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    /**
     * Loads translator service.
     *
     * @param string           $locale
     */
    private function load_translator(ContainerBuilder $container, $locale, $fallback_locale): void
    {
        $definition = new Definition(Translator::class, [$locale]);
        $container->setDefinition(self::TRANSLATOR_ID, $definition);
        $definition->addMethodCall('setFallbackLocales', [[$fallback_locale]]);
        $definition->addMethodCall('addLoader', ['xliff', new Definition(XliffFileLoader::class)]);
        $definition->addMethodCall('addLoader', ['yaml', new Definition(YamlFileLoader::class)]);
        $definition->addMethodCall('addLoader', ['php', new Definition(PhpFileLoader::class)]);
        $definition->addMethodCall('addLoader', ['array', new Definition(ArrayLoader::class)]);
        $container->setDefinition(self::TRANSLATOR_ID, $definition);
    }

    /**
     * Loads translator controller.
     */
    private function load_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Language_Controller::class, [new Reference(self::TRANSLATOR_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 800]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.translator', $definition);
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
