<?php

declare (strict_types=1);
namespace Behat\Config\Filter;

final class Role_Filter extends Filter
{
    public const NAME = 'role';
    public function __construct(string $value)
    {
        parent::__construct(self::NAME, $value);
    }
}