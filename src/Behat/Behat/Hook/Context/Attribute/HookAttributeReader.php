<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Context\Attribute;

use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Attribute\Attribute_Reader;
use Behat\Hook\After_Feature;
use Behat\Hook\After_Scenario;
use Behat\Hook\After_Step;
use Behat\Hook\After_Suite;
use Behat\Hook\Before_Feature;
use Behat\Hook\Before_Scenario;
use Behat\Hook\Before_Step;
use Behat\Hook\Before_Suite;
use Behat\Hook\Hook;
use Behat\Testwork\Hook\Call\Runtime_Hook;
use Reflection_Attribute;
use ReflectionMethod;
final readonly class Hook_Attribute_Reader implements Attribute_Reader
{
    /**
     * @var array<class-string<Hook>, class-string<RuntimeHook>>
     */
    private const KNOWN_ATTRIBUTES = [After_Feature::class => \Behat\Behat\Hook\Call\After_Feature::class, After_Scenario::class => \Behat\Behat\Hook\Call\After_Scenario::class, After_Step::class => \Behat\Behat\Hook\Call\After_Step::class, Before_Feature::class => \Behat\Behat\Hook\Call\Before_Feature::class, Before_Scenario::class => \Behat\Behat\Hook\Call\Before_Scenario::class, Before_Step::class => \Behat\Behat\Hook\Call\Before_Step::class, Before_Suite::class => \Behat\Testwork\Hook\Call\Before_Suite::class, After_Suite::class => \Behat\Testwork\Hook\Call\After_Suite::class];
    /**
     * Initializes reader.
     */
    public function __construct(private Doc_Block_Helper $doc_block_helper)
    {
    }
    /**
     * @return list<RuntimeHook>
     */
    public function read_callees(string $context_class, ReflectionMethod $method): array
    {
        $attributes = $method->get_attributes(Hook::class, Reflection_Attribute::IS_INSTANCEOF);
        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::KNOWN_ATTRIBUTES[$attribute->get_name()];
            $callable = [$context_class, $method->get_name()];
            $description = null;
            if ($doc_block = $method->get_doc_comment()) {
                $description = $this->doc_block_helper->extract_description($doc_block);
            }
            $callees[] = new $class($attribute->new_instance()->get_filter_string(), $callable, $description);
        }
        return $callees;
    }
}