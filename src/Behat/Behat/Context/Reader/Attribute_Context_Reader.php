<?php

declare (strict_types=1);
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
use Behat\Testwork\Call\Runtime_Callee;
use ReflectionClass;
use Reflection_Exception;
use ReflectionMethod;
/**
 * Reads context callees by Attributes using registered Attribute readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Attribute_Context_Reader implements Context_Reader
{
    /**
     * @var AttributeReader[]
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
     * @return list<RuntimeCallee>
     */
    public function read_context_callees(Context_Environment $environment, $context_class): array
    {
        $reflection = new ReflectionClass($context_class);
        $callees = [];
        foreach ($reflection->get_methods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->read_method_callees($reflection->get_name(), $method) as $callee) {
                $callees[] = $callee;
            }
        }
        return $callees;
    }
    /**
     * @return list<RuntimeCallee>
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
     * @return list<RuntimeCallee>
     */
    private function read_parent_callees(string $context_class, ReflectionMethod $method, Attribute_Reader $reader, array $callees): array
    {
        try {
            $prototype = $method->get_prototype();
            if ($prototype->get_declaring_class()->get_name() !== $method->get_declaring_class()->get_name()) {
                $callees = array_merge($callees, $reader->read_callees($context_class, $prototype));
            }
        } catch (Reflection_Exception) {
        }
        return $callees;
    }
}