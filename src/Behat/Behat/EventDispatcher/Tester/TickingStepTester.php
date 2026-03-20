<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Event_Dispatcher\Tester;

use Behat\Behat\Tester\Result\Step_Result;
use Behat\Behat\Tester\Step_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
/**
 * Enable ticks during step testing to allow SigintController in Testwork
 * to handle an interupt (on PHP7).
 *
 * @see Behat\Testwork\EventDispatcher\Cli\SigintController
 * @deprecated since the way signals are handled changed to use pcntl_signal_dispatch
 *   this class is no longer needed
 *
 * @todo Remove this class in the next major version
 *
 * @author Peter Mitchell <peterjmit@gmail.com>
 */
final readonly class Ticking_Step_Tester implements Step_Tester
{
    /**
     * Initializes tester.
     */
    public function __construct(private Step_Tester $base_tester)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Step_Node $step, $skip)
    {
        return $this->base_tester->set_up($env, $feature, $step, $skip);
    }
    public function test(Environment $env, Feature_Node $feature, Step_Node $step, $skip)
    {
        declare (ticks=1);
        return $this->base_tester->test($env, $feature, $step, $skip);
    }
    public function tear_down(Environment $env, Feature_Node $feature, Step_Node $step, $skip, Step_Result $result)
    {
        return $this->base_tester->tear_down($env, $feature, $step, $skip, $result);
    }
}