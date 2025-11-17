<?php
declare(strict_types=1);

namespace PhpJs\IR\Expr;

use InvalidArgumentException;

final class ExprProp extends Expr
{
    public function __construct(public string $name) {
        if (!is_string($name)) {
            throw new InvalidArgumentException('ExprProp: name must be a string');
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'k' => 'prop',
            'name' => $this->name
        ];
    }
}
