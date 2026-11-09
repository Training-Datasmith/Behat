<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

enum Show_Output_Option: string
{
    public const OPTION_NAME = 'show_output';
    public const PARAMETER_NAME = 'showOutput';
    case Yes = 'yes';
    case No = 'no';
    case OnFail = 'on-fail';
    case InSummary = 'in-summary';
}
