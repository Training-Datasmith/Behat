<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition;

/**
 * Step definition search result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Search_Result
{
    /**
     * Registers search match.
     *
     * @param string|null $matchedText
     */
    public function __construct(private readonly ?Definition $definition = null, private $matched_text = null, private readonly ?array $arguments = null)
    {
    }
    /**
     * Checks if result contains a match.
     */
    public function has_match(): bool
    {
        return $this->definition instanceof Definition;
    }
    /**
     * Returns matched definition.
     */
    public function get_matched_definition(): ?Definition
    {
        return $this->definition;
    }
    /**
     * Returns matched text.
     *
     * @return string|null
     */
    public function get_matched_text()
    {
        return $this->matched_text;
    }
    /**
     * Returns matched definition arguments.
     */
    public function get_matched_arguments(): ?array
    {
        return $this->arguments;
    }
}