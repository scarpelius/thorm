<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;

final class ShowNode extends Node {
    public function __construct(public Expr|bool $cond, public Node $child) {}
    public function jsonSerialize(): mixed {
        return [
            'k' => 'show',
            'when' => $this->cond instanceof Expr ? $this->cond : ['k'=>'val','v'=>(bool)$this->cond],
            'child' => $this->child
        ];
    }
}
