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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\Before_Teardown;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Represents an event right before outline teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Outline_Teardown extends Outline_Tested implements Before_Teardown
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature, private readonly OutlineNode $outline, private readonly Test_Result $result)
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
     * Returns outline node.
     */
    public function get_outline(): OutlineNode
    {
        return $this->outline;
    }

    /**
     * Returns current test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
}
