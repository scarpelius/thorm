<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

final class ExprEvent extends Expr {
    public function __construct(public string $path) {}
    public function jsonSerialize(): mixed { return ['k'=>'event','path'=>$this->path]; }
}
