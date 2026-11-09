<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use Behat\Config\Config_Converter_Interface;
use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Testwork\Output\Printer\Factory\Output_Factory;
use PhpParser\Node\Expr;

class Formatter implements Formatter_Config_Interface, Config_Converter_Interface
{
    private const OUTPUT_VERBOSITY_SETTING = 'output_verbosity';
    private const OUTPUT_PATH_SETTING = 'output_path';
    private const OUTPUT_DECORATE_SETTING = 'output_decorate';
    private const OUTPUT_STYLES_SETTING = 'output_styles';
    private const SHORT_SUMMARY_SETTING = 'short_summary';
    private const SHORT_SUMMARY_PARAMETER_NAME = 'shortSummary';
    private const OUTPUT_VERBOSITY_FUNCTION = 'withOutputVerbosity';
    private const OUTPUT_PATH_FUNCTION = 'withOutputPath';
    private const OUTPUT_DECORATE_FUNCTION = 'withOutputDecorated';
    private const OUTPUT_STYLES_FUNCTION = 'withOutputStyles';
    private const FORMATTER_FUNCTION_NAMES_PER_SETTING = [self::OUTPUT_VERBOSITY_SETTING => self::OUTPUT_VERBOSITY_FUNCTION, self::OUTPUT_PATH_SETTING => self::OUTPUT_PATH_FUNCTION, self::OUTPUT_DECORATE_SETTING => self::OUTPUT_DECORATE_FUNCTION, self::OUTPUT_STYLES_SETTING => self::OUTPUT_STYLES_FUNCTION];

    public function __construct(private readonly string $name, private array $settings = [])
    {
    }

    /**
     * @param int $level use Output_Factory::VERBOSITY_*
     */
    public function with_output_verbosity(int $level): self
    {
        $this->settings[self::OUTPUT_VERBOSITY_SETTING] = $level;

        return $this;
    }

    public function with_output_path(string $path): self
    {
        $this->settings[self::OUTPUT_PATH_SETTING] = $path;

        return $this;
    }

    public function with_output_decorated(bool $decorated = true): self
    {
        $this->settings[self::OUTPUT_DECORATE_SETTING] = $decorated;

        return $this;
    }

    public function with_output_styles(array $styles): self
    {
        $this->settings[self::OUTPUT_STYLES_SETTING] = $styles;

        return $this;
    }

    /**
     * @deprecated use with_output_verbosity() instead
     */
    public function withOutputVerbosity(int $level): self
    {
        return $this->with_output_verbosity($level);
    }

    /**
     * @deprecated use with_output_path() instead
     */
    public function withOutputPath(string $path): self
    {
        return $this->with_output_path($path);
    }

    /**
     * @deprecated use with_output_decorated() instead
     */
    public function withOutputDecorated(bool $decorated = true): self
    {
        return $this->with_output_decorated($decorated);
    }

    /**
     * @deprecated use with_output_styles() instead
     */
    public function withOutputStyles(array $styles): self
    {
        return $this->with_output_styles($styles);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function to_array(): array
    {
        return $this->settings;
    }

    public static function defaults(): array
    {
        return [];
    }

    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        $formatter_object = Config_Converter_Tools::create_object(self::class);
        $expr = $this->apply_base_settings($formatter_object);
        $arguments = $this->settings === [] ? [$this->name] : [$this->name, $this->settings];
        Config_Converter_Tools::add_arguments_to_constructor($arguments, $formatter_object);

        return $expr;
    }

    /**
     * @internal
     */
    protected function to_php_expr_for_named_formatter(): Expr
    {
        $formatter_object = Config_Converter_Tools::create_object(static::class);
        $expr = $this->apply_base_settings($formatter_object);
        $defaults = static::defaults();
        $arguments = [];
        foreach ($defaults as $name => $default_value) {
            if ($this->settings[$name] === $default_value) {
                continue;
            }
            $value = $this->settings[$name];
            if ($name === Show_Output_Option::OPTION_NAME) {
                $value = Show_Output_Option::from($value);
                $name = Show_Output_Option::PARAMETER_NAME;
            }
            if ($name === self::SHORT_SUMMARY_SETTING) {
                $name = self::SHORT_SUMMARY_PARAMETER_NAME;
            }
            if ($name === Pretty_Formatter::PRINT_SKIPPED_STEPS_SETTING) {
                $name = Pretty_Formatter::PRINT_SKIPPED_STEPS_PARAMETER_NAME;
            }
            $arguments[$name] = $value;
        }
        if ($arguments !== []) {
            Config_Converter_Tools::add_arguments_to_constructor($arguments, $formatter_object);
        }

        return $expr;
    }

    private function apply_base_settings(Expr $expr): Expr
    {
        foreach ($this->settings as $setting_name => $setting) {
            $function_name = Formatter::FORMATTER_FUNCTION_NAMES_PER_SETTING[$setting_name] ?? null;
            if ($function_name !== null) {
                if ($setting_name === self::OUTPUT_VERBOSITY_SETTING) {
                    $setting = Config_Converter_Tools::find_reference_to_class_constant(Output_Factory::class, $setting);
                }
                $expr = Config_Converter_Tools::add_method_call(self::class, $function_name, [$setting], $expr);
                unset($this->settings[$setting_name]);
            }
        }

        return $expr;
    }
}
