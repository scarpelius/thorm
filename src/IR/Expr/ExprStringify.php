<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

use JsonSerializable;
use Thorm\IR\Atom;

final class ExprStringify extends Expr implements JsonSerializable
{
    /** @param Expr|int|float|string|bool|null $value */
    public function __construct(
        public readonly mixed $value,
        public readonly int $space = 2
    ) {}

    public function jsonSerialize(): array
    {
        $val = $this->value instanceof JsonSerializable ? $this->value->jsonSerialize() : $this->value;
        return [
            'k'     => 'stringify',
            'value' => $val,
            'space' => $this->space,
        ];
    }
}
