<?php

declare(strict_types=1);

namespace Haikara\DiForklift\Exceptions;

use LogicException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends LogicException implements NotFoundExceptionInterface
{

}