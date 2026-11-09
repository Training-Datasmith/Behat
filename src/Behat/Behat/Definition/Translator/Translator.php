<?php

declare(strict_types=1);

namespace Behat\Behat\Definition\Translator;

class Translator extends \Symfony\Component\Translation\Translator implements Translator_Interface
{
    public function get_locale(): string
    {
        return $this->getLocale();
    }
}
