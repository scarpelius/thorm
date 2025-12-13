<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;

final class HtmlNode extends Node {
    public function __construct(public Expr|string $value) {}
    public function jsonSerialize(): mixed {
        return ['k' => 'html', 'value' => $this->value instanceof Expr ? $this->value : ['k'=>'val','v'=>(string)$this->value]];
    }
}
