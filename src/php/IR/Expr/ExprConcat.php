<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\AtomCollectable;

/**
 * IR expression for string concatenation.
 *
 * Evaluates all parts in order and concatenates the resulting values.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprConcat([Expr::val('Hello '), Expr::val('world')]);
 */
final class ExprConcat extends Expr implements AtomCollectable
{
    /**
     * Build a concat expression.
     *
     * @param array<int, Expr> $parts Expression parts.
     */
    public function __construct(public array $parts) {}

    /**
     * Collect atom dependencies from concat parts.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        foreach ($this->parts as $p) $collect($p);
    }

    /**
     * Encode this concat expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'concat', 'parts' => $this->parts]; 
    }
}
