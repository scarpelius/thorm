<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use Thorm\IR\AtomCollectable;

/**
 * IR expression for generic operations.
 *
 * Stores operation name and up to three operands.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprOp('add', Expr::val(1), Expr::val(2));
 */
final class ExprOp extends Expr implements AtomCollectable
{
    public string $name;
    public ?Expr $a = null; // operation name
    public ?Expr $b = null; // operand
    public ?Expr $c = null; // operand

    /** @var Expr[] */
    public array $args = []; // keep if others still use it

    /**
     * Build an operation expression.
     *
     * @param string $name Operation name.
     * @param Expr ...$args Operation operands.
     */
    public function __construct(string $name, ...$args)
    {
        $this->name = $name;
        $this->args = $args;
        if (isset($args[0])) $this->a = $args[0];
        if (isset($args[1])) $this->b = $args[1];
        if (isset($args[2])) $this->c = $args[2];
    }

    /**
     * Collect atom dependencies from all operands.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->a);
        $collect($this->b);
        $collect($this->c);
    }

    /**
     * Encode this operation expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        //if ($this->a === 'get') {
        //    return ['k' => 'get', 'a' => $this->a, 'b' => $this->b];
        //}
        $out = ['k' => 'op', 'name' => $this->name];
        // Emit a/b/c for simplicity
        if ($this->a !== null) $out['a'] = $this->a;
        if ($this->b !== null) $out['b'] = $this->b;
        if ($this->c !== null) $out['c'] = $this->c;
        return $out;
    }
}
