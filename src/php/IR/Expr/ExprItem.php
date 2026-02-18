<?php
namespace Thorm\IR\Expr;

/**
 * IR expression for reading values from repeat-item context.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprItem('id');
 */
final class ExprItem extends Expr {
    /**
     * Build an item expression.
     *
     * @param string $path Item context path.
     */
    public function __construct(public string $path) {}

    /**
     * Encode this item expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed {
        return ['k' => 'item', 'path' => $this->path];
    }
}
