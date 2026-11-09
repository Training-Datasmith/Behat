<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Translator;

use Behat\Behat\Definition\Definition;
use Stringable;
/**
 * Represents definition translated to the specific language.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Translated_Definition implements Stringable, Definition
{
    /**
     * Initialises translated definition.
     *
     * @param string     $translatedPattern
     * @param string     $language
     */
    public function __construct(private readonly Definition $definition, private $translated_pattern, private $language)
    {
    }
    public function get_type()
    {
        return $this->definition->get_type();
    }
    public function get_pattern()
    {
        return $this->translated_pattern;
    }
    /**
     * Returns original (not translated) pattern.
     *
     * @return string
     */
    public function get_original_pattern()
    {
        return $this->definition->get_pattern();
    }
    /**
     * Returns language definition was translated to.
     *
     * @return string
     */
    public function get_language()
    {
        return $this->language;
    }
    public function get_description()
    {
        return $this->definition->get_description();
    }
    public function get_path()
    {
        return $this->definition->get_path();
    }
    public function is_a_method()
    {
        return $this->definition->is_a_method();
    }
    public function is_an_instance_method()
    {
        return $this->definition->is_an_instance_method();
    }
    public function get_callable()
    {
        return $this->definition->get_callable();
    }
    public function get_reflection()
    {
        return $this->definition->get_reflection();
    }
    public function get_original_definition(): Definition
    {
        return $this->definition;
    }
    public function __toString(): string
    {
        return $this->definition->__toString();
    }
}