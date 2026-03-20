<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Annotation;

use Behat\Behat\Util\Strict_Regex;
/**
 * Helper class for DocBlock parsing.
 */
class Doc_Block_Helper
{
    /**
     * Extracts a description from the provided docblock,
     * with support for multiline descriptions.
     */
    public function extract_description(string $doc_block): string
    {
        // Remove indentation
        $description = Strict_Regex::replace('/^[\s\t]*/m', '', $doc_block);
        // normalize line endings
        $description = str_replace("\r\n", "\n", $description);
        // Remove block comment syntax
        $description = Strict_Regex::replace('/^\/\*\*\s*|^\s*\*\s|^\s*\*\/$/m', '', (string) $description);
        // Remove annotations
        $description = Strict_Regex::replace('/^@.*$/m', '', $description);
        // Ignore docs after a "--" separator
        if (preg_match('/^--.*$/m', $description)) {
            $description_parts = preg_split('/^--.*$/m', $description);
            $description = array_shift($description_parts);
        }
        // Trim leading and trailing newlines
        return trim((string) $description, "\r\n");
    }
}