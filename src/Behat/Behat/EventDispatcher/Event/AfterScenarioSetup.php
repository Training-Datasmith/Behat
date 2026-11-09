<?php

declare(strict_types=1);

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event after scenario setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterScenarioSetup extends ScenarioTested implements AfterSetup
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly Scenario $scenario,
        private readonly Setup $setup,
    ) {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function getFeature(): \Behat\Gherkin\Node\FeatureNode
    {
        return $this->feature;
    }

    public function getScenario(): \Behat\Gherkin\Node\ScenarioLikeInterface
    {
        return $this->scenario;
    }

    /**
     * Returns current test setup.
     */
    public function getSetup(): \Behat\Testwork\Tester\Setup\Setup
    {
        return $this->setup;
    }
}
