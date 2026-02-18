<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;

/**
 * IR expression for reading atom values.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprRead($countAtom);
 */
final class ExprRead extends Expr implements AtomCollectable {
    /**
     * Build an atom-read expression.
     *
     * @param Atom $a Atom definition.
     */
    public function __construct(public Atom $a) {}

    /**
     * Collect atom dependencies for this read expression.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect):void
    {
        $collect($this->a);
    }

    /**
     * Encode this read expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed 
    { 
        return ['k' => 'read', 'atom' => $this->a->id]; 
    }
}
