<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Argument;

use Reflection_Function_Abstract;
/**
 * Organises function arguments using its reflection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Argument_Organiser
{
    /**
     * Organises arguments using function reflection.
     *
     * @param mixed[]                    $arguments
     *
     * @return mixed[]
     */
    public function organise_arguments(Reflection_Function_Abstract $function, array $arguments);
}