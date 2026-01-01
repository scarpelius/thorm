<?php
namespace Thorm\IR\Expr;

final class ExprItem extends Expr {
    public function __construct(public string $path) {}
    public function jsonSerialize(): mixed {
        return ['k' => 'item', 'path' => $this->path];
    }
}
