<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * 生成処理を格納する
     * @var array
     */
    protected array $definitions;

    /**
     * 生成された依存性を格納する
     * @var array
     */
    protected array $dependencies;

    public function get(string $id): mixed
    {
        return $this->dependencies[$id] ??= $this->definitions[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->dependencies[$id]) || isset($this->definitions[$id]);
    }

    public function add(string $id, mixed $definition): void {
        $this->definitions[$id] = $definition;
    }
}