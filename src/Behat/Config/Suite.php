<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Config\Filter\Filter_Interface;
use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Narrative_Filter;
use Behat\Config\Filter\Role_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use PhpParser\Node\Expr;

final class Suite implements Config_Converter_Interface
{
    private const CONTEXTS_SETTING = 'contexts';
    private const PATHS_SETTING = 'paths';
    private const FILTERS_SETTING = 'filters';
    private const WITH_CONTEXTS_FUNCTION = 'with_contexts';
    private const ADD_CONTEXT_FUNCTION = 'add_context';
    private const PATHS_FUNCTION = 'with_paths';
    private const FILTER_FUNCTION = 'with_filter';

    public function __construct(private readonly string $name, private array $settings = [])
    {
    }

    public function with_contexts(string ...$contexts): self
    {
        foreach ($contexts as $context) {
            $this->settings[self::CONTEXTS_SETTING][] = $context;
        }

        return $this;
    }

    public function add_context(string $context, array $constructor_args = []): self
    {
        $this->settings[self::CONTEXTS_SETTING][][$context] = $constructor_args;

        return $this;
    }

    public function with_paths(string ...$paths): self
    {
        foreach ($paths as $path) {
            $this->settings[self::PATHS_SETTING][] = $path;
        }

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

    public function name(): string
    {
        return $this->name;
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
        $suite_object = Config_Converter_Tools::create_object(self::class);
        $expr = $suite_object;
        $this->add_contexts_to_expr($expr);
        $this->add_paths_to_expr($expr);
        $this->add_filters_to_expr($expr);
        $arguments = $this->settings === [] ? [$this->name] : [$this->name, $this->settings];
        Config_Converter_Tools::add_arguments_to_constructor($arguments, $suite_object);

        return $expr;
    }

    private function add_contexts_to_expr(Expr &$expr): void
    {
        if (!isset($this->settings[self::CONTEXTS_SETTING])) {
            return;
        }
        $contexts = $this->settings[self::CONTEXTS_SETTING];
        unset($this->settings[self::CONTEXTS_SETTING]);
        // Contexts can be configured with or without constructor arguments.
        $has_any_with_arguments = (bool) array_filter($contexts, is_array(...));
        if (!$has_any_with_arguments) {
            // All the contexts are just class names, we can add them as a single `->with_contexts` call
            $expr = Config_Converter_Tools::add_method_call(self::class, self::WITH_CONTEXTS_FUNCTION, $contexts, $expr);

            return;
        }
        // One or more contexts has constructor arguments. We need to preserve the existing order, in case any are
        // registering hooks. Therefore, we need to add each one individually.
        foreach ($contexts as $context_config) {
            if (is_array($context_config)) {
                // The structure is [$context => $constructorArgs]
                $args = [array_key_first($context_config), array_shift($context_config)];
            } else {
                $args = [$context_config, []];
            }
            $expr = Config_Converter_Tools::add_method_call(self::class, self::ADD_CONTEXT_FUNCTION, $args, $expr);
        }
    }

    private function add_paths_to_expr(Expr &$expr): void
    {
        if (isset($this->settings[self::PATHS_SETTING])) {
            $expr = Config_Converter_Tools::add_method_call(self::class, self::PATHS_FUNCTION, $this->settings[self::PATHS_SETTING], $expr);
            unset($this->settings[self::PATHS_SETTING]);
        }
    }

    private function add_filters_to_expr(Expr &$expr): void
    {
        if (isset($this->settings[self::FILTERS_SETTING])) {
            foreach ($this->settings[self::FILTERS_SETTING] as $filter_name => $filter_value) {
                $filter = match ($filter_name) {
                    Name_Filter::NAME => new Name_Filter($filter_value),
                    Narrative_Filter::NAME => new Narrative_Filter($filter_value),
                    Role_Filter::NAME => new Role_Filter($filter_value),
                    Tag_Filter::NAME => new Tag_Filter($filter_value),
                    default => null,
                };
                if ($filter !== null) {
                    $expr = Config_Converter_Tools::add_method_call(self::class, self::FILTER_FUNCTION, [$filter->to_php_expr()], $expr);
                    unset($this->settings[self::FILTERS_SETTING][$filter_name]);
                }
            }
            if ($this->settings[self::FILTERS_SETTING] === []) {
                unset($this->settings[self::FILTERS_SETTING]);
            }
        }
    }
}
