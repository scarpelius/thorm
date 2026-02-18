<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * IR expression for string casting.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprStrCast(Expr::val(42));
 */
final class ExprStrCast extends Expr 
{
    /**
     * Build a string-cast expression.
     *
     * @param Expr $x Input expression.
     */
    public function __construct(public Expr $x) {}

    /**
     * Encode this string-cast expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return ['k'=>'str','x'=>$this->x]; 
    }
}
