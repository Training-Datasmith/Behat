<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Context\Attribute;

use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Attribute\Attribute_Reader;
use Behat\Behat\Definition\Call;
use Behat\Behat\Definition\Call\Given;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Definition\Call\When;
use Behat\Step as Attribute;
use Reflection_Attribute;
use ReflectionMethod;
/**
 * Reads definition Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Attribute_Reader implements Attribute_Reader
{
    /**
     * @var array<class-string<Attribute\Definition>, class-string<Call\RuntimeDefinition>>
     */
    private static array $attribute_to_call_map = [Attribute\Given::class => Given::class, Attribute\When::class => When::class, Attribute\Then::class => Then::class];
    /**
     * Initializes reader.
     */
    public function __construct(private readonly Doc_Block_Helper $doc_block_helper)
    {
    }
    /**
     * @return list<Call\RuntimeDefinition>
     */
    public function read_callees(string $context_class, ReflectionMethod $method): array
    {
        $attributes = $method->get_attributes(Attribute\Definition::class, Reflection_Attribute::IS_INSTANCEOF);
        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::$attribute_to_call_map[$attribute->get_name()];
            $callable = [$context_class, $method->get_name()];
            $description = null;
            if ($doc_block = $method->get_doc_comment()) {
                $description = $this->doc_block_helper->extract_description($doc_block);
            }
            $callees[] = new $class($attribute->new_instance()->get_pattern(), $callable, $description);
        }
        return $callees;
    }
}