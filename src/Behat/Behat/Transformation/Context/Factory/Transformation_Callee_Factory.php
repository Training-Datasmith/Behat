<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Context\Factory;

use Behat\Behat\Transformation\Simple_Argument_Transformation;
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\Transformation\Column_Based_Table_Transformation;
use Behat\Behat\Transformation\Transformation\Pattern_Transformation;
use Behat\Behat\Transformation\Transformation\Return_Type_Transformation;
use Behat\Behat\Transformation\Transformation\Row_Based_Table_Transformation;
use Behat\Behat\Transformation\Transformation\Table_Column_Transformation;
use Behat\Behat\Transformation\Transformation\Table_Row_Transformation;
use Behat\Behat\Transformation\Transformation\Token_Name_And_Return_Type_Transformation;
use Behat\Behat\Transformation\Transformation\Token_Name_Transformation;
use ReflectionMethod;
/**
 * Generates the callee for a transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal
 */
class Transformation_Callee_Factory
{
    public static function create(string $context_class, ReflectionMethod $method, string $pattern, ?string $description): Transformation
    {
        $callable = [$context_class, $method->get_name()];
        foreach (self::simple_transformations() as $transformation) {
            if ($transformation::supports_pattern_and_method($pattern, $method)) {
                return new $transformation($pattern, $callable, $description);
            }
        }
        return new Pattern_Transformation($pattern, $callable, $description);
    }
    /**
     * Returns list of default transformations.
     *
     * @return list<class-string<SimpleArgumentTransformation>>
     */
    private static function simple_transformations(): array
    {
        return [Row_Based_Table_Transformation::class, Column_Based_Table_Transformation::class, Table_Row_Transformation::class, Table_Column_Transformation::class, Token_Name_And_Return_Type_Transformation::class, Return_Type_Transformation::class, Token_Name_Transformation::class];
    }
}