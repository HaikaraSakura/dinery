<?php

declare(strict_types=1);

namespace Haikara\Dinery\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct(protected string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
