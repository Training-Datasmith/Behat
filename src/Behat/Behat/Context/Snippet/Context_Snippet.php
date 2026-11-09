<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet;

use Behat\Behat\Snippet\Snippet;
use Behat\Gherkin\Node\StepNode;

/**
 * Represents a definition snippet for a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Context_Snippet implements Snippet
{
    /**
     * Initializes definition snippet.
     */
    public function __construct(private StepNode $step, private string $template, private string $context_class, private array $used_classes = [])
    {
    }

    public function get_type(): string
    {
        return 'context';
    }

    public function get_hash(): string
    {
        return md5($this->template);
    }

    public function get_snippet(): string
    {
        return sprintf($this->template, $this->step->getKeywordType());
    }

    public function get_step(): StepNode
    {
        return $this->step;
    }

    public function get_target(): string
    {
        return $this->context_class;
    }

    /**
     * Returns the classes used in the snippet which should be imported.
     *
     * @return string[]
     */
    public function get_used_classes(): array
    {
        return $this->used_classes;
    }
}
