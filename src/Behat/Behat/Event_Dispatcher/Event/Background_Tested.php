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
use Behat\Gherkin\Node\NodeInterface;
use Behat\Testwork\Event_Dispatcher\Event\Lifecycle_Event;

/**
 * Represents a background event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Background_Tested extends Lifecycle_Event implements Scenario_Like_Tested
{
    public const BEFORE = 'tester.background_tested.before';
    public const AFTER_SETUP = 'tester.background_tested.after_setup';
    public const BEFORE_TEARDOWN = 'tester.background_tested.before_teardown';
    public const AFTER = 'tester.background_tested.after';

    /**
     * Returns background node.
     *
     * @return BackgroundNode
     */
    abstract public function get_background();

    /**
     * Returns node.
     *
     * @return NodeInterface
     */
    final public function get_node()
    {
        return $this->getBackground();
    }
}
