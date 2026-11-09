<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Exception\Invalid_Pattern_Exception;
use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Behat\Definition\Pattern\Simple_Step_Method_Name_Suggester;
use Behat\Behat\Definition\Pattern\Step_Method_Name_Suggester;
use Behat\Behat\Util\Strict_Regex;

/**
 * Defines a way to handle turnip patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Turnip_Pattern_Policy implements Pattern_Policy
{
    public const TOKEN_REGEX = "[\"']?(?P<%s>(?<=\")[^\"]*(?=\")|(?<=')[^']*(?=')|\\-?[\\w\\.\\,]+)['\"]?";
    public const PLACEHOLDER_REGEXP = '/\\\\:(\\w+)/';
    public const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\\(([^\\\\]+)\\\\\\)(\s)?/';
    public const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\\/(\w+)/';
    /**
     * @var string[]
     */
    private array $regex_cache = [];
    /**
     * @var string[]
     */
    private static array $placeholder_patterns = ['/(?<!\\w)"[^"]+"(?!\\w)/', "/(?<!\\w)'[^']+'(?!\\w)/", '/(?<!\\w|\\.|\\,)\\-?\\d+(?:[\\.\\,]\\d+)?(?!\\w|\\.|\\,)/'];

    public function __construct(private readonly Step_Method_Name_Suggester $method_name_suggester = new Simple_Step_Method_Name_Suggester())
    {
    }

    public function supports_pattern_type($type): bool
    {
        return null === $type || 'turnip' === $type;
    }

    public function generate_pattern($step_text): Pattern
    {
        $count = 0;
        $pattern = (string) $step_text;
        foreach (self::$placeholder_patterns as $replace_pattern) {
            $pattern = Strict_Regex::replace_callback($replace_pattern, function () use (&$count): string {
                return ':arg' . ++$count;
            }, $pattern);
        }
        $pattern = $this->escape_alternation_syntax($pattern);
        $method_name = $this->method_name_suggester->suggest(Strict_Regex::replace(self::$placeholder_patterns, '', (string) $step_text));

        return new Pattern($method_name, $pattern, $count);
    }

    public function supports_pattern($pattern): bool
    {
        return true;
    }

    public function transform_pattern_to_regex($pattern)
    {
        if (!isset($this->regex_cache[$pattern])) {
            $this->regex_cache[$pattern] = $this->create_transformed_regex($pattern);
        }

        return $this->regex_cache[$pattern];
    }

    /**
     * @param string $pattern
     */
    private function create_transformed_regex($pattern): string
    {
        $regex = preg_quote($pattern, '/');
        $regex = $this->replace_tokens_with_regex_capture_groups($regex);
        $regex = $this->replace_turnip_optional_ending_with_regex($regex);
        $regex = $this->replace_turnip_alternative_words_with_regex($regex);

        return '/^' . $regex . '$/iu';
    }

    /**
     * Replaces turnip tokens with regex capture groups.
     */
    private function replace_tokens_with_regex_capture_groups(string $regex): string
    {
        return Strict_Regex::replace_callback(self::PLACEHOLDER_REGEXP, $this->replace_token_with_regex_capture_group(...), $regex);
    }

    private function replace_token_with_regex_capture_group($token_match): string
    {
        if (strlen((string) $token_match[1]) > 32) {
            throw new Invalid_Pattern_Exception("Token name should not exceed 32 characters, but `{$token_match[1]}` was used.");
        }

        return sprintf(self::TOKEN_REGEX, $token_match[1]);
    }

    /**
     * Replaces turnip optional ending with regex non-capturing optional group.
     */
    private function replace_turnip_optional_ending_with_regex(string $regex): string
    {
        return Strict_Regex::replace(self::OPTIONAL_WORD_REGEXP, '(?:\1)?(?:\2)?(?:\3)?', $regex);
    }

    /**
     * Replaces turnip alternative words with regex non-capturing alternating group.
     */
    private function replace_turnip_alternative_words_with_regex(string $regex): string
    {
        $regex = Strict_Regex::replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);

        return $this->remove_escaping_of_alternation_syntax($regex);
    }

    /**
     * Adds escaping to alternation syntax in pattern.
     *
     * By default, Turnip treats `/` as alternation syntax. Meaning `one/two` for Turnip
     * means either `one` or `two`. Sometimes though you'll want to use slash character
     * with different purpose (URL, UNIX paths). In this case, you would escape slashes
     * with backslash.
     *
     * This method adds escaping to all slashes in generated snippets.
     */
    private function escape_alternation_syntax(string $pattern): string
    {
        return str_replace('/', '\/', $pattern);
    }

    /**
     * Removes escaping of alternation syntax from regex.
     *
     * This method removes those escaping backslashes from your slashes, so your steps
     * could be matched against your escaped definitions.
     */
    private function remove_escaping_of_alternation_syntax(string $regex): string
    {
        return str_replace('\\\\/', '/', $regex);
    }
}
