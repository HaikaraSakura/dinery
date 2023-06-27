<?php

declare(strict_types=1);

namespace Haikara\DiForklift\Attributes;

use Attribute;

#[Attribute]
class Inject
{
    protected string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}