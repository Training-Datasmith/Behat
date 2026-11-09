<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Hook\Context\Annotation;

use Behat\Behat\Context\Annotation\Annotation_Reader;
use Behat\Behat\Hook\Call\After_Feature;
use Behat\Behat\Hook\Call\After_Scenario;
use Behat\Behat\Hook\Call\After_Step;
use Behat\Behat\Hook\Call\Before_Feature;
use Behat\Behat\Hook\Call\Before_Scenario;
use Behat\Behat\Hook\Call\Before_Step;
use Behat\Testwork\Hook\Call\After_Suite;
use Behat\Testwork\Hook\Call\Before_Suite;
use Behat\Testwork\Hook\Call\Runtime_Hook;
use ReflectionMethod;
/**
 * Reads hook callees from context method annotations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Hook_Annotation_Reader implements Annotation_Reader
{
    private static string $regex = '/^\@(beforesuite|aftersuite|beforefeature|afterfeature|beforescenario|afterscenario|beforestep|afterstep)(?:\s+(.+))?$/i';
    /**
     * @var string[]
     */
    private static array $classes = ['beforesuite' => Before_Suite::class, 'aftersuite' => After_Suite::class, 'beforefeature' => Before_Feature::class, 'afterfeature' => After_Feature::class, 'beforescenario' => Before_Scenario::class, 'afterscenario' => After_Scenario::class, 'beforestep' => Before_Step::class, 'afterstep' => After_Step::class];
    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param string           $docLine
     * @param string           $description
     */
    public function read_callee($context_class, ReflectionMethod $method, $doc_line, $description): ?Runtime_Hook
    {
        if (!preg_match(self::$regex, $doc_line, $match)) {
            return null;
        }
        $type = strtolower($match[1]);
        $class = self::$classes[$type];
        $pattern = $match[2] ?? null;
        $callable = [$context_class, $method->get_name()];
        return new $class($pattern, $callable, $description);
    }
}