<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet;

use Behat\Behat\Context\Snippet\Context_Snippet;
use Behat\Gherkin\Node\Step_Node;
/**
 * Aggregates multiple similar snippets with different targets and steps.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Aggregate_Snippet
{
    /**
     * Initializes snippet.
     *
     * @param Snippet[] $snippets
     */
    public function __construct(private array $snippets)
    {
    }
    /**
     * Returns snippet type.
     */
    public function get_type(): string
    {
        return current($this->snippets)->get_type();
    }
    /**
     * Returns snippet unique ID (step type independent).
     */
    public function get_hash(): string
    {
        return current($this->snippets)->get_hash();
    }
    /**
     * Returns definition snippet text.
     */
    public function get_snippet(): string
    {
        return current($this->snippets)->get_snippet();
    }
    /**
     * Returns all steps interested in this snippet.
     *
     * @return StepNode[]
     */
    public function get_steps(): array
    {
        return array_unique(array_map(fn(Snippet $snippet) => $snippet->get_step(), $this->snippets), SORT_REGULAR);
    }
    /**
     * Returns all snippet targets.
     *
     * @return string[]
     */
    public function get_targets(): array
    {
        return array_unique(array_map(fn(Snippet $snippet) => $snippet->get_target(), $this->snippets));
    }
    /**
     * Returns the classes used in the snippet which should be imported.
     *
     * @return string[]
     */
    public function get_used_classes(): array
    {
        if ($this->snippets === []) {
            return [];
        }
        return array_unique(array_merge(...array_values(array_map(function (Snippet $snippet): array {
            if (!$snippet instanceof Context_Snippet) {
                return [];
            }
            return $snippet->get_used_classes();
        }, $this->snippets))));
    }
}