<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Tester\Exception;

use Behat\Testwork\Tester\Exception\Tester_Exception;
use RuntimeException;
/**
 * Represents a pending exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pending_Exception extends RuntimeException implements Tester_Exception
{
    /**
     * Initializes pending exception.
     *
     * @param string $text
     */
    public function __construct($text = 'TODO: write pending definition')
    {
        parent::__construct($text);
    }
}