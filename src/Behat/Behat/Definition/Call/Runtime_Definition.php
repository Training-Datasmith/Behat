<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Call\Runtime_Callee;
use Stringable;

/**
 * Represents a step definition created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Runtime_Definition extends Runtime_Callee implements Stringable, Definition
{
    private bool $used = false;

    /**
     * Initializes definition.
     *
     * @param string      $type
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct(private $type, private $pattern, callable|array $callable, ?string $description = null)
    {
        parent::__construct($callable, $description);
    }

    public function get_type()
    {
        return $this->type;
    }

    public function get_pattern()
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return $this->get_type() . ' ' . $this->get_pattern();
    }

    /**
     * @internal
     */
    public function mark_as_used(): void
    {
        $this->used = true;
    }

    /**
     * @internal
     */
    public function has_been_used(): bool
    {
        return $this->used;
    }
}
