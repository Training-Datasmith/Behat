<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Event;

use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\Before_Tested;
/**
 * Represents an event before step test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Step_Tested extends Step_Tested implements Before_Tested
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly Feature_Node $feature, private readonly Step_Node $step)
    {
        parent::__construct($env);
    }
    /**
     * Returns feature.
     */
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
    /**
     * Returns step node.
     */
    public function get_step(): Step_Node
    {
        return $this->step;
    }
}