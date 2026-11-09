<?php

declare(strict_types=1);
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
use Behat\Behat\Transformation\Regex_Generator;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Runtime_Callee;
use Exception;
use Stringable;

/**
 * Pattern-based transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pattern_Transformation extends Runtime_Callee implements Stringable, Transformation
{
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct(private $pattern, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }

    /**
     * Checks if transformer supports argument.
     */
    public function supports_definition_and_argument(Regex_Generator $regex_generator, Definition_Call $definition_call, $argument_value): bool
    {
        $regex = $regex_generator->generate_regex($definition_call->get_environment()->get_suite()->get_name(), $this->pattern, $definition_call->get_feature()->getLanguage());

        return $this->match($regex, $argument_value, $match);
    }

    /**
     * Transforms argument value using transformation and returns a new one.
     *
     * @throws Exception If transformation throws exception
     */
    public function transform_argument(Regex_Generator $regex_generator, Call_Center $call_center, Definition_Call $definition_call, $argument_value)
    {
        $regex = $regex_generator->generate_regex($definition_call->get_environment()->get_suite()->get_name(), $this->pattern, $definition_call->get_feature()->getLanguage());
        $this->match($regex, $argument_value, $arguments);
        $call = new Transformation_Call($definition_call->get_environment(), $definition_call->get_callee(), $this, $arguments);
        $result = $call_center->make_call($call);
        if ($result->has_exception()) {
            throw $result->get_exception();
        }

        return $result->get_return();
    }

    public function get_pattern()
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return 'PatternTransform ' . $this->pattern;
    }

    private function match($regex_pattern, $argument_value, &$match): bool
    {
        if (is_string($argument_value) && preg_match($regex_pattern, $argument_value, $match)) {
            // take arguments from capture groups if there are some
            if (count($match) > 1) {
                $match = array_slice($match, 1);
            }

            return true;
        }

        return false;
    }
}
