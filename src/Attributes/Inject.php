<?php

declare(strict_types=1);

namespace Haikara\DiForklift\Attributes;

use Attribute;

#[Attribute]
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