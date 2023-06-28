<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use Haikara\DiForklift\Exceptions\NotFoundException;
use LogicException;
use Psr\Container\ContainerInterface;

/**
 * 生成された依存性のコンテナ
 */
class Dependencies implements ContainerInterface
{
    /**
     * 生成処理を格納する
     * @var array
     */
    protected array $dependencies;

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException;
        }

        return $this->dependencies[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->dependencies[$id]);
    }

    public function add(string $id, mixed $concrete): void
    {
        if ($this->has($id)) {
            throw new LogicException('Dependenciesに登録済みの値が与えられました。ライブラリの根本的なバグです。');
        }

        $this->dependencies[$id] = $concrete;
    }
}