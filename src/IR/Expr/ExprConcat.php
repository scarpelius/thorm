<?php
declare(strict_types=1);

namespace PhpJs\IR\Expr;

final class ExprConcat extends Expr {
    /** @param Expr[] $parts */
    public function __construct(public array $parts) {}
    public function jsonSerialize(): mixed { return ['k' => 'concat', 'parts' => $this->parts]; }
}