<?php
declare(strict_types=1);

namespace PhpJs\IR\Expr;

final class ExprStrCast extends Expr 
{
    public function __construct(public Expr $x) {}
    public function jsonSerialize(): mixed
    {
        return ['k'=>'str','x'=>$this->x]; 
    }
}
