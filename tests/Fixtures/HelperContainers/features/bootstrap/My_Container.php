<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

final class My_Container implements ContainerInterface
{
    private ?Shared_Service $service = null;

    public function has(string $id): bool
    {
        return $id === 'shared_service';
    }

    public function get(string $id): Shared_Service
    {
        if ($id !== 'shared_service') {
            throw new InvalidArgumentException();
        }

        return $this->service ?? $this->service = new Shared_Service();
    }
}
