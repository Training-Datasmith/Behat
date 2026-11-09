<?php

declare(strict_types=1);

namespace Behat\Testwork\Argument\Exception;

use InvalidArgumentException;

class Unexpected_Multiline_Argument_Exception extends InvalidArgumentException implements Argument_Exception
{
}
