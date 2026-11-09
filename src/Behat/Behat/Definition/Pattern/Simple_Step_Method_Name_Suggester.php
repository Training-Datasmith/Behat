<?php

declare(strict_types=1);

namespace Behat\Behat\Definition\Pattern;

use Behat\Behat\Util\Strict_Regex;

final class Simple_Step_Method_Name_Suggester implements Step_Method_Name_Suggester
{
    public const DEFAULT_NAME = 'stepDefinition1';

    public function suggest(string $step_text_without_placeholders): string
    {
        // Put the text in title case first so that it will become CamelCase when we strip strings
        $name = mb_convert_case($step_text_without_placeholders, MB_CASE_TITLE);
        // Remove characters that are never valid in a method name
        $name = Strict_Regex::replace('/[^a-zA-Z0-9_\x80-\xff]/u', '', $name);
        // Remove leading digits (these are the only characters that are valid in a name except at the beginning)
        $name = Strict_Regex::replace('/^\d+/', '', $name);
        if ($name === '') {
            // Context_Snippet_Generator::getUniqueMethodName will increment the trailing number if necessary so that
            // all step methods in a Context have unique names (e.g. stepDefinition2, stepDefinition3, etc).
            return self::DEFAULT_NAME;
        }

        // Switch from CamelCase to camelCase
        return mb_lcfirst($name);
    }
}
