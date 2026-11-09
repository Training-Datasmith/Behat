<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Profile;

$profile = (new Profile('pretty_without_paths'))
    ->disableFormatter(Progress_Formatter::NAME)
    ->withFormatter(new Pretty_Formatter(paths: false))
;

return (new Config())->withProfile($profile);
