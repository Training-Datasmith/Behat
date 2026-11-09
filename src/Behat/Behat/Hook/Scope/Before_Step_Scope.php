<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents a Before_Step hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Before_Step_Scope implements Step_Scope
{
    /**
     * Initializes scope.
     */
    public function __construct(private Environment $environment, private FeatureNode $feature, private StepNode $step)
    {
    }

    /**
     * Returns hook scope name.
     */
    public function get_name(): string
    {
        return self::BEFORE;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function get_suite()
    {
        return $this->environment->get_suite();
    }

    /**
     * Returns hook environment.
     */
    public function get_environment(): Environment
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     */
    public function get_feature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns scope step.
     */
    public function get_step(): StepNode
    {
        return $this->step;
    }
}
