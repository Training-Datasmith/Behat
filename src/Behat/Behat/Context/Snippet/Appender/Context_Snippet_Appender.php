<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Snippet\Appender;

use Behat\Behat\Snippet\Aggregate_Snippet;
use Behat\Behat\Snippet\Appender\Snippet_Appender;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Behat\Util\Strict_Regex;
use Behat\Testwork\Filesystem\Filesystem_Logger;
use ReflectionClass;
/**
 * Appends context-related snippets to their context classes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Context_Snippet_Appender implements Snippet_Appender
{
    public const PENDING_EXCEPTION_CLASS = Pending_Exception::class;
    public function __construct(private ?Filesystem_Logger $logger = null)
    {
    }
    public function supports_snippet(Aggregate_Snippet $snippet): bool
    {
        return 'context' === $snippet->get_type();
    }
    public function append_snippet(Aggregate_Snippet $snippet): void
    {
        foreach ($snippet->get_targets() as $context_class) {
            $reflection = new ReflectionClass($context_class);
            $content = file_get_contents($reflection->get_file_name());
            foreach ($snippet->get_used_classes() as $class) {
                if (!$this->is_class_imported($class, $content)) {
                    $content = $this->import_class($class, $content);
                }
            }
            $generated = rtrim(strtr($snippet->get_snippet(), ['\\' => '\\\\', '$' => '\$']));
            $content = Strict_Regex::replace('/}\s*$/', "\n" . $generated . "\n}\n", $content);
            $path = $reflection->get_file_name();
            file_put_contents($path, $content);
            $this->log_snippet_addition($snippet, $path);
        }
    }
    /**
     * Checks if context file already has class in it.
     *
     * @param string $class
     * @param string $contextFileContent
     */
    private function is_class_imported($class, string|bool $context_file_content): bool
    {
        $class_import_regex = sprintf('@use[^;]*%s.*;@ms', preg_quote($class, '@'));
        return 1 === preg_match($class_import_regex, $context_file_content);
    }
    /**
     * Adds use-block for class.
     *
     * @param string $contextFileContent
     */
    private function import_class(string $class, string|bool $context_file_content): string
    {
        $replace_with = '$1use ' . $class . ";\n\$2;";
        return Strict_Regex::replace('@^(.*)(use\s+[^;]*);@m', $replace_with, $context_file_content, 1);
    }
    /**
     * Logs snippet addition to the provided path (if logger is given).
     *
     * @param string           $path
     */
    private function log_snippet_addition(Aggregate_Snippet $snippet, $path): void
    {
        if (!$this->logger instanceof Filesystem_Logger) {
            return;
        }
        $steps = $snippet->get_steps();
        $reason = sprintf('`<comment>%s</comment>` definition added', $steps[0]->get_text());
        $this->logger->file_updated($path, $reason);
    }
}