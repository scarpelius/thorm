<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

final class ExprVal extends Expr {
    public function __construct(public mixed $v) {}
    public function jsonSerialize(): mixed { return ['k' => 'val', 'v' => $this->v]; }
}
