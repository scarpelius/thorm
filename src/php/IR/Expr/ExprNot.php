<?php
namespace Thorm\IR\Expr;

/**
 * IR expression for logical negation.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprNot(Expr::val(true));
 */
final class ExprNot extends Expr implements \JsonSerializable {
    /**
     * Build a logical-not expression.
     *
     * @param Expr $x Input expression.
     */
    public function __construct(public Expr $x) {}
    
    /**
     * Encode this not expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed { 
        return ['k' => 'not', 'x' => $this->x]; 
    }
}
