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
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event_Dispatcher\Event\Before_Tested;

/**
 * Represents an event before feature tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Before_Feature_Tested extends Feature_Tested implements Before_Tested
{
    /**
     * Initializes event.
     */
    public function __construct(Environment $env, private readonly FeatureNode $feature)
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
}
