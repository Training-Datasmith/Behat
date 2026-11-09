<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Search;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Searches for a step definition in a specific environment.
 *
 * @see Definition_Finder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Search_Engine
{
    /**
     * Searches for a step definition.
     *
     * @return Search_Result|null
     */
    public function search_definition(Environment $environment, FeatureNode $feature, StepNode $step);
}
