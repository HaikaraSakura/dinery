<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class ClassA {
    public function __construct(protected ClassB $class_b) {
    }
}

class ClassB {
    public function __construct(protected ClassCInterface $class_c) {
    }
}

class ClassC implements ClassCInterface{
    public function __construct() {
    }
}

class ClassD {
}

interface ClassCInterface {
}

$container = New \Haikara\DiForklift\Container;

$container->add(ClassCInterface::class, fn () => new ClassC);

$container->add(ClassD::class);
var_dump($container->has(ClassD::class));

var_dump(spl_object_id($container->get(ClassA::class)) === spl_object_id($container->get(ClassA::class)));