<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * IR expression for reading values from event payloads.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprEvent('target.value');
 */
final class ExprEvent extends Expr {
    /**
     * Build an event expression.
     *
     * @param string $path Event payload path.
     */
    public function __construct(public string $path) {}

    /**
     * Encode this event expression as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed { return ['k'=>'event','path'=>$this->path]; }
}
