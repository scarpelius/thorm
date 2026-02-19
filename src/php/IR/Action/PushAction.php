<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

/**
 * Action that pushes a value into an atom collection.
 *
 * @group IR/Action
 * @example
 * $action = new PushAction(1, Expr::val('item'));
 */
final class PushAction implements Action
{
    /**
     * Build a push action.
     *
     * @param int $atomId Atom identifier.
     * @param Expr|int|float|string|bool|array|null $value Value to push.
     */
    public function __construct(
        public readonly int $atomId,
        public readonly Expr|int|float|string|bool|array|null $value
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'push'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $v = $this->value;
        if ($v instanceof \JsonSerializable) $v = $v->jsonSerialize();
        return ['k' => 'push', 'atom' => $this->atomId, 'value' => $v];
    }
}
