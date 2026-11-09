<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Service_Container\Exception;

use RuntimeException;
/**
 * Extension exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Extension_Exception extends RuntimeException implements Service_Container_Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $extensionName
     */
    public function __construct($message, private $extension_name)
    {
        parent::__construct($message);
    }
    /**
     * Returns name of the extension that caused exception.
     *
     * @return string
     */
    public function get_extension_name()
    {
        return $this->extension_name;
    }
}