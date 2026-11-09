<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Event_Dispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Setup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event after step setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Step_Setup extends Step_Tested implements After_Setup
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature, private readonly StepNode $step, private readonly Setup $setup)
    {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function get_feature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns step node.
     */
    public function get_step(): StepNode
    {
        return $this->step;
    }

    /**
     * Returns current test setup.
     */
    public function get_setup(): Setup
    {
        return $this->setup;
    }

    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     *
     * @return bool
     */
    public function has_output()
    {
        return $this->setup->has_output();
    }
}
