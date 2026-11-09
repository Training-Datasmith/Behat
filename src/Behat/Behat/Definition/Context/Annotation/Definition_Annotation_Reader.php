<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Context\Annotation;

use Behat\Behat\Context\Annotation\Annotation_Reader;
use Behat\Behat\Definition\Call\Given;
use Behat\Behat\Definition\Call\Runtime_Definition;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Definition\Call\When;
use ReflectionMethod;

/**
 * Reads definition annotations from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Definition_Annotation_Reader implements Annotation_Reader
{
    private static string $regex = '/^\@(given|when|then)\s+(.+)$/i';
    /**
     * @var string[]
     */
    private static array $classes = ['given' => Given::class, 'when' => When::class, 'then' => Then::class];

    public function read_callee($context_class, ReflectionMethod $method, $doc_line, $description): ?Runtime_Definition
    {
        if (!preg_match(self::$regex, $doc_line, $match)) {
            return null;
        }
        $type = strtolower($match[1]);
        $class = self::$classes[$type];
        $pattern = $match[2];
        $callable = [$context_class, $method->getName()];

        return new $class($pattern, $callable, $description);
    }
}
