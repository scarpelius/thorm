<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\AtomCollectable;

final class ExprConcat extends Expr implements AtomCollectable
{
    /** @param Expr[] $parts */
    public function __construct(public array $parts) {}

    public function collectAtoms(callable $collect): void
    {
        foreach ($this->parts as $p) $collect($p);
    }

    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'concat', 'parts' => $this->parts]; 
    }
}