<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * IR expression for URL query parameters.
 *
 * Reads values from the current route query map.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprQuery('q');
 */
final class ExprQuery extends Expr implements \JsonSerializable
{
    /**
     * Build a query-param expression.
     *
     * @param string $name Query key.
     */
    public function __construct(public string $name) {}

    /**
     * Encode this query-param expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed
    {
        return ['k' => 'query', 'name' => $this->name];
    }
}
