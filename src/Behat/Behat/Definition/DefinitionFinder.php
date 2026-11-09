<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition;

use Behat\Behat\Definition\Search\Search_Engine;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
/**
 * Finds specific step definition in environment using registered search engines.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Finder
{
    /**
     * @var SearchEngine[]
     */
    private array $engines = [];
    /**
     * Registers definition search engine.
     */
    public function register_search_engine(Search_Engine $search_engine): void
    {
        $this->engines[] = $search_engine;
    }
    /**
     * Searches definition for a provided step in a provided environment.
     *
     * @return SearchResult
     */
    public function find_definition(Environment $environment, Feature_Node $feature, Step_Node $step)
    {
        foreach ($this->engines as $engine) {
            $result = $engine->search_definition($environment, $feature, $step);
            if (null !== $result && $result->has_match()) {
                return $result;
            }
        }
        return new Search_Result();
    }
}