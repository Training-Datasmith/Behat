<?php

use Behat\Behat\Context\Custom_Snippet_Accepting_Context;

final class Feature_Context_Custom_Snippet implements Custom_Snippet_Accepting_Context
{
    public static function get_accepted_snippet_type(): string
    {
        return 'regex';
    }
}
