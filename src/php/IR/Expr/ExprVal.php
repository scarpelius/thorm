<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\AtomCollectable;

final class ExprVal  extends Expr implements AtomCollectable {
    public function __construct(public mixed $v) {}

    public function collectAtoms(callable $collect): void
    {
        // do nothing
    }

    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'val', 'v' => $this->v]; 
    }
}
