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

use Behat\Behat\Context\Environment\Context_Environment;
use Behat\Testwork\Call\Callee;
/**
 * Reads callees from a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Context_Reader
{
    /**
     * Reads callees from specific environment & context.
     *
     * @param string             $contextClass
     *
     * @return Callee[]
     */
    public function read_context_callees(Context_Environment $environment, $context_class);
}