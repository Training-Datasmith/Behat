<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Exception\Stringer;

use Behat\Testwork\Exception\Exception_Presenter;
use Exception;
/**
 * Finds a best way to present as a string particular.
 *
 * @see ExceptionPresenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Exception_Stringer
{
    /**
     * Checks if stringer supports provided exception.
     *
     * @return bool
     */
    public function supports_exception(Exception $exception);
    /**
     * Strings provided exception.
     *
     * @param int $verbosity
     *
     * @return string
     */
    public function string_exception(Exception $exception, $verbosity);
}