<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Service_Container;

use Behat\Behat\Gherkin\Cli\Filter_Controller;
use Behat\Behat\Gherkin\Cli\Syntax_Controller;
use Behat\Behat\Gherkin\Specification\Locator\Filesystem_Feature_Locator;
use Behat\Behat\Gherkin\Specification\Locator\Filesystem_Rerun_Scenarios_List_Locator;
use Behat\Behat\Gherkin\Specification\Locator\Filesystem_Scenarios_List_Locator;
use Behat\Behat\Gherkin\Suite\Setup\Suite_With_Paths_Setup;
use Behat\Gherkin\Cache\FileCache;
use Behat\Gherkin\Cache\MemoryCache;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\NarrativeFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\GherkinCompatibilityMode;
use Behat\Gherkin\Keywords\CachedArrayKeywords;
use Behat\Gherkin\Keywords\KeywordsDumper;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Loader\GherkinFileLoader;
use Behat\Gherkin\Parser;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Filesystem\Service_Container\Filesystem_Extension;
use Behat\Testwork\Service_Container\Exception\Extension_Exception;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Specification\Service_Container\Specification_Extension;
use Behat\Testwork\Suite\Service_Container\Suite_Extension;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Behat with gherkin suites and features.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Gherkin_Extension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'gherkin';
    public const KEYWORDS_DUMPER_ID = 'gherkin.keywords_dumper';
    public const KEYWORDS_ID = 'gherkin.keywords';
    /*
     * Available extension points
     */
    public const LOADER_TAG = 'gherkin.loader';
    private Service_Processor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?Service_Processor $processor = null)
    {
        $this->processor = $processor ?: new Service_Processor();
    }

    public function get_config_key(): string
    {
        return 'gherkin';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $children_builder = $builder->addDefaultsIfNotSet()->children();
        $children_builder->scalarNode('cache')->info('Sets the gherkin parser cache folder')->defaultValue(is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat_gherkin_cache' : null);
        $children_builder->enumNode('compatibility')->info('Controls the extent to which gherkin is parsed equivalent to other cucumber tools')->values(array_map(fn (GherkinCompatibilityMode $m) => $m->value, GherkinCompatibilityMode::cases()))->defaultValue(GherkinCompatibilityMode::LEGACY->value);
        $children_builder->arrayNode('filters')->info('Sets the gherkin filters (overridable by CLI options)')->performNoDeepMerging()->defaultValue([])->useAttributeAsKey('name')->prototype('scalar')->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_parameters($container);
        $this->load_gherkin($container);
        $this->load_keywords($container);
        $this->load_parser($container, $config['compatibility']);
        $this->load_default_loaders($container, $config['cache']);
        $this->load_profile_filters($container, $config['filters']);
        $this->load_syntax_controller($container);
        $this->load_filter_controller($container);
        $this->load_suite_with_paths_setup($container);
        $this->load_filesystem_feature_locator($container);
        $this->load_filesystem_scenarios_list_locator($container);
        $this->load_filesystem_rerun_scenarios_list_locator($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_loaders($container);
    }

    /**
     * Loads default container parameters.
     */
    private function load_parameters(ContainerBuilder $container): void
    {
        $container->setParameter('suite.generic.default_settings', ['paths' => ['%paths.base%/features'], 'contexts' => ['Feature_Context']]);
    }

    /**
     * Loads gherkin service.
     */
    private function load_gherkin(ContainerBuilder $container): void
    {
        $definition = new Definition(Gherkin::class);
        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads keyword services.
     */
    private function load_keywords(ContainerBuilder $container): void
    {
        $definition = new Definition(CachedArrayKeywords::class);
        $definition->setFactory([CachedArrayKeywords::class, 'withDefaultKeywords']);
        $container->setDefinition(self::KEYWORDS_ID, $definition);
        $definition = new Definition(KeywordsDumper::class, [new Reference(self::KEYWORDS_ID)]);
        $container->setDefinition(self::KEYWORDS_DUMPER_ID, $definition);
    }

    /**
     * Loads gherkin parser.
     */
    private function load_parser(ContainerBuilder $container, string $compatibility_mode): void
    {
        $definition = new Definition(Parser::class, [new Reference('gherkin.lexer'), GherkinCompatibilityMode::from($compatibility_mode)]);
        $container->setDefinition('gherkin.parser', $definition);
        $definition = new Definition(Lexer::class, [new Reference('gherkin.keywords')]);
        $container->setDefinition('gherkin.lexer', $definition);
    }

    /**
     * Loads gherkin loaders.
     */
    private function load_default_loaders(ContainerBuilder $container, $cache_path): void
    {
        $definition = new Definition(GherkinFileLoader::class, [new Reference('gherkin.parser')]);
        if ($cache_path) {
            $cache_definition = new Definition(FileCache::class, [$cache_path]);
        } else {
            $cache_definition = new Definition(MemoryCache::class);
        }
        $definition->addMethodCall('setCache', [$cache_definition]);
        $definition->addMethodCall('setBasePath', ['%paths.base%']);
        $definition->addTag(self::LOADER_TAG, ['priority' => 50]);
        $container->setDefinition('gherkin.loader.gherkin_file', $definition);
    }

    /**
     * Loads profile-level gherkin filters.
     */
    private function load_profile_filters(ContainerBuilder $container, array $filters): void
    {
        $gherkin = $container->getDefinition(self::MANAGER_ID);
        foreach ($filters as $type => $filter_string) {
            $filter = $this->create_filter_definition($type, $filter_string);
            $gherkin->addMethodCall('addFilter', [$filter]);
        }
    }

    /**
     * Loads syntax controller.
     */
    private function load_syntax_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Syntax_Controller::class, [new Reference(self::KEYWORDS_DUMPER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 600]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.gherkin_syntax', $definition);
    }

    /**
     * Loads filter controller.
     */
    private function load_filter_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Filter_Controller::class, [new Reference(self::MANAGER_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 700]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.gherkin_filters', $definition);
    }

    /**
     * Loads suite with paths setup.
     */
    private function load_suite_with_paths_setup(ContainerBuilder $container): void
    {
        $definition = new Definition(Suite_With_Paths_Setup::class, ['%paths.base%', new Reference(Filesystem_Extension::LOGGER_ID)]);
        $definition->addTag(Suite_Extension::SETUP_TAG, ['priority' => 50]);
        $container->setDefinition(Suite_Extension::SETUP_TAG . '.suite_with_paths', $definition);
    }

    /**
     * Loads filesystem feature locator.
     */
    private function load_filesystem_feature_locator(ContainerBuilder $container): void
    {
        $definition = new Definition(Filesystem_Feature_Locator::class, [new Reference(self::MANAGER_ID), '%paths.base%']);
        $definition->addTag(Specification_Extension::LOCATOR_TAG, ['priority' => 60]);
        $container->setDefinition(Specification_Extension::LOCATOR_TAG . '.filesystem_feature', $definition);
    }

    /**
     * Loads filesystem scenarios list locator.
     */
    private function load_filesystem_scenarios_list_locator(ContainerBuilder $container): void
    {
        $definition = new Definition(Filesystem_Scenarios_List_Locator::class, [new Reference(self::MANAGER_ID)]);
        $definition->addTag(Specification_Extension::LOCATOR_TAG, ['priority' => 50]);
        $container->setDefinition(Specification_Extension::LOCATOR_TAG . '.filesystem_scenarios_list', $definition);
    }

    /**
     * Loads filesystem rerun scenarios list locator.
     */
    private function load_filesystem_rerun_scenarios_list_locator(ContainerBuilder $container): void
    {
        $definition = new Definition(Filesystem_Rerun_Scenarios_List_Locator::class, [new Reference(self::MANAGER_ID)]);
        $definition->addTag(Specification_Extension::LOCATOR_TAG, ['priority' => 50]);
        $container->setDefinition(Specification_Extension::LOCATOR_TAG . '.filesystem_rerun_scenarios_list', $definition);
    }

    /**
     * Processes all available gherkin loaders.
     */
    private function process_loaders(ContainerBuilder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::LOADER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);
        foreach ($references as $reference) {
            $definition->addMethodCall('addLoader', [$reference]);
        }
    }

    /**
     * Creates filter definition of provided type.
     *
     * @param string $type
     *
     * @throws Extension_Exception If filter type is not recognised
     */
    private function create_filter_definition(int|string $type, $filter_string): Definition
    {
        if ('role' === $type) {
            return new Definition(RoleFilter::class, [$filter_string]);
        }
        if ('name' === $type) {
            return new Definition(NameFilter::class, [$filter_string]);
        }
        if ('tags' === $type) {
            return new Definition(TagFilter::class, [$filter_string]);
        }
        if ('narrative' === $type) {
            return new Definition(NarrativeFilter::class, [$filter_string]);
        }
        throw new Extension_Exception(sprintf('`%s` filter is not supported by the `filters` option of gherkin extension. Supported types are `%s`.', $type, implode('`, `', ['narrative', 'role', 'name', 'tags'])), 'gherkin');
    }
}
