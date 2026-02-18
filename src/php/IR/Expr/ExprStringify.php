<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use JsonSerializable;
use Thorm\IR\AtomCollectable;

/**
 * IR expression for JSON stringification.
 *
 * @group IR/Expr
 * @example
 * $expr = new ExprStringify(Expr::val(['a' => 1]), 2);
 */
final class ExprStringify extends Expr implements JsonSerializable, AtomCollectable
{
    /**
     * Build a stringify expression.
     *
     * @param Expr|int|float|string|bool|null $value Value to stringify.
     * @param int $space Indentation spaces.
     */
    public function __construct(
        public readonly mixed $value,
        public readonly int $space = 2
    ) {}

    /**
     * Collect atom dependencies from stringify input.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->value);
    }

    /**
     * Encode this stringify expression as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'k'     => 'stringify',
            'value' => $this->value instanceof JsonSerializable 
                ? $this->value->jsonSerialize() 
                : $this->value,
            'space' => $this->space,
        ];
    }
}
