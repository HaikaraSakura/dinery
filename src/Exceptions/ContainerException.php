<?php

declare(strict_types=1);

namespace Haikara\DiForklift\Exceptions;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends LogicException implements ContainerExceptionInterface
{
}