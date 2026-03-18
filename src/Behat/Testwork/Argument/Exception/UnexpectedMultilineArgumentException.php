<?php

declare(strict_types=1);

namespace Behat\Testwork\Argument\Exception;

use InvalidArgumentException;

class UnexpectedMultilineArgumentException extends InvalidArgumentException implements ArgumentException
{
}
