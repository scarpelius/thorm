<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use JsonSerializable;
use Thorm\IR\Atom;

/**
 * Base IR expression type.
 *
 * Expression nodes model runtime-evaluated values used by nodes, actions,
 * and effects.
 *
 * @group IR/Expr
 * @example
 * $expr = Expr::op('add', Expr::val(1), Expr::val(2));
 */
abstract class Expr implements JsonSerializable 
{
    /** @return mixed[] */
    abstract public function jsonSerialize(): mixed;

    /**
     * Create a literal-value expression.
     *
     * @param mixed $v Literal value.
     * @return self
     */
    public static function val(mixed $v): self
    {
        return new ExprVal($v);
    }

    /**
     * Create an atom-read expression.
     *
     * @param Atom $a Atom definition.
     * @return self
     */
    public static function read(Atom $a): self
    {
        return new ExprRead($a);
    }

    /**
     * Create a binary operation expression.
     *
     * @param string $name Operation name.
     * @param Expr|int|float|string|bool $a Left operand.
     * @param Expr|int|float|string|bool $b Right operand.
     * @return self
     */
    public static function op(string $name, Expr|int|float|string|bool $a, Expr|int|float|string|bool $b): self
    {
        return new ExprOp($name, self::ensure($a), self::ensure($b));
    }

    /**
     * Create a string-concatenation expression.
     *
     * @param Expr|string ...$parts Parts to concatenate.
     * @return self
     */
    public static function concat(Expr|string ...$parts): self
    {
        $ensured = array_map(fn($p) => $p instanceof Expr ? $p : new ExprVal((string)$p), $parts);
        return new ExprConcat($ensured);
    }

    /**
     * Normalize scalar values into expression nodes.
     *
     * @param Expr|int|float|string|bool $x Input expression or scalar.
     * @return Expr
     */
    public static function ensure(Expr|int|float|string|bool $x): Expr
    {
        return $x instanceof Expr ? $x : new ExprVal($x);
    }

    /**
     * Create an event-path expression.
     *
     * @param string $path Event payload path.
     * @return self
     */
    public static function event(string $path): self
    { 
        return new ExprEvent($path);
    }
    
    /**
     * Create a numeric-cast expression.
     *
     * @param Expr $x Input expression.
     * @return self
     */
    public static function num(Expr $x): self
    { 
        return new ExprNumCast($x);
    }
    
    /**
     * Create a string-cast expression.
     *
     * @param Expr $x Input expression.
     * @return self
     */
    public static function str(Expr $x): self
    {
        return new ExprStrCast($x);
    }

    /**
     * Create a list-item expression path accessor.
     *
     * @param string $path Item path.
     * @return self
     */
    public static function item(string $path): self
    {
        return new ExprItem($path);
    }

    /**
     * Create a logical-not expression.
     *
     * @param self $x Input expression.
     * @return self
     */
    public static function not(self $x): self { 
        return new ExprNot($x); 
    }
}
