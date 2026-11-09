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

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Definition_Repository;
use Behat\Behat\Definition\Exception\Ambiguous_Match_Exception;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Search_Result;
use Behat\Behat\Definition\Translator\Definition_Translator;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Argument\Argument_Organiser;
use Behat\Testwork\Argument\Exception\Unexpected_Multiline_Argument_Exception;
use Behat\Testwork\Environment\Environment;

/**
 * Searches for a step definition using definition repository.
 *
 * @see Definition_Repository
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Repository_Search_Engine implements Search_Engine
{
    /**
     * Initializes search engine.
     */
    public function __construct(private Definition_Repository $repository, private Pattern_Transformer $pattern_transformer, private Definition_Translator $translator, private Argument_Organiser $argument_organiser)
    {
    }

    /**
     * @throws Ambiguous_Match_Exception
     */
    public function search_definition(Environment $environment, FeatureNode $feature, StepNode $step): ?Search_Result
    {
        $suite = $environment->get_suite();
        $language = $feature->getLanguage();
        $step_text = $step->getText();
        $multi = $step->getArguments();
        $step_location = $feature->getFile() . ':' . $step->getLine();
        $definitions = [];
        $result = null;
        foreach ($this->repository->get_environment_definitions($environment) as $definition) {
            $definition = $this->translator->translate_definition($suite, $definition, $language);
            $new_result = $this->match($definition, $step_location, $step_text, $multi);
            if (!$new_result instanceof Search_Result) {
                continue;
            }
            $result = $new_result;
            $matched_definition = $new_result->get_matched_definition();
            if ($matched_definition instanceof Definition) {
                $definitions[] = $matched_definition;
            }
        }
        if (count($definitions) > 1) {
            throw new Ambiguous_Match_Exception($result->get_matched_text(), $definitions);
        }

        return $result;
    }

    /**
     * Attempts to match provided definition against a step text.
     *
     * @param ArgumentInterface[] $multiline
     */
    private function match(Definition $definition, string $step_location, string $step_text, array $multiline): ?Search_Result
    {
        $match = $this->pattern_transformer->match_pattern($definition->get_pattern(), $step_text);
        if ($match === false) {
            return null;
        }
        $function = $definition->get_reflection();
        $match = array_merge($match, array_values($multiline));
        try {
            $arguments = $this->argument_organiser->organise_arguments($function, $match);
        } catch (Unexpected_Multiline_Argument_Exception $e) {
            // Add the location of the feature and step that caused the problem.
            // We can't do this where the exception is originally thrown because the Argument_Organiser interface
            // is used for other types of function / argument processing e.g. Context constructors, so it has no
            // knowledge of the concept of a Step.
            throw new Unexpected_Multiline_Argument_Exception($e->getMessage() . PHP_EOL . 'This is probably an error in your step implementation or in ' . $step_location, previous: $e);
        }

        return new Search_Result($definition, $step_text, $arguments);
    }
}
