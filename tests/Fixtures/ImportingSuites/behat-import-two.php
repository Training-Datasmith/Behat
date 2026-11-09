<?php

$config = new Behat\Config\Config();
$config
    ->import('config/suites/first.php')
    ->import('config/suites/second.php')
;

return $config;
