<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Annotation;

use Behat\Testwork\Call\Callee;
use ReflectionMethod;

/**
 * Reads custom annotation of a provided context method into a Callee.
 *
 * @see Annotated_Context_Reader
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Annotation_Reader
{
    /**
     * Reads all callees associated with a provided method.
     *
     * @param string           $description
     *
     * @return Callee|null
     */
    public function read_callee($context_class, ReflectionMethod $method, $doc_line, $description);
}
