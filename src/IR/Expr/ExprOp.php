<?php
declare(strict_types=1);

namespace PhpJs\IR\Expr;

final class ExprOp extends Expr {
    public string $name;
    public ?Expr $a = null; // operation name
    public ?Expr $b = null; // operand
    public ?Expr $c = null; // operand

    /** @var Expr[] */
    public array $args = []; // keep if others still use it

    public function __construct(string $name, ...$args)
    {
        $this->name = $name;
        $this->args = $args;
        if (isset($args[0])) $this->a = $args[0];
        if (isset($args[1])) $this->b = $args[1];
        if (isset($args[2])) $this->c = $args[2];
    }

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
