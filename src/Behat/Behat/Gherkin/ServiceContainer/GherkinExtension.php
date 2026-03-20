<?php

declare (strict_types=1);
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
use Behat\Gherkin\Cache\File_Cache;
use Behat\Gherkin\Cache\Memory_Cache;
use Behat\Gherkin\Filter\Name_Filter;
use Behat\Gherkin\Filter\Narrative_Filter;
use Behat\Gherkin\Filter\Role_Filter;
use Behat\Gherkin\Filter\Tag_Filter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Gherkin_Compatibility_Mode;
use Behat\Gherkin\Keywords\Cached_Array_Keywords;
use Behat\Gherkin\Keywords\Keywords_Dumper;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Loader\Gherkin_File_Loader;
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
use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
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
    public function configure(Array_Node_Definition $builder): void
    {
        $children_builder = $builder->add_defaults_if_not_set()->children();
        $children_builder->scalar_node('cache')->info('Sets the gherkin parser cache folder')->default_value(is_writable(sys_get_temp_dir()) ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat_gherkin_cache' : null);
        $children_builder->enum_node('compatibility')->info('Controls the extent to which gherkin is parsed equivalent to other cucumber tools')->values(array_map(fn(Gherkin_Compatibility_Mode $m) => $m->value, Gherkin_Compatibility_Mode::cases()))->default_value(Gherkin_Compatibility_Mode::LEGACY->value);
        $children_builder->array_node('filters')->info('Sets the gherkin filters (overridable by CLI options)')->perform_no_deep_merging()->default_value([])->use_attribute_as_key('name')->prototype('scalar')->end();
    }
    public function load(Container_Builder $container, array $config): void
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
    public function process(Container_Builder $container): void
    {
        $this->process_loaders($container);
    }
    /**
     * Loads default container parameters.
     */
    private function load_parameters(Container_Builder $container): void
    {
        $container->set_parameter('suite.generic.default_settings', ['paths' => ['%paths.base%/features'], 'contexts' => ['FeatureContext']]);
    }
    /**
     * Loads gherkin service.
     */
    private function load_gherkin(Container_Builder $container): void
    {
        $definition = new Definition(Gherkin::class);
        $container->set_definition(self::MANAGER_ID, $definition);
    }
    /**
     * Loads keyword services.
     */
    private function load_keywords(Container_Builder $container): void
    {
        $definition = new Definition(Cached_Array_Keywords::class);
        $definition->set_factory([Cached_Array_Keywords::class, 'withDefaultKeywords']);
        $container->set_definition(self::KEYWORDS_ID, $definition);
        $definition = new Definition(Keywords_Dumper::class, [new Reference(self::KEYWORDS_ID)]);
        $container->set_definition(self::KEYWORDS_DUMPER_ID, $definition);
    }
    /**
     * Loads gherkin parser.
     */
    private function load_parser(Container_Builder $container, string $compatibility_mode): void
    {
        $definition = new Definition(Parser::class, [new Reference('gherkin.lexer'), Gherkin_Compatibility_Mode::from($compatibility_mode)]);
        $container->set_definition('gherkin.parser', $definition);
        $definition = new Definition(Lexer::class, [new Reference('gherkin.keywords')]);
        $container->set_definition('gherkin.lexer', $definition);
    }
    /**
     * Loads gherkin loaders.
     *
     * @param string           $cachePath
     */
    private function load_default_loaders(Container_Builder $container, $cache_path): void
    {
        $definition = new Definition(Gherkin_File_Loader::class, [new Reference('gherkin.parser')]);
        if ($cache_path) {
            $cache_definition = new Definition(File_Cache::class, [$cache_path]);
        } else {
            $cache_definition = new Definition(Memory_Cache::class);
        }
        $definition->add_method_call('setCache', [$cache_definition]);
        $definition->add_method_call('setBasePath', ['%paths.base%']);
        $definition->add_tag(self::LOADER_TAG, ['priority' => 50]);
        $container->set_definition('gherkin.loader.gherkin_file', $definition);
    }
    /**
     * Loads profile-level gherkin filters.
     */
    private function load_profile_filters(Container_Builder $container, array $filters): void
    {
        $gherkin = $container->get_definition(self::MANAGER_ID);
        foreach ($filters as $type => $filter_string) {
            $filter = $this->create_filter_definition($type, $filter_string);
            $gherkin->add_method_call('addFilter', [$filter]);
        }
    }
    /**
     * Loads syntax controller.
     */
    private function load_syntax_controller(Container_Builder $container): void
    {
        $definition = new Definition(Syntax_Controller::class, [new Reference(self::KEYWORDS_DUMPER_ID), new Reference(Translator_Extension::TRANSLATOR_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 600]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.gherkin_syntax', $definition);
    }
    /**
     * Loads filter controller.
     */
    private function load_filter_controller(Container_Builder $container): void
    {
        $definition = new Definition(Filter_Controller::class, [new Reference(self::MANAGER_ID)]);
        $definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 700]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.gherkin_filters', $definition);
    }
    /**
     * Loads suite with paths setup.
     */
    private function load_suite_with_paths_setup(Container_Builder $container): void
    {
        $definition = new Definition(Suite_With_Paths_Setup::class, ['%paths.base%', new Reference(Filesystem_Extension::LOGGER_ID)]);
        $definition->add_tag(Suite_Extension::SETUP_TAG, ['priority' => 50]);
        $container->set_definition(Suite_Extension::SETUP_TAG . '.suite_with_paths', $definition);
    }
    /**
     * Loads filesystem feature locator.
     */
    private function load_filesystem_feature_locator(Container_Builder $container): void
    {
        $definition = new Definition(Filesystem_Feature_Locator::class, [new Reference(self::MANAGER_ID), '%paths.base%']);
        $definition->add_tag(Specification_Extension::LOCATOR_TAG, ['priority' => 60]);
        $container->set_definition(Specification_Extension::LOCATOR_TAG . '.filesystem_feature', $definition);
    }
    /**
     * Loads filesystem scenarios list locator.
     */
    private function load_filesystem_scenarios_list_locator(Container_Builder $container): void
    {
        $definition = new Definition(Filesystem_Scenarios_List_Locator::class, [new Reference(self::MANAGER_ID)]);
        $definition->add_tag(Specification_Extension::LOCATOR_TAG, ['priority' => 50]);
        $container->set_definition(Specification_Extension::LOCATOR_TAG . '.filesystem_scenarios_list', $definition);
    }
    /**
     * Loads filesystem rerun scenarios list locator.
     */
    private function load_filesystem_rerun_scenarios_list_locator(Container_Builder $container): void
    {
        $definition = new Definition(Filesystem_Rerun_Scenarios_List_Locator::class, [new Reference(self::MANAGER_ID)]);
        $definition->add_tag(Specification_Extension::LOCATOR_TAG, ['priority' => 50]);
        $container->set_definition(Specification_Extension::LOCATOR_TAG . '.filesystem_rerun_scenarios_list', $definition);
    }
    /**
     * Processes all available gherkin loaders.
     */
    private function process_loaders(Container_Builder $container): void
    {
        $references = $this->processor->find_and_sort_tagged_services($container, self::LOADER_TAG);
        $definition = $container->get_definition(self::MANAGER_ID);
        foreach ($references as $reference) {
            $definition->add_method_call('addLoader', [$reference]);
        }
    }
    /**
     * Creates filter definition of provided type.
     *
     * @param string $type
     * @param string $filterString
     *
     * @throws ExtensionException If filter type is not recognised
     */
    private function create_filter_definition(int|string $type, $filter_string): Definition
    {
        if ('role' === $type) {
            return new Definition(Role_Filter::class, [$filter_string]);
        }
        if ('name' === $type) {
            return new Definition(Name_Filter::class, [$filter_string]);
        }
        if ('tags' === $type) {
            return new Definition(Tag_Filter::class, [$filter_string]);
        }
        if ('narrative' === $type) {
            return new Definition(Narrative_Filter::class, [$filter_string]);
        }
        throw new Extension_Exception(sprintf('`%s` filter is not supported by the `filters` option of gherkin extension. Supported types are `%s`.', $type, implode('`, `', ['narrative', 'role', 'name', 'tags'])), 'gherkin');
    }
}