<?php

declare(strict_types=1);

namespace Haikara\Dinery;

use ArrayObject;
use Haikara\Dinery\Exceptions\NotFoundException;
use LogicException;
use Psr\Container\ContainerInterface;

/**
 * 生成された依存性のコンテナ
 */
class Dependencies implements ContainerInterface
{
    /**
     * 生成した依存性を格納する
     * @var ArrayObject<callable>
     */
    protected ArrayObject $dependencies;

    public function __construct() {
        $this->dependencies = new ArrayObject;
    }

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
