<?php

declare(strict_types=1);

namespace Haikara\Dinery;

use Closure;

class DefinitionReuse
{
    public function __construct(protected Closure $definition) {

    }

    public function get(): Closure {
        return $this->definition;
    }
}
