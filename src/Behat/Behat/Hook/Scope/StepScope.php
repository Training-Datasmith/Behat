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
use Behat\Testwork\Hook\Scope\Hook_Scope;
/**
 * Represents a step hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Step_Scope extends Hook_Scope
{
    public const BEFORE = 'step.before';
    public const AFTER = 'step.after';
    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function get_feature();
    /**
     * Returns scope step.
     *
     * @return StepNode
     */
    public function get_step();
}