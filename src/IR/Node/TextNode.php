<?php
declare(strict_types=1);

namespace PhpJs\IR\Node;

use PhpJs\IR\Expr\Expr;

final class TextNode extends Node {
    public function __construct(public Expr|string $value) {}
    public function jsonSerialize(): mixed {
        return ['k' => 'text', 'value' => $this->value instanceof Expr ? $this->value : ['k'=>'val','v'=>(string)$this->value]];
    }
}
