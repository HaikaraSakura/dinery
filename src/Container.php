<?php

declare(strict_types=1);

namespace Haikara\Dinery;

use Closure;
use Haikara\Dinery\Attributes\Inject;
use Haikara\Dinery\Exceptions\ContainerException;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    /**
     * 生成処理を格納する
     * @var Definitions
     */
    protected Definitions $definitions;

    /**
     * 生成された依存性を格納する
     * @var Dependencies
     */
    protected Dependencies $dependencies;

    /**
     * 生成されたReflectionClassのコンテナ
     * @var ReflectionClasses
     */
    protected ReflectionClasses $reflections;

    protected bool $instance_reuse = true;

    public function __construct() {
        $this->definitions = new Definitions;
        $this->dependencies = new Dependencies;
        $this->reflections = new ReflectionClasses;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id): mixed
    {
        // 生成済みならそれを返す
        if ($this->dependencies->has($id)) {
            return $this->dependencies->get($id);
        }

        // 生成済みではないが定義済みの場合、生成処理を実行
        if ($this->definitions->has($id)) {
            $definition = $this->definitions->get($id);

            // DefinitionReuseなら、依存性を再利用する
            if ($definition instanceof DefinitionReuse) {
                $dependency = $this->call($definition->get());
                $this->dependencies->add($id, $dependency);
            } else {
                $dependency = $this->call($definition);
            }

            return $dependency;
        }

        // 未登録のIDなら自動解決を試みる
        $dependency = $this->resolve($id);

        if ($this->instance_reuse) {
            $this->dependencies->add($id, $dependency);
        }

        return $dependency;
    }

    /**
     * IDが登録済みかどうか
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        // 生成済み、もしくは生成処理を定義済みならtrue
        return $this->dependencies->has($id) || $this->definitions->has($id);
    }

    /**
     * 生成処理の登録。
     * $definitionがnullなら$idに指定された値の生成処理を自動で登録する
     *
     * @param string $id
     * @param ?callable $definition
     * @return void
     */
    public function add(string $id, ?callable $definition = null): void {
        $this->instance_reuse
            ? $this->addReuse($id, $definition)
            : $this->addEach($id, $definition);
    }

    /**
     * 生成処理の登録。インスタンスを使いまわしさせる。
     *
     * @param string $id
     * @param callable|null $definition
     * @return void
     */
    public function addReuse(string $id, ?callable $definition = null): void {
        $definition ??= fn () => $this->resolve($id);

        if (! $definition instanceof Closure) {
            $definition = Closure::fromCallable($definition);
        }

        $this->definitions->add($id, new DefinitionReuse($definition));
    }

    /**
     * 生成処理の登録。インスタンスを使いまわしさせない。
     *
     * @param string $id
     * @param callable|null $definition
     * @return void
     */
    public function addEach(string $id, ?callable $definition = null): void {
        $definition ??= fn () => $this->resolve($id);

        if (! $definition instanceof Closure) {
            $definition = Closure::fromCallable($definition);
        }

        $this->definitions->add($id, $definition);
    }

    /**
     * インスタンスを使いまわしさせるかどうかのデフォルト設定。
     * デフォルトの設定にかかわらず、addReuseとaddEachは個別に機能する。
     *
     * @param bool $reuse_flag
     * @return void
     */
    public function instanceReuse(bool $reuse_flag): void {
        $this->instance_reuse = $reuse_flag;
    }

    /**
     * callableな値を依存解決したうえで関数実行し、その結果を返す。
     * 足りない引数は$optionsに指定することができる
     *
     * @param callable $callback
     * @param array $options
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function call(callable $callback, array $options = []): mixed {
        try {
            $ref_func = new ReflectionFunction(Closure::fromCallable($callback));
        } catch (ReflectionException) {
            throw new ContainerException;
        }

        $params = $options;

        // コンストラクタの引数から依存性を判断
        foreach ($ref_func->getParameters() as $ref_param) {
            $param_name = $ref_param->getName();
            $params[$param_name] = $options[$param_name] ?? $this->getDependency($ref_param);
        }

        return $callback(...$params);
    }

    /**
     * ReflectionClassを分析し、クラスのインスタンス化に必要な依存性を取り揃える
     *
     * @param string $id
     * @return object
     * @throws ContainerExceptionInterface
     */
    protected function resolve(string $id): object
    {
        // IDがクラス文字列でなければ依存解決エラー
        if (!class_exists($id)) {
            echo $id;
            throw new ContainerException;
        }

        $ref_class = $this->reflections->get($id);

        // クラスがインスタンス化不可なら依存解決エラー
        if (!$ref_class->isInstantiable()) {
            throw new ContainerException;
        }

        $ref_constructor = $ref_class->getConstructor();

        $params = [];

        // コンストラクタの引数から依存性を判断
        if ($ref_constructor instanceof ReflectionMethod) {
            foreach ($ref_constructor->getParameters() as $ref_param) {
                $param_name = $ref_param->getName();
                $params[$param_name] = $this->getDependency($ref_param);
            }
        }

        return new $id(...$params);
    }

    /**
     * ReflectionParameterを分析し、必要な依存性を取得する
     * Inject属性があれば参照、なければ型宣言から判別
     * 型宣言もなければデフォルト値を返す
     * デフォルト値もなければ依存解決エラー
     *
     * @param ReflectionParameter $ref_param
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    protected function getDependency(ReflectionParameter $ref_param): mixed
    {
        // Inject属性があれば参照
        if ($this->hasInjectAttribute($ref_param)) {
            $id = $this->getInjectAttribute($ref_param)->getId();
            return $this->get($id);
        }

        $ref_type = $ref_param->getType();

        try {
            // 引数の型が指定されていれば、IDとして依存性を取得
            if ($ref_type instanceof ReflectionNamedType && !$ref_type->isBuiltin()) {
                $id = $ref_type->getName();
                return $this->get($id);
            }

            $class_name = $ref_param->getDeclaringClass()->getName();
            throw new ContainerException(
                "{$class_name}の依存関係を解決できませんでした。コンストラクタの引数{$ref_param->getName()}の型が指定されていません。引数の型を指定するか、デフォルト値を設定してください。"
            );
        } catch (ContainerException $e) {
            // デフォルト値が設定されていればそれを返す
            if ($ref_param->isDefaultValueAvailable()) {
                return $ref_param->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * 引数がInject属性を持っているかどうか
     *
     * @param ReflectionParameter $ref_param
     * @return bool
     */
    protected function hasInjectAttribute(ReflectionParameter $ref_param): bool
    {
        return isset($ref_param->getAttributes(Inject::class)[0]);
    }

    /**
     * 引数が持っているInject属性のインスタンスを返す
     *
     * @param ReflectionParameter $ref_param
     * @return Inject
     */
    protected function getInjectAttribute(ReflectionParameter $ref_param): Inject
    {
        $ref_attrs = $ref_param->getAttributes(Inject::class);

        if ($ref_attrs === []) {
            throw new LogicException;
        }

        return $ref_attrs[0]->newInstance();
    }
}
