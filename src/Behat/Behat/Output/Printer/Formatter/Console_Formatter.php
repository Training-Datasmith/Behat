<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Printer\Formatter;

use Behat\Behat\Util\Regex_Exception;
use Behat\Behat\Util\Strict_Regex;
use Symfony\Component\Console\Formatter\OutputFormatter as BaseOutputFormatter;

/**
 * Symfony2 Console output formatter extended with custom highlighting tokens support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Console_Formatter extends BaseOutputFormatter
{
    public const CUSTOM_PATTERN = '/{\+([a-z-_]+)}(.*?){\-\1}/si';
    public const HREF_PATTERN = '/<href=([^>]+)>(.*?)<\/>/';

    /**
     * Formats a message according to the given styles.
     *
     * @param string $message The message to style
     *
     * @return string The styled message
     */
    public function format($message): string
    {
        try {
            $formatted_message = Strict_Regex::replace_callback(self::CUSTOM_PATTERN, $this->replace_style(...), $message);
        } catch (Regex_Exception $e) {
            $formatted_message = 'Error formatting output: ' . $e->getMessage();
        }

        return self::replace_href($formatted_message);
    }

    /**
     * Replaces style of the output.
     *
     * @return string The replaced style
     */
    private function replace_style(array $match)
    {
        if (!$this->isDecorated()) {
            return $match[2];
        }
        if ($this->hasStyle($match[1])) {
            $style = $this->getStyle($match[1]);
        } else {
            return $match[0];
        }

        return $style->apply($match[2]);
    }

    /**
     * @internal
     */
    public static function replace_href(string $message): string
    {
        return Strict_Regex::replace_callback(self::HREF_PATTERN, function ($matches): string {
            $url = $matches[1];
            $text = $matches[2];

            return "\x1b]8;;{$url}\x1b\\{$text}\x1b]8;;\x1b\\";
        }, $message);
    }
}
