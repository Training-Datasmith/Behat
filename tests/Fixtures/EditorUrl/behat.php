<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->with_profile(new Profile('default'))
    ->with_profile(
        (new Profile('editor_url'))
        ->with_path_options(editor_url: 'phpstorm://open?file={relPath}&line={line}')
    );
