<?php
namespace Thorm\IR\Expr;

final class ExprNot extends Expr implements \JsonSerializable {
    public function __construct(public Expr $x) {}
    
    public function jsonSerialize(): mixed { 
        return ['k' => 'not', 'x' => $this->x]; 
    }
}
