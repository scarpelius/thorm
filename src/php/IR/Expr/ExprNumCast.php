<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

final class ExprNumCast extends Expr {
    public function __construct(public Expr $x) {}
    public function jsonSerialize(): mixed { return ['k'=>'num','x'=>$this->x]; }
}