<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

final class TextNode extends Node implements AtomCollectable {
    public function __construct(public Expr|string $value) {}

    public function collectAtoms(callable $collect):void
    {
        if($this->value instanceof Expr) {
            $collect($this->value);
        }
    }

    public function jsonSerialize(): mixed {
        return [
            'k' => 'text', 
            'value' => $this->value instanceof Expr 
                ? $this->value 
                : ['k'=>'val','v'=>(string)$this->value]
        ];
    }
}
