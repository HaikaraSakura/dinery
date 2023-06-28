<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use Psr\Container\ContainerInterface;

/**
 * 生成処理のコンテナ
 */
class Definitions implements ContainerInterface
{
    /**
     * 生成処理を格納する
     * @var callable[]
     */
    protected array $definitions;

    public function get(string $id): mixed
    {
        return $this->definitions[$id]();
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function add(string $id, callable $definition): void
    {
        $this->definitions[$id] = $definition;
    }
}
