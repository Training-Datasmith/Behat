<?php

declare (strict_types=1);
namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Config\Filter\Filter_Interface;
use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Narrative_Filter;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Gherkin\Gherkin_Compatibility_Mode;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use Php_Parser\Node\Expr;
final class Gherkin_Options implements Config_Converter_Interface
{
    private const CACHE_SETTING = 'cache';
    private const COMPATIBILITY_SETTING = 'compatibility';
    private const FILTERS_SETTING = 'filters';
    private const CACHE_FUNCTION = 'withCacheDir';
    private const COMPATIBILITY_FUNCTION = 'withCompatibilityMode';
    private const FILTER_FUNCTION = 'withFilter';
    public function __construct(private array $settings = [])
    {
    }
    public function to_array(): array
    {
        return $this->settings;
    }
    /**
     * Sets the parser cache directory (defaults to the system tmp dir, if writable).
     */
    public function with_cache_dir(string $dir): self
    {
        $this->settings[self::CACHE_SETTING] = $dir;
        return $this;
    }
    /**
     * Controls the extent to which gherkin is parsed equivalent to other cucumber tools.
     *
     * In legacy mode (the default), feature files are parsed as they have been in previous versions of Behat. This
     * differs slightly from the behaviour of current versions of the official cucumber parsers and runners.
     *
     * Other modes will parse identical to the official cucumber parsers.
     */
    public function with_compatibility_mode(Gherkin_Compatibility_Mode $mode): self
    {
        $this->settings[self::COMPATIBILITY_SETTING] = $mode->value;
        return $this;
    }
    public function with_filter(Filter_Interface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings[self::FILTERS_SETTING] ?? [])) {
            throw new Configuration_Loading_Exception(sprintf('The filter "%s" already exists.', $filter->name()));
        }
        $this->settings[self::FILTERS_SETTING][$filter->name()] = $filter->value();
        return $this;
    }
    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        $options_object = Config_Converter_Tools::create_object(self::class);
        $expr = $options_object;
        $this->add_cache_to_expr($expr);
        $this->add_compatibility_mode_to_expr($expr);
        $this->add_filters_to_expr($expr);
        $arguments = count($this->settings) === 0 ? [] : [$this->settings];
        Config_Converter_Tools::add_arguments_to_constructor($arguments, $options_object);
        return $expr;
    }
    private function add_cache_to_expr(Expr &$expr): void
    {
        if (isset($this->settings[self::CACHE_SETTING])) {
            $expr = Config_Converter_Tools::add_method_call(self::class, self::CACHE_FUNCTION, [$this->settings[self::CACHE_SETTING]], $expr);
            unset($this->settings[self::CACHE_SETTING]);
        }
    }
    private function add_compatibility_mode_to_expr(Expr &$expr): void
    {
        if (isset($this->settings[self::COMPATIBILITY_SETTING])) {
            $expr = Config_Converter_Tools::add_method_call(self::class, self::COMPATIBILITY_FUNCTION, [Gherkin_Compatibility_Mode::from($this->settings[self::COMPATIBILITY_SETTING])], $expr);
            unset($this->settings[self::COMPATIBILITY_SETTING]);
        }
    }
    private function add_filters_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::FILTERS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::FILTERS_SETTING] as $name => $filter_value) {
            $filter = match ($name) {
                Name_Filter::NAME => new Name_Filter($filter_value),
                Narrative_Filter::NAME => new Narrative_Filter($filter_value),
                Role_Filter::NAME => new Role_Filter($filter_value),
                Tag_Filter::NAME => new Tag_Filter($filter_value),
                default => null,
            };
            if ($filter !== null) {
                $expr = Config_Converter_Tools::add_method_call(self::class, self::FILTER_FUNCTION, [$filter->to_php_expr()], $expr);
                unset($this->settings[self::FILTERS_SETTING][$name]);
            }
        }
        if ($this->settings[self::FILTERS_SETTING] === []) {
            unset($this->settings[self::FILTERS_SETTING]);
        }
    }
}