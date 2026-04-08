<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

/**
 * Action that appends one value or an array of values into an atom collection.
 *
 * @group IR/Action
 */
final class AppendAction implements Action
{
    /**
     * @param int $atomId Atom identifier.
     * @param Expr|int|float|string|bool|array|null $value Value(s) to append.
     */
    public function __construct(
        public readonly int $atomId,
        public readonly Expr|int|float|string|bool|array|null $value
    ) {}

    public function kind(): string
    {
        return 'append';
    }

    public function jsonSerialize(): array
    {
        $v = $this->value;
        if ($v instanceof \JsonSerializable) {
            $v = $v->jsonSerialize();
        }

        return ['k' => 'append', 'atom' => $this->atomId, 'value' => $v];
    }
}

