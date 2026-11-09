<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Transformation;

use Behat\Behat\Definition\Call\Definition_Call;
use Behat\Behat\Transformation\Call\Transformation_Call;
use Behat\Behat\Transformation\Simple_Argument_Transformation;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Runtime_Callee;
use ReflectionMethod;
use Stringable;
/**
 * Name and return type object transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Token_Name_And_Return_Type_Transformation extends Runtime_Callee implements Stringable, Simple_Argument_Transformation
{
    private readonly Token_Name_Transformation $token_transformation;
    private readonly Return_Type_Transformation $return_transformation;
    public static function supports_pattern_and_method($pattern, ReflectionMethod $method): bool
    {
        return Token_Name_Transformation::supports_pattern_and_method($pattern, $method) && Return_Type_Transformation::supports_pattern_and_method('', $method);
    }
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct($pattern, callable|array $callable, ?string $description = null)
    {
        $this->token_transformation = new Token_Name_Transformation($pattern, $callable, $description);
        $this->return_transformation = new Return_Type_Transformation('', $callable, $description);
        parent::__construct($callable, $description);
    }
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_argument_value): bool
    {
        return $this->token_transformation->supports_definition_and_argument($definition_call, $argument_index, $argument_argument_value) && $this->return_transformation->supports_definition_and_argument($definition_call, $argument_index, $argument_argument_value);
    }
    public function transform_argument(Call_Center $call_center, Definition_Call $definition_call, $argument_index, $argument_value)
    {
        $call = new Transformation_Call($definition_call->get_environment(), $definition_call->get_callee(), $this, [$argument_value]);
        $result = $call_center->make_call($call);
        if ($result->has_exception()) {
            throw $result->get_exception();
        }
        return $result->get_return();
    }
    public function get_priority(): int
    {
        return 100;
    }
    public function get_pattern()
    {
        return $this->token_transformation->get_pattern();
    }
    public function __toString(): string
    {
        return 'NamedReturnTypeTransform ' . $this->get_pattern();
    }
}