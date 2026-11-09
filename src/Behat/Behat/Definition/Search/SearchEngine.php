<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Search;

use Behat\Behat\Definition\Definition_Finder;
use Behat\Behat\Definition\Search_Result;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
/**
 * Searches for a step definition in a specific environment.
 *
 * @see DefinitionFinder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Search_Engine
{
    /**
     * Searches for a step definition.
     *
     * @return SearchResult|null
     */
    public function search_definition(Environment $environment, Feature_Node $feature, Step_Node $step);
}