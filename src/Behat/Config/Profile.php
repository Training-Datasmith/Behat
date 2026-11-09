<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Config\Filter\Filter_Interface;
use Behat\Config\Formatter\Formatter;
use Behat\Config\Formatter\Formatter_Config_Interface;
use Behat\Config\Formatter\J_Unit_Formatter;
use Behat\Config\Formatter\JSON_Formatter;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use PhpParser\Node\Expr;

final class Profile implements Config_Converter_Interface
{
    private const SUITES_SETTING = 'suites';
    private const EXTENSIONS_SETTING = 'extensions';
    private const GHERKIN_SETTING = 'gherkin';
    private const FORMATTERS_SETTING = 'formatters';
    private const DEFINITIONS_SETTING = 'definitions';
    private const PRINT_UNUSED_DEFINITIONS_SETTING = 'print_unused_definitions';
    private const PATH_OPTIONS_SETTING = 'path_options';
    private const PRINT_ABSOLUTE_PATHS_SETTING = 'print_absolute_paths';
    private const EDITOR_URL_SETTING = 'editor_url';
    private const REMOVE_PREFIX_SETTING = 'remove_prefix';
    private const DISABLE_FORMATTER_FUNCTION = 'disableFormatter';
    private const FORMATTER_FUNCTION = 'withFormatter';
    private const GHERKIN_FUNCTION = 'withGherkinOptions';
    private const UNUSED_DEFINITIONS_FUNCTION = 'withPrintUnusedDefinitions';
    private const EXTENSION_FUNCTION = 'withExtension';
    private const SUITE_FUNCTION = 'withSuite';
    private const PATH_OPTIONS_FUNCTION = 'withPathOptions';
    private const TESTER_OPTIONS_FUNCTION = 'withTesterOptions';
    private const PRINT_ABSOLUTE_PATHS_PARAMETER = 'printAbsolutePaths';
    private const EDITOR_URL_PARAMETER = 'editorUrl';
    private const REMOVE_PREFIX_PARAMETER = 'removePrefix';

    public function __construct(private readonly string $name, private array $settings = [])
    {
    }

    public function with_suite(Suite $suite): self
    {
        if (array_key_exists($suite->name(), $this->settings[self::SUITES_SETTING] ?? [])) {
            throw new Configuration_Loading_Exception(sprintf('The suite "%s" already exists.', $suite->name()));
        }
        $this->settings[self::SUITES_SETTING][$suite->name()] = $suite->to_array();

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function with_extension(Extension_Config_Interface $extension): self
    {
        if (array_key_exists($extension->name(), $this->settings[self::EXTENSIONS_SETTING] ?? [])) {
            throw new Configuration_Loading_Exception(sprintf('The extension "%s" already exists.', $extension->name()));
        }
        $this->settings[self::EXTENSIONS_SETTING][$extension->name()] = $extension->to_array();

        return $this;
    }

    /**
     * @deprecated use withGherkinOptions()->withFilter(). This method will be removed in 4.0
     */
    public function with_filter(Filter_Interface $filter): self
    {
        return $this->with_gherkin_options((new Gherkin_Options($this->settings[self::GHERKIN_SETTING] ?? []))->with_filter($filter));
    }

    public function with_formatter(Formatter_Config_Interface $formatter): self
    {
        $this->settings[self::FORMATTERS_SETTING][$formatter->name()] = $formatter->to_array();

        return $this;
    }

    public function disable_formatter(string $name): self
    {
        $this->settings[self::FORMATTERS_SETTING][$name] = false;

        return $this;
    }

    public function with_gherkin_options(Gherkin_Options $gherkin): self
    {
        $this->settings[self::GHERKIN_SETTING] = $gherkin->to_array();

        return $this;
    }

    public function with_print_unused_definitions(bool $print_unused_definitions = true): self
    {
        $this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING] = $print_unused_definitions;

        return $this;
    }

