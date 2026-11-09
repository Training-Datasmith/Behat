<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Translator\Translator_Interface;
use Behat\Behat\Transformation\Regex_Generator;
use Behat\Behat\Transformation\Simple_Argument_Transformation;
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\Transformation\Pattern_Transformation;
use Behat\Behat\Transformation\Transformation_Repository;
use Behat\Gherkin\Node\Argument_Interface;
use Behat\Testwork\Call\Call_Center;
/**
 * Argument transformer based on transformations repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Repository_Argument_Transformer implements Argument_Transformer, Regex_Generator
{
    /**
     * Initializes transformer.
     */
    public function __construct(private Transformation_Repository $repository, private Call_Center $call_center, private Pattern_Transformer $pattern_transformer, private Translator_Interface $translator)
    {
    }
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_value): bool
    {
        return count($this->repository->get_environment_transformations($definition_call->get_environment())) > 0;
    }
    public function transform_argument(Definition_Call $definition_call, $argument_index, $argument_value)
    {
        $environment = $definition_call->get_environment();
        [$simple_transformations, $normal_transformations] = $this->split_simple_and_normal_transformations($this->repository->get_environment_transformations($environment));
        $new_value = $this->apply_simple_transformations($simple_transformations, $definition_call, $argument_index, $argument_value);
        return $this->apply_normal_transformations($normal_transformations, $definition_call, $argument_index, $new_value);
    }
    public function generate_regex($suite_name, $pattern, $language)
    {
        $translated_pattern = $this->translator->trans($pattern, [], $suite_name, $language);
        if ($pattern == $translated_pattern) {
            return $this->pattern_transformer->transform_pattern_to_regex($pattern);
        }
        return $this->pattern_transformer->transform_pattern_to_regex($translated_pattern);
    }
    /**
     * Apply simple argument transformations in priority order.
     *
     * @param SimpleArgumentTransformation[] $transformations
     * @param int|string                     $index
     */
    private function apply_simple_transformations(array $transformations, Definition_Call $definition_call, $index, $value)
    {
        usort($transformations, fn(Simple_Argument_Transformation $t1, Simple_Argument_Transformation $t2): int => $t2->get_priority() <=> $t1->get_priority());
        $new_value = $value;
        foreach ($transformations as $transformation) {
            $new_value = $this->transform($definition_call, $transformation, $index, $new_value);
        }
        return $new_value;
    }
    /**
     * Apply normal (non-simple) argument transformations.
     *
     * @param Transformation[] $transformations
     * @param int|string       $index
     */
    private function apply_normal_transformations(array $transformations, Definition_Call $definition_call, $index, $value)
    {
        $new_value = $value;
        foreach ($transformations as $transformation) {
            $new_value = $this->transform($definition_call, $transformation, $index, $new_value);
        }
        return $new_value;
    }
    /**
     * Transforms argument value using registered transformers.
     *
     * @param int|string $index
     */
    private function transform(Definition_Call $definition_call, Transformation $transformation, $index, $value)
    {
        if (is_object($value) && !$value instanceof Argument_Interface) {
            return $value;
        }
        if ($transformation instanceof Simple_Argument_Transformation && $transformation->supports_definition_and_argument($definition_call, $index, $value)) {
            return $transformation->transform_argument($this->call_center, $definition_call, $index, $value);
        }
        if ($transformation instanceof Pattern_Transformation && $transformation->supports_definition_and_argument($this, $definition_call, $value)) {
            return $transformation->transform_argument($this, $this->call_center, $definition_call, $value);
        }
        return $value;
    }
    /**
     * Splits transformations into simple and normal ones.
     *
     * @param Transformation[] $transformations
     */
    private function split_simple_and_normal_transformations(array $transformations): array
    {
        return array_reduce($transformations, fn($acc, $t): array => [$t instanceof Simple_Argument_Transformation ? array_merge($acc[0], [$t]) : $acc[0], $t instanceof Simple_Argument_Transformation ? $acc[1] : array_merge($acc[1], [$t])], [[], []]);
    }
}