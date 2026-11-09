<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Hook;

use Attribute;

/**
 * Represents an Attribute for Before_Step hook.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Before_Step implements Hook
{
    public function __construct(public $filter_string = null)
    {
    }

    public function get_filter_string(): ?string
    {
        return $this->filter_string;
    }
}
