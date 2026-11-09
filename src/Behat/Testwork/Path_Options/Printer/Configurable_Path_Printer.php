<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Path_Options\Printer;

use Behat\Behat\Util\Strict_Regex;
use Symfony\Component\Console\Formatter\OutputFormatter;

final class Configurable_Path_Printer
{
    private readonly string $base_path;

    public function __construct(string $base_path, private bool $print_absolute_paths, private ?string $editor_url = null, private array $remove_prefix = [])
    {
        $real_base_path = realpath($base_path);
        if ($real_base_path) {
            $base_path = $real_base_path;
        }
        $this->base_path = $base_path;
        $this->remove_prefix = $remove_prefix;
    }

    public function set_print_absolute_paths(bool $print_absolute_paths): void
    {
        $this->print_absolute_paths = $print_absolute_paths;
    }

    public function set_editor_url(?string $editor_url): void
    {
        $this->editor_url = $editor_url;
    }

    public function set_remove_prefix(array $remove_prefix): void
    {
        $this->remove_prefix = $remove_prefix;
    }

    /**
     * Conditionally transforms paths to relative and adds editor links if configured.
     */
    public function process_paths_in_text(string $text, bool $apply_editor_url = true): string
    {
        // If no editor URL is set or the caller opts out, use the original behavior
        if ($this->editor_url === null || $apply_editor_url === false) {
            $processed_text = $this->print_absolute_paths ? $text : str_replace($this->base_path . DIRECTORY_SEPARATOR, '', $text);
            // Apply removePrefix if configured
            foreach ($this->remove_prefix as $prefix) {
                $processed_text = str_replace($prefix, '', $processed_text);
            }

            return $processed_text;
        }
        // Search for paths in the text
        $base_path_pattern = preg_quote($this->base_path . DIRECTORY_SEPARATOR, '/');
        $pattern = '/(' . $base_path_pattern . '[^:\s]+)((:|\s+line\s+)(\d+))?/';

        return Strict_Regex::replace_callback($pattern, function (array $matches): string {
            $file_path = $matches[1];
            $line = $matches[4] ?? null;
            // Calculate absolute and relative paths
            $abs_path = $file_path;
            $rel_path = str_replace($this->base_path . DIRECTORY_SEPARATOR, '', $file_path);
            // Format the path according to printAbsolutePaths setting
            $display_path = $this->print_absolute_paths ? $abs_path : $rel_path;
            // Remove prefixes if configured
            foreach ($this->remove_prefix as $prefix) {
                if (str_starts_with($display_path, $prefix)) {
                    $display_path = substr($display_path, strlen($prefix));
                    break;
                }
            }
            // If no line number is present, use empty string
            if ($line === null) {
                $line = '';
            }
            // Replace placeholders in the editor URL
            $editor_url = str_replace(['{absPath}', '{relPath}', '{line}'], [$abs_path, $rel_path, $line], $this->editor_url);

            // Create a link with the path
            // $matches[2] represents the line number with its prefix (`:LINE` or ` line LINE`)
            return '<href=' . OutputFormatter::escape($editor_url) . '>' . $display_path . ($matches[2] ?? '') . '</>';
        }, $text);
    }
}
