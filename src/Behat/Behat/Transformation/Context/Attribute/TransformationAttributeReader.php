<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Context\Attribute;

use Behat\Behat\Context\Annotation\Doc_Block_Helper;
use Behat\Behat\Context\Attribute\Attribute_Reader;
use Behat\Behat\Transformation\Context\Factory\Transformation_Callee_Factory;
use Behat\Behat\Transformation\Transformation;
use Behat\Transformation as Attribute;
use ReflectionMethod;
/**
 * Reads transformation Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Transformation_Attribute_Reader implements Attribute_Reader
{
    public function __construct(private Doc_Block_Helper $doc_block_helper)
    {
    }
    /**
     * @return list<Transformation>
     */
    public function read_callees(string $context_class, ReflectionMethod $method): array
    {
        $attributes = $method->get_attributes(Attribute\Transform::class);
        $callees = [];
        foreach ($attributes as $attribute) {
            $pattern = $attribute->new_instance()->pattern;
            $description = null;
            if ($doc_block = $method->get_doc_comment()) {
                $description = $this->doc_block_helper->extract_description($doc_block);
            }
            $callees[] = Transformation_Callee_Factory::create($context_class, $method, $pattern, $description);
        }
        return $callees;
    }
}