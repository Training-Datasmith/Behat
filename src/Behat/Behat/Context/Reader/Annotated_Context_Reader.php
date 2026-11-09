<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Annotation\Annotation_Reader;
use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Environment\Context_Environment;
use Behat\Behat\Util\Strict_Regex;
use Behat\Testwork\Call\Callee;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Reads context callees by annotations using registered annotation readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Annotated_Context_Reader implements Context_Reader
{
    public const DOCLINE_TRIMMER_REGEX = '/^\/\*\*\s*|^\s*\*\s*|\s*\*\/$|\s*$/';
    /**
     * @var string[]
     */
    private static array $ignore_annotations = ['@param', '@return', '@throws', '@see', '@uses', '@todo'];
    /**
     * @var Annotation_Reader[]
     */
    private array $readers = [];

    /**
     * Initializes reader.
     */
    public function __construct(private readonly Doc_Block_Helper $doc_block_helper)
    {
    }

    /**
     * Registers annotation reader.
     */
    public function register_annotation_reader(Annotation_Reader $reader): void
    {
        $this->readers[] = $reader;
    }

    /**
     * @return list<Callee>
     */
    public function read_context_callees(Context_Environment $environment, $context_class): array
    {
        $reflection = new ReflectionClass($context_class);
        $callees = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->read_method_callees($reflection->getName(), $method) as $callee) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }

    /**
     * Loads callees associated with specific method.
     *
     * @param string           $class
     *
     * @return list<Callee>
     */
    private function read_method_callees($class, ReflectionMethod $method): array
    {
        $callees = [];
        // read parent annotations
        try {
            $prototype = $method->getPrototype();
            // error occurs on every second PHP stable release - getPrototype() returns itself
            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $this->read_method_callees($class, $prototype));
            }
        } catch (ReflectionException) {
        }
        if ($doc_block = $method->getDocComment()) {
            return array_merge($callees, $this->read_doc_block_callees($class, $method, $doc_block));
        }

        return $callees;
    }

    /**
     * Reads callees from the method doc block.
     *
     * @param string           $class
     *
     * @return list<Callee>
     */
    private function read_doc_block_callees($class, ReflectionMethod $method, $doc_block): array
    {
        $callees = [];
        $description = $this->doc_block_helper->extract_description($doc_block);
        $doc_block = $this->merge_multilines($doc_block);
        foreach (explode("\n", $doc_block) as $doc_line) {
            $doc_line = Strict_Regex::replace(self::DOCLINE_TRIMMER_REGEX, '', $doc_line);
            if ($this->is_empty($doc_line)) {
                continue;
            }
            if ($this->is_not_annotation($doc_line)) {
                continue;
            }
            if ($callee = $this->read_doc_line_callee($class, $method, $doc_line, $description)) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }

    /**
     * Merges multiline strings (strings ending with "\").
     */
    private function merge_multilines(string $doc_block): string
    {
        return Strict_Regex::replace('#\\\\$\\s*+\\*\\s*+([^\\\\$]++)#m', '$1', $doc_block);
    }

    /**
     * Checks if provided doc lien is empty.
     */
    private function is_empty(string $doc_line): bool
    {
        return '' === $doc_line;
    }

    /**
     * Checks if provided doc line is not an annotation.
     */
    private function is_not_annotation(string $doc_line): bool
    {
        return !str_starts_with($doc_line, '@');
    }

    /**
     * Reads callee from provided doc line using registered annotation readers.
     *
     * @param string      $class
     * @param string|null $description
     *
     * @return Callee|null
     */
    private function read_doc_line_callee($class, ReflectionMethod $method, string $doc_line, $description = null)
    {
        if ($this->is_ignored_annotation($doc_line)) {
            return null;
        }
        foreach ($this->readers as $reader) {
            if ($callee = $reader->read_callee($class, $method, $doc_line, $description)) {
                return $callee;
            }
        }

        return null;
    }

    /**
     * Checks if provided doc line is one of the ignored annotations.
     */
    private function is_ignored_annotation(string $doc_line): bool
    {
        $low_doc_line = strtolower($doc_line);
        foreach (self::$ignore_annotations as $ignored_annotation) {
            if (str_starts_with($low_doc_line, $ignored_annotation)) {
                return true;
            }
        }

        return false;
    }
}
