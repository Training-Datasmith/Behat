<?php

declare(strict_types=1);
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
use ReflectionAttribute;
use ReflectionMethod;

/**
 * Reads definition Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Attribute_Reader implements Attribute_Reader
{
    /**
     * @var array<class-string<Attribute\Definition>, class-string<Call\Runtime_Definition>>
     */
    private static array $attribute_to_call_map = [Attribute\Given::class => Given::class, Attribute\When::class => When::class, Attribute\Then::class => Then::class];

    /**
     * Initializes reader.
     */
    public function __construct(private readonly Doc_Block_Helper $doc_block_helper)
    {
    }

    /**
     * @return list<Call\Runtime_Definition>
     */
    public function read_callees(string $context_class, ReflectionMethod $method): array
    {
        $attributes = $method->getAttributes(Attribute\Definition::class, ReflectionAttribute::IS_INSTANCEOF);
        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::$attribute_to_call_map[$attribute->getName()];
            $callable = [$context_class, $method->getName()];
            $description = null;
            if ($doc_block = $method->getDocComment()) {
                $description = $this->doc_block_helper->extract_description($doc_block);
            }
            $callees[] = new $class($attribute->newInstance()->get_pattern(), $callable, $description);
        }

        return $callees;
    }
}
