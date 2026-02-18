<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * IR expression for numeric casting.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprNumCast(Expr::val('42'));
 */
final class ExprNumCast extends Expr {
    /**
     * Build a numeric-cast expression.
     *
     * @param Expr $x Input expression.
     */
    public function __construct(public Expr $x) {}

    /**
     * Encode this numeric-cast expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed { return ['k'=>'num','x'=>$this->x]; }
}
