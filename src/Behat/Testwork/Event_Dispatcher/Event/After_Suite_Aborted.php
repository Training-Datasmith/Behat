<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Event_Dispatcher\Event;

use Behat\Testwork\Specification\No_Specifications_Iterator;
use Behat\Testwork\Specification\Specification_Iterator;

/**
 * Represents an event in which suite was aborted.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class After_Suite_Aborted extends Suite_Tested
{
    public function get_specification_iterator(): Specification_Iterator
    {
        return new No_Specifications_Iterator($this->get_suite());
    }
}
