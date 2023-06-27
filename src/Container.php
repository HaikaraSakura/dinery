<?php

declare(strict_types=1);

namespace Haikara\DiForklift;

use Haikara\DiForklift\Attributes\Inject;
use Haikara\DiForklift\Exceptions\ContainerException;
use Haikara\DiForklift\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

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
        if (isset($this->dependencies[$id])) {
            return $this->dependencies[$id];
        }

        if (isset($this->definitions[$id])) {
            $this->dependencies[$id] = $this->definitions[$id]();
        }


        if (class_exists($id)) {
            $params = $this->getDependenciesFromReflectionClass(new ReflectionClass($id));
            $this->dependencies[$id] = new $id(...$params);
        }

        if (!isset($this->dependencies[$id])) {
            throw new NotFoundException();
        }

        return $this->dependencies[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->dependencies[$id]) || isset($this->definitions[$id]);
    }

    public function add(string $id, mixed $definition): void {
        $this->definitions[$id] = $definition;
    }

    /**
     * ReflectionClassを分析し、クラスのインスタンス化に必要な依存性を取り揃える
     * @param ReflectionClass $ref_class
     * @return array
     */
    protected function getDependenciesFromReflectionClass(ReflectionClass $ref_class): array
    {
        if (!$ref_class->isInstantiable()) {
            throw new ContainerException;
        }

        $ref_constructor = $ref_class->getConstructor();

        if ($ref_constructor === null) {
            return [];
        }

        $params = [];

        foreach ($ref_constructor->getParameters() as $ref_param) {
            $param_name = $ref_param->getName();
            $params[$param_name] = $this->getDependency($ref_param);
        }

        return $params;
    }

    /**
     * ReflectionParameterを分析し、必要な依存性を取得する
     * @param ReflectionParameter $ref_param
     * @return mixed
     */
    protected function getDependency(ReflectionParameter $ref_param)
    {
        $ref_type = $ref_param->getType();

        if ($this->hasInjectAttribute($ref_param)) {
            $inject = $this->getInjectAttributeInstance($ref_param);
            $dependency_name = $inject->getId();
            return $this->get($dependency_name);
        }

        // 型が指定されていなければ依存解決エラー
        if ($ref_type === null) {
            throw new ContainerException;
        }

        $type_name = $ref_type->getName();

        return $this->get($type_name);
    }

    /**
     * 引数がInject属性を持っているかどうか
     *
     * @param ReflectionParameter $ref_param
     * @return boolean
     */
    protected function hasInjectAttribute(ReflectionParameter $ref_param): bool
    {
        return isset($ref_param->getAttributes(Inject::class)[0]);
    }

    /**
     * 引数が持っているInject属性のインスタンスを返す
     *
     * @param ReflectionParameter $ref_param
     * @return ?Inject
     */
    protected function getInjectAttributeInstance(ReflectionParameter $ref_param): ?Inject
    {
        $ref_attr = $ref_param->getAttributes(Inject::class)[0];
        $attr_inject = $ref_attr->newInstance();

        if ($attr_inject instanceof Inject) {
            return $attr_inject;
        } else {
            return null;
        }
    }
}