<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    public function get(string $id): mixed
    {
    }

    public function has(string $id): bool
    {
    }
}