    public function with_path_options(bool $print_absolute_paths = false, ?string $editor_url = null, array $remove_prefix = []): self
    {
        $this->settings[self::PATH_OPTIONS_SETTING][self::PRINT_ABSOLUTE_PATHS_SETTING] = $print_absolute_paths;
        if ($editor_url !== null) {
            $this->settings[self::PATH_OPTIONS_SETTING][self::EDITOR_URL_SETTING] = $editor_url;
        }
        if ($remove_prefix !== []) {
            $this->settings[self::PATH_OPTIONS_SETTING][self::REMOVE_PREFIX_SETTING] = $remove_prefix;
        }

        return $this;
    }

    public function with_tester_options(Tester_Options $options): self
    {
        // Tester options are split between multiple settings keys due to implementation details in Behat
        foreach ($options->to_array() as $group => $group_settings) {
            $this->settings[$group] = $group_settings;
        }

        return $this;
    }

    public function withSuite(Suite $suite): self
    {
        return $this->with_suite($suite);
    }

    public function withExtension(Extension_Config_Interface $extension): self
    {
        return $this->with_extension($extension);
    }

    public function withFilter(Filter_Interface $filter): self
    {
        return $this->with_filter($filter);
    }

    public function withFormatter(Formatter_Config_Interface $formatter): self
    {
        return $this->with_formatter($formatter);
    }

    public function disableFormatter(string $name): self
    {
        return $this->disable_formatter($name);
    }

    public function withGherkinOptions(Gherkin_Options $gherkin): self
    {
        return $this->with_gherkin_options($gherkin);
    }

    public function withPrintUnusedDefinitions(bool $printUnusedDefinitions = true): self
    {
        return $this->with_print_unused_definitions($printUnusedDefinitions);
    }

    public function withPathOptions(bool $printAbsolutePaths = false, ?string $editorUrl = null, array $removePrefix = []): self
    {
        return $this->with_path_options($printAbsolutePaths, $editorUrl, $removePrefix);
    }

    public function withTesterOptions(Tester_Options $options): self
    {
        return $this->with_tester_options($options);
    }

    public function to_array(): array
    {
        return $this->settings;
    }

    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        $profile_object = Config_Converter_Tools::create_object(self::class);
        $expr = $profile_object;
        $this->add_formatters_to_expr($expr);
        $this->add_gherkin_options_to_expr($expr);
        $this->add_unused_definitions_to_expr($expr);
        $this->add_path_options_to_expr($expr);
        $this->add_tester_options_to_expr($expr);
        $this->add_extensions_to_expr($expr);
        $this->add_suites_to_expr($expr);
        $arguments = count($this->settings) === 0 ? [$this->name] : [$this->name, $this->settings];
        Config_Converter_Tools::add_arguments_to_constructor($arguments, $profile_object);

