<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Definition\Call\Definition_Call;

/**
 * Transforms a single argument value.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Argument_Transformer
{
    /**
     * Checks if transformer supports argument.
     *
     * @return bool
     */
    public function supports_definition_and_argument(Definition_Call $definition_call, $argument_index, $argument_value);

    /**
     * Transforms argument value using transformation and returns a new one.
     */
    public function transform_argument(Definition_Call $definition_call, $argument_index, $argument_value);
}
