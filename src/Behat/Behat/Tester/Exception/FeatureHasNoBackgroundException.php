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

use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Exception\Testwork_Exception;
use RuntimeException;
/**
 * Represents exception throw during attempt to test non-existent feature background.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Feature_Has_No_Background_Exception extends RuntimeException implements Testwork_Exception
{
    /**
     * Initializes exception.
     *
     * @param string      $message
     */
    public function __construct($message, private readonly Feature_Node $feature)
    {
        parent::__construct($message);
    }
    /**
     * Returns feature that caused exception.
     */
    public function get_feature(): Feature_Node
    {
        return $this->feature;
    }
}