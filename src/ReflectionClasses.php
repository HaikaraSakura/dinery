<?php

declare(strict_types=1);

namespace Haikara\Dinery;

use ArrayObject;
use Haikara\Dinery\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * 生成されたReflectionClassのコンテナ
 */
class ReflectionClasses implements ContainerInterface
{
    /**
     * 生成した依存性を格納する
     * @var ArrayObject<ReflectionClass>
     */
    protected ArrayObject $reflections;

    public function __construct() {
        $this->reflections = new ArrayObject;
    }

    public function get(string $id): ReflectionClass
    {
        if (!class_exists($id) && !interface_exists($id)) {
            throw new NotFoundException;
        }

        return $this->reflections[$id] ??= new ReflectionClass($id);
    }

    public function has(string $id): bool
    {
        return isset($this->reflections[$id]);
    }

    public function add(string $id, ReflectionClass $ref_class): void
    {
        $this->reflections[$id] = $ref_class;
    }
}
