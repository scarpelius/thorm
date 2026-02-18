<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\AtomCollectable;

/**
 * IR expression for literal values.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprVal('hello');
 */
final class ExprVal  extends Expr implements AtomCollectable {
    /**
     * Build a literal-value expression.
     *
     * @param mixed $v Literal value.
     */
    public function __construct(public mixed $v) {}

    /**
     * Literal values have no atom dependencies.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        // do nothing
    }

    /**
     * Encode this literal expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'val', 'v' => $this->v]; 
    }
}
