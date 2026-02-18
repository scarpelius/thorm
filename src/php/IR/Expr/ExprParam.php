<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * IR expression for route path parameters.
 *
 * Reads values from the current route parameter map.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprParam('id');
 */
final class ExprParam extends Expr implements \JsonSerializable
{
    /**
     * Build a route-param expression.
     *
     * @param string $name Parameter name.
     */
    public function __construct(public string $name) {}

    /**
     * Encode this route-param expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed
    {
        return ['k' => 'param', 'name' => $this->name];
    }
}
