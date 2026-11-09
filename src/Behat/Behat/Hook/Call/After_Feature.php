<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\Feature_Scope;

/**
 * Represents an After_Feature hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Feature extends Runtime_Feature_Hook
{
    /**
     * Initializes hook.
     *
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($filter_string, $callable, $description = null)
    {
        parent::__construct(Feature_Scope::AFTER, $filter_string, $callable, $description);
    }

    public function get_name(): string
    {
        return 'After_Feature';
    }
}
