<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;

final class ExprRead extends Expr implements AtomCollectable {
    public function __construct(public Atom $a) {}

    public function collectAtoms(callable $collect):void
    {
        $collect($this->a);
    }

    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'read', 'atom' => $this->a->id]; 
    }
}
