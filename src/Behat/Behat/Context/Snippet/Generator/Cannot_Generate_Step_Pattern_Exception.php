<?php

declare (strict_types=1);
namespace Behat\Behat\Context\Snippet\Generator;

use UnexpectedValueException;
final class Cannot_Generate_Step_Pattern_Exception extends UnexpectedValueException
{
    public function __construct(public readonly string $step_text)
    {
        parent::__construct(sprintf('Cannot automatically generate a step pattern matching `%s`', $step_text));
    }
}