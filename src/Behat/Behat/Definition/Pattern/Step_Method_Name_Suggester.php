<?php

declare(strict_types=1);

namespace Behat\Behat\Definition\Pattern;

/**
 * Suggests a suitable method name for a step definition based on the text of the step.
 *
 * Suggested names do not have to be unique - the Context_Snippet_Generator will add a numerical suffix if the Context
 * already contains a method with the suggested name.
 */
interface Step_Method_Name_Suggester
{
    public function suggest(string $step_text_without_placeholders): string;
}
