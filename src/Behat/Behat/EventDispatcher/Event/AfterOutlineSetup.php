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
use Behat\Gherkin\Node\Outline_Node;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\After_Setup;
use Behat\Testwork\Tester\Setup\Setup;
/**
 * Represents an event right after outline setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Outline_Setup extends Outline_Tested implements After_Setup
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly Feature_Node $feature, private readonly Outline_Node $outline, private readonly Setup $setup)
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
     * Returns outline node.
     */
    public function get_outline(): Outline_Node
    {
        return $this->outline;
    }
    /**
     * Returns current test setup.
     */
    public function get_setup(): Setup
    {
        return $this->setup;
    }
}