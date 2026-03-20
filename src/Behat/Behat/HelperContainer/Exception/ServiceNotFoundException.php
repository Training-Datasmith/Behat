<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Helper_Container\Exception;

use InvalidArgumentException;
use Psr\Container\Not_Found_Exception_Interface;
/**
 * Represents an exception thrown when service ID is not found inside the container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Service_Not_Found_Exception extends InvalidArgumentException implements Helper_Container_Exception, Not_Found_Exception_Interface
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $serviceId
     */
    public function __construct($message, private $service_id)
    {
        parent::__construct($message);
    }
    /**
     * Returns service ID that caused exception.
     *
     * @return string
     */
    public function get_service_id()
    {
        return $this->service_id;
    }
}