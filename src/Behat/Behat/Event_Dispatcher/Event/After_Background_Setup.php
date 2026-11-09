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

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Setup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after background was setup for testing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Background_Setup extends Background_Tested implements After_Setup
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature, private readonly BackgroundNode $background, private readonly Setup $setup)
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
     * Returns scenario node.
     *
     * @deprecated Use getBackground() instead
     *
     * @return ScenarioLikeInterface
     */
    public function get_scenario(): BackgroundNode
    {
        return $this->background;
    }

    /**
     * Returns background node.
     */
    public function get_background(): BackgroundNode
    {
        return $this->background;
    }

    /**
     * Returns current test setup.
     */
    public function get_setup(): Setup
    {
        return $this->setup;
    }
}
