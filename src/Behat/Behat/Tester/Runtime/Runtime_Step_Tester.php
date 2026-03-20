<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Definition\Call\Runtime_Definition;
use Behat\Behat\Definition\Definition_Finder;
use Behat\Behat\Definition\Exception\Search_Exception;
use Behat\Behat\Definition\Search_Result;
use Behat\Behat\Definition\Translator\Translated_Definition;
use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Failed_Step_Search_Result;
use Behat\Behat\Tester\Result\Skipped_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Behat\Tester\Result\Undefined_Step_Result;
use Behat\Behat\Tester\Step_Tester;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Successful_Setup;
use Behat\Testwork\Tester\Setup\Successful_Teardown;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Tester executing step tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Runtime_Step_Tester implements Step_Tester
{
    /**
     * Initialize tester.
     */
    public function __construct(private Definition_Finder $definition_finder, private Call_Center $call_center)
    {
    }
    public function set_up(Environment $env, Feature_Node $feature, Step_Node $step, $skip): Setup
    {
        return new Successful_Setup();
    }
    public function test(Environment $env, Feature_Node $feature, Step_Node $step, $skip = false): Step_Result
    {
        try {
            $search = $this->search_definition($env, $feature, $step);
            $result = $this->test_definition($env, $feature, $step, $search, $skip);
        } catch (Search_Exception $exception) {
            $result = new Failed_Step_Search_Result($exception);
        }
        return $result;
    }
    public function tear_down(Environment $env, Feature_Node $feature, Step_Node $step, $skip, Step_Result $result): Teardown
    {
        return new Successful_Teardown();
    }
    /**
     * Searches for a definition.
     *
     * @return SearchResult
     */
    private function search_definition(Environment $env, Feature_Node $feature, Step_Node $step)
    {
        return $this->definition_finder->find_definition($env, $feature, $step);
    }
    /**
     * Tests found definition.
     *
     * @param bool      $skip
     */
    private function test_definition(Environment $env, Feature_Node $feature, Step_Node $step, Search_Result $search, $skip): Step_Result
    {
        if (!$search->has_match()) {
            return new Undefined_Step_Result();
        }
        $definition = $search->get_matched_definition();
        // If the definition found is a translated definition, we need to mark the original definition
        if ($definition instanceof Translated_Definition) {
            $definition = $definition->get_original_definition();
        }
        // If a definition has been found, we mark it as used even if it may be skipped,
        // as we want to count skipped definitions as used
        if ($definition instanceof Runtime_Definition) {
            $definition->mark_as_used();
        }
        if ($skip) {
            return new Skipped_Step_Result($search);
        }
        $call = $this->create_definition_call($env, $feature, $search, $step);
        $result = $this->call_center->make_call($call);
        return new Executed_Step_Result($search, $result);
    }
    /**
     * Creates definition call.
     */
    private function create_definition_call(Environment $env, Feature_Node $feature, Search_Result $search, Step_Node $step): Definition_Call
    {
        $definition = $search->get_matched_definition();
        $arguments = $search->get_matched_arguments();
        return new Definition_Call($env, $feature, $step, $definition, $arguments);
    }
}