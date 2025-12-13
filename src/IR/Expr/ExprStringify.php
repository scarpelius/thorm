<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use JsonSerializable;
use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;

final class ExprStringify extends Expr implements JsonSerializable, AtomCollectable
{
    /** @param Expr|int|float|string|bool|null $value */
    public function __construct(
        public readonly mixed $value,
        public readonly int $space = 2
    ) {}

    public function collectAtoms(callable $collect): void
    {
        $collect($this->value);
    }

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
