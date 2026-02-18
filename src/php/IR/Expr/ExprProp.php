<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use InvalidArgumentException;

/**
 * IR expression for reading component prop values.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprProp('title');
 */
final class ExprProp extends Expr
{
    /**
     * Build a prop expression.
     *
     * @param string $name Prop name.
     */
    public function __construct(public string $name) {
        if (!is_string($name)) {
            throw new InvalidArgumentException('ExprProp: name must be a string');
        }
    }

    /**
     * Encode this prop expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed
    {
        return [
            'k' => 'prop',
            'name' => $this->name
        ];
    }
}
