<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\Atom;

final class ExprRead extends Expr {
    public function __construct(public Atom $a) {}
    public function jsonSerialize(): mixed { return ['k' => 'read', 'atom' => $this->a->id]; }
}
