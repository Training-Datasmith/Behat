<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Context\Annotation;

use Behat\Behat\Context\Annotation\Annotation_Reader;
use Behat\Behat\Transformation\Context\Factory\Transformation_Callee_Factory;
use Behat\Behat\Transformation\Transformation;
use ReflectionMethod;

/**
 * Step transformation annotation reader.
 *
 * Reads step transformations from a context method annotation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation_Annotation_Reader implements Annotation_Reader
{
    private static string $regex = '/^\@transform\s*+(.*+)$/i';

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $description
     */
    public function read_callee($context_class, ReflectionMethod $method, $doc_line, $description): ?Transformation
    {
        if (!preg_match(self::$regex, $doc_line, $match)) {
            return null;
        }
        $pattern = $match[1];

        return Transformation_Callee_Factory::create($context_class, $method, $pattern, $description);
    }
}
