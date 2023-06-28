<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use ArrayObject;
use Haikara\DiForklift\Exceptions\ContainerException;
use Haikara\DiForklift\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

/**
 * 生成処理のコンテナ
 */
class Definitions implements ContainerInterface
{
    /**
     * 生成処理を格納する
     * @var ArrayObject<callable>
     */
    protected ArrayObject $definitions;

    public function __construct() {
        $this->definitions = new ArrayObject;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException;
        }

        return $this->definitions[$id]();
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function add(string $id, callable $definition): void
    {
        if ($this->has($id)) {
            throw new ContainerException('生成処理の定義を上書きすることは許可されていません。');
        }

        $this->definitions[$id] = $definition;
    }
}
