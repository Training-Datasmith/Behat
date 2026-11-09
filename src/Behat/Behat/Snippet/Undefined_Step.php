<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Represents an undefined step in a specific environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Undefined_Step
{
    /**
     * Initializes undefined step.
     */
    public function __construct(private Environment $environment, private StepNode $step)
    {
    }

    /**
     * Returns environment that needs this step.
     */
    public function get_environment(): Environment
    {
        return $this->environment;
    }

    /**
     * Returns undefined step node.
     */
    public function get_step(): StepNode
    {
        return $this->step;
    }
}
