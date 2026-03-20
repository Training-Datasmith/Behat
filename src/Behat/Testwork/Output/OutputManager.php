<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output;

use Behat\Testwork\Output\Exception\Formatter_Not_Found_Exception;
use Symfony\Component\Event_Dispatcher\Event_Dispatcher_Interface;
/**
 * Manages formatters and their configuration.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Output_Manager
{
    /**
     * @var Formatter[]
     */
    private array $formatters = [];
    /**
     * Initializes manager.
     */
    public function __construct(private readonly Event_Dispatcher_Interface $event_dispatcher)
    {
    }
    /**
     * Registers formatter.
     */
    public function register_formatter(Formatter $formatter): void
    {
        if (isset($this->formatters[$formatter->get_name()])) {
            $this->disable_formatter($formatter->get_name());
        }
        $this->formatters[$formatter->get_name()] = $formatter;
    }
    /**
     * Checks if formatter is registered.
     *
     * @param string $name
     */
    public function is_formatter_registered($name): bool
    {
        return isset($this->formatters[$name]);
    }
    /**
     * Returns formatter by name provided.
     *
     * @param string $name
     *
     * @return Formatter
     *
     * @throws FormatterNotFoundException
     */
    public function get_formatter($name)
    {
        if (!$this->is_formatter_registered($name)) {
            throw new Formatter_Not_Found_Exception(sprintf('`%s` formatter is not found or has not been properly registered. Registered formatters: `%s`.', $name, implode('`, `', array_keys($this->formatters))), $name);
        }
        return $this->formatters[$name];
    }
    /**
     * Returns all registered formatters.
     *
     * @return Formatter[]
     */
    public function get_formatters()
    {
        return $this->formatters;
    }
    /**
     * Enable formatter by name provided.
     *
     * @param string $formatter
     */
    public function enable_formatter($formatter): void
    {
        if (!$this->is_formatter_registered($formatter) && class_exists($formatter)) {
            $formatter_instance = new $formatter();
            $formatter = $formatter_instance->get_name();
            if (!$this->is_formatter_registered($formatter)) {
                $this->register_formatter($formatter_instance);
            }
        }
        $this->event_dispatcher->add_subscriber($this->get_formatter($formatter));
    }
    /**
     * Disable formatter by name provided.
     *
     * @param string $formatter
     */
    public function disable_formatter($formatter): void
    {
        $this->event_dispatcher->remove_subscriber($this->get_formatter($formatter));
    }
    /**
     * Disable all registered formatters.
     */
    public function disable_all_formatters(): void
    {
        array_map($this->disable_formatter(...), array_keys($this->formatters));
    }
    /**
     * Sets provided parameter to said formatter.
     *
     * @param string $formatter
     * @param string $parameterName
     */
    public function set_formatter_parameter($formatter, $parameter_name, $parameter_value): void
    {
        $formatter = $this->get_formatter($formatter);
        $printer = $formatter->get_output_printer();
        switch ($parameter_name) {
            case 'output_verbosity':
                $printer->set_output_verbosity($parameter_value);
                return;
            case 'output_path':
                $printer->set_output_path($parameter_value);
                return;
            case 'output_decorate':
                $printer->set_output_decorated($parameter_value);
                return;
            case 'output_styles':
                $printer->set_output_styles($parameter_value);
                return;
        }
        $formatter->set_parameter($parameter_name, $parameter_value);
    }
    /**
     * Sets provided formatter parameters.
     *
     * @param string $formatter
     */
    public function set_formatter_parameters($formatter, array $parameters): void
    {
        foreach ($parameters as $key => $val) {
            $this->set_formatter_parameter($formatter, $key, $val);
        }
    }
    /**
     * Sets provided parameter to all registered formatters.
     *
     * @param string $parameterName
     */
    public function set_formatters_parameter($parameter_name, $parameter_value): void
    {
        foreach (array_keys($this->formatters) as $formatter) {
            $this->set_formatter_parameter($formatter, $parameter_name, $parameter_value);
        }
    }
    /**
     * Sets provided parameters to all registered formatters.
     */
    public function set_formatters_parameters(array $parameters): void
    {
        foreach ($parameters as $key => $val) {
            $this->set_formatters_parameter($key, $val);
        }
    }
}