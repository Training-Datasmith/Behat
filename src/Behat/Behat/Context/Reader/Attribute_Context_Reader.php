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

use Behat\Behat\Context\Attribute\Attribute_Reader;
use Behat\Behat\Context\Environment\Context_Environment;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Reads context callees by Attributes using registered Attribute readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Attribute_Context_Reader implements Context_Reader
{
    /**
     * @var Attribute_Reader[]
     */
    private array $readers = [];

    /**
     * Registers attribute reader.
     */
    public function register_attribute_reader(Attribute_Reader $reader): void
    {
        $this->readers[] = $reader;
    }

    /**
     * @return list<Runtime_Callee>
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
     * @return list<Runtime_Callee>
     */
    private function read_method_callees(string $context_class, ReflectionMethod $method): array
    {
        $callees = [];
        foreach ($this->readers as $reader) {
            $callees = array_merge($this->read_parent_callees($context_class, $method, $reader, $callees), $reader->read_callees($context_class, $method));
        }

        return $callees;
    }

    /**
     * @return list<Runtime_Callee>
     */
    private function read_parent_callees(string $context_class, ReflectionMethod $method, Attribute_Reader $reader, array $callees): array
    {
        try {
            $prototype = $method->getPrototype();
            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $reader->read_callees($context_class, $prototype));
            }
        } catch (ReflectionException) {
        }

        return $callees;
    }
}
