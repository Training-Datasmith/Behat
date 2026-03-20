<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
/**
 * Represents a BeforeStep hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Before_Step_Scope implements Step_Scope
{
    /**
     * Initializes scope.
     */
    public function __construct(private Environment $environment, private Feature_Node $feature, private Step_Node $step)
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
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
    /**
     * Returns scope step.
     */
    public function get_step(): Step_Node
    {
        return $this->step;
    }
}