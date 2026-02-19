<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

/**
 * Action that increments an atom by an expression or scalar value.
 *
 * @group IR/Action
 * @example
 * $action = new AddAction(1, Expr::val(2));
 */
final class AddAction implements Action
{
    /**
     * Build an add action.
     *
     * @param int $atomId Atom identifier.
     * @param int|float|Expr $by Value to add.
     */
    public function __construct(
        public readonly int $atomId,
        public readonly int|float|Expr $by
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'add'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $by = $this->by;
        if ($by instanceof \JsonSerializable) $by = $by->jsonSerialize();

        return ['k' => $this->kind(), 'atom' => $this->atomId, 'by' => $by];
    }
}