        return $expr;
    }

    private function add_formatters_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::FORMATTERS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::FORMATTERS_SETTING] as $name => $formatter_settings) {
            if ($formatter_settings === false) {
                $expr = Config_Converter_Tools::add_method_call(self::class, self::DISABLE_FORMATTER_FUNCTION, [$name], $expr);
            } else {
                if ($formatter_settings === null) {
                    $formatter_settings = true;
                }
                if (isset($formatter_settings[Show_Output_Option::OPTION_NAME])) {
                    $formatter_settings[Show_Output_Option::PARAMETER_NAME] = Show_Output_Option::from($formatter_settings[Show_Output_Option::OPTION_NAME]);
                    unset($formatter_settings[Show_Output_Option::OPTION_NAME]);
                }
                $formatter = match ($name) {
                    Pretty_Formatter::NAME => $formatter_settings === true ? new Pretty_Formatter() : new Pretty_Formatter(...$formatter_settings),
                    Progress_Formatter::NAME => $formatter_settings === true ? new Progress_Formatter() : new Progress_Formatter(...$formatter_settings),
                    J_Unit_Formatter::NAME => $formatter_settings === true ? new J_Unit_Formatter() : new J_Unit_Formatter(...$formatter_settings),
                    JSON_Formatter::NAME => $formatter_settings === true ? new JSON_Formatter() : new JSON_Formatter(...$formatter_settings),
                    default => $formatter_settings === true ? new Formatter($name) : new Formatter($name, $formatter_settings),
                };
                $expr = Config_Converter_Tools::add_method_call(self::class, self::FORMATTER_FUNCTION, [$formatter->to_php_expr()], $expr);
            }
        }
        unset($this->settings[self::FORMATTERS_SETTING]);
    }

    private function add_gherkin_options_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::GHERKIN_SETTING])) {
            return;
        }
        $options_object = new Gherkin_Options($this->settings[self::GHERKIN_SETTING]);
        $expr = Config_Converter_Tools::add_method_call(self::class, self::GHERKIN_FUNCTION, [$options_object->to_php_expr()], $expr);
        unset($this->settings[self::GHERKIN_SETTING]);
    }

    private function add_unused_definitions_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING])) {
            return;
        }
        $expr = Config_Converter_Tools::add_method_call(self::class, self::UNUSED_DEFINITIONS_FUNCTION, [$this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]], $expr);
        unset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]);
        if ($this->settings[self::DEFINITIONS_SETTING] === []) {
            unset($this->settings[self::DEFINITIONS_SETTING]);
        }
    }

    private function add_path_options_to_expr(Expr &$expr): void
    {
        $args = [self::PRINT_ABSOLUTE_PATHS_PARAMETER => false, self::EDITOR_URL_PARAMETER => null, self::REMOVE_PREFIX_PARAMETER => []];
        $settings_per_parameter = [self::PRINT_ABSOLUTE_PATHS_PARAMETER => self::PRINT_ABSOLUTE_PATHS_SETTING, self::EDITOR_URL_PARAMETER => self::EDITOR_URL_SETTING, self::REMOVE_PREFIX_PARAMETER => self::REMOVE_PREFIX_SETTING];
        $setting_found = false;
        foreach ($settings_per_parameter as $parameter => $setting) {
            if (isset($this->settings[self::PATH_OPTIONS_SETTING][$setting])) {
                $args[$parameter] = $this->settings[self::PATH_OPTIONS_SETTING][$setting];
                unset($this->settings[self::PATH_OPTIONS_SETTING][$setting]);
                $setting_found = true;
            }
        }
        if ($setting_found) {
            $expr = Config_Converter_Tools::add_method_call(self::class, self::PATH_OPTIONS_FUNCTION, $args, $expr);
            if ($this->settings[self::PATH_OPTIONS_SETTING] === []) {
                unset($this->settings[self::PATH_OPTIONS_SETTING]);
            }
        }
    }

    private function add_tester_options_to_expr(Expr &$expr): void
    {
        $options_object = Tester_Options::consume_settings_from_profile($this->settings);
        if (!$options_object instanceof Tester_Options) {
            return;
        }
        $expr = Config_Converter_Tools::add_method_call(self::class, self::TESTER_OPTIONS_FUNCTION, [$options_object->to_php_expr()], $expr);
    }

    private function add_extensions_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::EXTENSIONS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::EXTENSIONS_SETTING] as $name => $extension_settings) {
            $extension_object = new Extension($name, $extension_settings ?? []);
            $expr = Config_Converter_Tools::add_method_call(self::class, self::EXTENSION_FUNCTION, [$extension_object->to_php_expr()], $expr);
        }
        unset($this->settings[self::EXTENSIONS_SETTING]);
    }

    private function add_suites_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::SUITES_SETTING])) {
            return;
        }
        foreach ($this->settings[self::SUITES_SETTING] as $name => $suite_settings) {
            $suite_object = new Suite($name, $suite_settings ?? []);
            $expr = Config_Converter_Tools::add_method_call(self::class, self::SUITE_FUNCTION, [$suite_object->to_php_expr()], $expr);
        }
        unset($this->settings[self::SUITES_SETTING]);
    }
}
