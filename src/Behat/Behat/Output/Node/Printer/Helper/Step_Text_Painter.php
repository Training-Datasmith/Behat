<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Util\Strict_Regex;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Paints step text (with tokens) according to found definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Step_Text_Painter
{
    /**
     * Initializes painter.
     */
    public function __construct(private Pattern_Transformer $pattern_transformer, private Result_To_String_Converter $result_converter)
    {
    }

    /**
     * Colorizes step text arguments according to definition.
     *
     * @param string     $text
     *
     * @return string
     */
    public function paint_text($text, Definition $definition, Test_Result $result)
    {
        $regex = $this->pattern_transformer->transform_pattern_to_regex($definition->get_pattern());
        $style = $this->result_converter->convert_result_to_string($result);
        $param_style = $style . '_param';
        // If it's just a string - skip
        if (!str_starts_with($regex, '/')) {
            return $text;
        }
        // Find arguments with offsets
        $matches = [];
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);
        // Replace arguments with colorized ones
        $shift = 0;
        $last_replacement_position = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key)) {
                continue;
            }
            if (-1 === $match[1]) {
                continue;
            }
            if (str_contains($match[0], '<')) {
                continue;
            }
            $offset = $match[1] + $shift;
            $value = $match[0];
            // Skip inner matches
            if ($last_replacement_position > $offset) {
                continue;
            }
            $last_replacement_position = $offset + strlen($value);
            $begin = substr($text, 0, $offset);
            $end = substr($text, $last_replacement_position);
            $format = "{-{$style}}{+{$param_style}}%s{-{$param_style}}{+{$style}}";
            $text = sprintf("%s{$format}%s", $begin, $value, $end);
            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $last_replacement_position += strlen($format) - 2;
        }
        // Replace "<", ">" with colorized ones
        $text = Strict_Regex::replace('/(<[^>]+>)/', "{-{$style}}{+{$param_style}}\$1{-{$param_style}}{+{$style}}", $text);

        return $text;
    }
}
