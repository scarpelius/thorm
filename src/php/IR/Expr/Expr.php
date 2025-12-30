<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use JsonSerializable;
use Thorm\IR\Atom;

abstract class Expr implements JsonSerializable 
{
    /** @return mixed[] */
    abstract public function jsonSerialize(): mixed;

    public static function val(mixed $v): self
    {
        return new ExprVal($v);
    }
    public static function read(Atom $a): self
    {
        return new ExprRead($a);
    }
    public static function op(string $name, Expr|int|float|string|bool $a, Expr|int|float|string|bool $b): self
    {
        return new ExprOp($name, self::ensure($a), self::ensure($b));
    }
    public static function concat(Expr|string ...$parts): self
    {
        $ensured = array_map(fn($p) => $p instanceof Expr ? $p : new ExprVal((string)$p), $parts);
        return new ExprConcat($ensured);
    }

    /** @param Expr|int|float|string|bool $x */
    public static function ensure(Expr|int|float|string|bool $x): Expr
    {
        return $x instanceof Expr ? $x : new ExprVal($x);
    }

    public static function event(string $path): self
    { 
        return new ExprEvent($path);
    }
    
    public static function num(Expr $x): self
    { 
        return new ExprNumCast($x);
    }
    
    public static function str(Expr $x): self
    {
        return new ExprStrCast($x);
    }

    public static function item(string $path): self
    {
        return new ExprItem($path);
    }

    public static function not(self $x): self { 
        return new ExprNot($x); 
    }
}
