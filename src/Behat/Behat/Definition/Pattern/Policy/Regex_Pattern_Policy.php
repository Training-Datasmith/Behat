<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Pattern\Policy;

use function array_keys;
use Behat\Behat\Definition\Exception\Invalid_Pattern_Exception;
use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Behat\Definition\Pattern\Simple_Step_Method_Name_Suggester;
use Behat\Behat\Definition\Pattern\Step_Method_Name_Suggester;
use Behat\Behat\Util\Strict_Regex;
/**
 * Defines a way to handle regex patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Regex_Pattern_Policy implements Pattern_Policy
{
    /**
     * @var array<string,string>
     */
    private static array $replace_patterns = [
        "/(?<=\\W|^)\\\\'(?:((?!\\').)*)\\\\'(?=\\W|\$)/" => "'([^']*)'",
        // Single quoted strings
        '/(?<=\W|^)\"(?:[^\"]*)\"(?=\W|$)/' => '"([^"]*)"',
        // Double quoted strings
        '/(?<=\W|^)(\d+)(?=\W|$)/' => '(\d+)',
    ];
    public function __construct(private readonly Step_Method_Name_Suggester $method_name_suggester = new Simple_Step_Method_Name_Suggester())
    {
    }
    public function supports_pattern_type($type): bool
    {
        return 'regex' === $type;
    }
    public function generate_pattern($step_text): Pattern
    {
        $method_name = $this->method_name_suggester->suggest(Strict_Regex::replace(array_keys(self::$replace_patterns), '', $this->escape_step_text($step_text)));
        $step_regex = $this->generate_regex($step_text);
        $placeholder_count = $this->count_placeholders($step_text, $step_regex);
        return new Pattern($method_name, '/^' . $step_regex . '$/', $placeholder_count);
    }
    public function supports_pattern($pattern): bool
    {
        return (bool) preg_match('/^(?:\{.*\}|([~\/#`]).*\1)[imsxADSUXJu]*$/s', $pattern);
    }
    public function transform_pattern_to_regex($pattern)
    {
        if (false === @preg_match($pattern, 'anything')) {
            $error = error_get_last();
            $error_message = $error['message'] ?? '';
            throw new Invalid_Pattern_Exception(sprintf('The regex `%s` is invalid: %s', $pattern, $error_message));
        }
        return $pattern;
    }
    /**
     * Generates regex from step text.
     *
     * @param string $stepText
     */
    private function generate_regex($step_text): string
    {
        return Strict_Regex::replace(array_keys(self::$replace_patterns), array_values(self::$replace_patterns), $this->escape_step_text($step_text));
    }
    /**
     * Counts regex placeholders using provided text.
     *
     * @param string $stepText
     */
    private function count_placeholders($step_text, string $step_regex): int
    {
        preg_match('/^' . $step_regex . '$/', $step_text, $matches);
        return count($matches) ? count($matches) - 1 : 0;
    }
    /**
     * Returns escaped step text.
     */
    private function escape_step_text(string $step_text): string
    {
        return Strict_Regex::replace('/([\/\[\]\(\)\\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $step_text);
    }
}