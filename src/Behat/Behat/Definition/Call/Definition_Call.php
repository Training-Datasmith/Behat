<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

use Behat\Behat\Definition\Definition;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Call\Environment_Call;
use Behat\Testwork\Environment\Environment;

/**
 * Enhances environment call with definition information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @method Definition getCallee()
 */
final class Definition_Call extends Environment_Call
{
    /**
     * Initializes definition call.
     */
    public function __construct(Environment $environment, private readonly FeatureNode $feature, private readonly StepNode $step, Definition $definition, array $arguments, $error_reporting_level = null)
    {
        parent::__construct($environment, $definition, $arguments, $error_reporting_level);
    }

    /**
     * Returns step feature node.
     */
    public function get_feature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns definition step node.
     */
    public function get_step(): StepNode
    {
        return $this->step;
    }
}
