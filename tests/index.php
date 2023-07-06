<?php

declare(strict_types=1);

use Haikara\Dinery\Attributes\Inject;
use Haikara\Dinery\Container;

require_once __DIR__ . '/../vendor/autoload.php';

class ClassA {
    public function __construct(
        protected ClassB $class_b,
        protected $text = 'a'
    ) {
    }
}

class ClassB {
    public function __construct(
        protected ClassCInterface $class_c,
        protected ClassD $class_d
    ) {
    }
}

class ClassC implements ClassCInterface{
    public function __construct() {
    }
}

class ClassD {
    public function __construct(
        public DateTimeImmutable $date_1,
        public DateTimeImmutable $date_2,
    ) {
    }
}

interface ClassCInterface {

}

<<<<<<< Updated upstream
$container = New Container;
$container->instanceReuse(true);
//
$container->add(ClassCInterface::class, fn () => new ClassC);
=======
$container = new Container;
$container->instanceReuse(true);

// $container->add(ClassCInterface::class, fn () => new ClassC);
>>>>>>> Stashed changes
// $container->add('b', fn () => 'b');
//
// $container->add(ClassD::class);
// var_dump($container->has(ClassD::class));
//
// var_dump($container->get(ClassA::class));
//
// var_dump($container->get(ClassA::class) === $container->get(ClassA::class));
//
$container->add(DateTimeImmutable::class);
//
// print_r($container->get(DateTimeImmutable::class));
// sleep(1);
// print_r($container->get(DateTimeImmutable::class));

$class_d = $container->get(ClassD::class);

var_dump(spl_object_id($class_d->date_1) === spl_object_id($class_d->date_2));
var_dump($class_d->date_1);
var_dump($class_d->date_2);


$container->call(function (ClassA $class_a, array $args) {
    print_r($class_a);
    print_r($args);
}, ['args' => ['year' => 2023, 'month' => 1]]);
