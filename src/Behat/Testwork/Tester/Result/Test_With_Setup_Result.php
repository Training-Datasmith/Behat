<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Result;

use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * Represents a test result with both setup and teardown attached.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Test_With_Setup_Result implements Test_Result
{
    /**
     * Initializes test result.
     */
    public function __construct(private Setup $setup, private Test_Result $result, private Teardown $teardown)
    {
    }
    public function is_passed(): bool
    {
        return self::PASSED == $this->get_result_code();
    }
    public function get_result_code()
    {
        if (!$this->setup->is_successful()) {
            return self::FAILED;
        }
        if (!$this->teardown->is_successful()) {
            return self::FAILED;
        }
        return $this->result->get_result_code();
    }
}