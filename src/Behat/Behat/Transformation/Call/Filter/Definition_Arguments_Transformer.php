<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Call\Filter;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Transformation\Exception\Unsupported_Call_Exception;
use Behat\Behat\Transformation\Transformer\Argument_Transformer;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\Call_Filter;
/**
 * Handles definition calls by intercepting them and transforming their arguments using transformations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Arguments_Transformer implements Call_Filter
{
    /**
     * @var ArgumentTransformer[]
     */
    private array $argument_transformers = [];
    /**
     * Registers new argument transformer.
     */
    public function register_argument_transformer(Argument_Transformer $transformer): void
    {
        $this->argument_transformers[] = $transformer;
    }
    public function supports_call(Call $call): bool
    {
        return $call instanceof Definition_Call;
    }
    public function filter_call(Call $call)
    {
        if (!$call instanceof Definition_Call) {
            throw new Unsupported_Call_Exception(sprintf('DefinitionArgumentTransformer can not filter `%s` call.', $call::class), $call);
        }
        $new_arguments = [];
        $transformed = false;
        foreach ($call->get_arguments() as $index => $value) {
            $new_value = $this->transform_argument($call, $index, $value);
            if ($new_value !== $value) {
                $transformed = true;
            }
            $new_arguments[$index] = $new_value;
        }
        if (!$transformed) {
            return $call;
        }
        return new Definition_Call($call->get_environment(), $call->get_feature(), $call->get_step(), $call->get_callee(), $new_arguments, $call->get_error_reporting_level());
    }
    /**
     * Transforms call argument using registered transformers.
     */
    private function transform_argument(Definition_Call $definition_call, int|string $index, $value)
    {
        foreach ($this->argument_transformers as $transformer) {
            if (!$transformer->supports_definition_and_argument($definition_call, $index, $value)) {
                continue;
            }
            return $transformer->transform_argument($definition_call, $index, $value);
        }
        return $value;
    }
}