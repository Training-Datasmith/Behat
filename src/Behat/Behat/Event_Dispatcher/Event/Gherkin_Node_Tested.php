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

use Behat\Gherkin\Node\NodeInterface;

/**
 * Represents a Gherkin node based event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Gherkin_Node_Tested
{
    /**
     * Returns node.
     *
     * @return NodeInterface
     */
    public function get_node();
}
