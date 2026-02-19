<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

/**
 * Action that increments an atom by a numeric delta.
 *
 * @group IR/Action
 * @example
 * $action = new IncAction(1, 1);
 */
final class IncAction implements Action
{
    /**
     * Build an increment action.
     *
     * @param int $atomId Atom identifier.
     * @param int|float $by Increment delta.
     */
    public function __construct(
        public readonly int $atomId,
        public readonly int|float $by = 1
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'inc'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, int|float|string>
     */
    public function jsonSerialize(): array
    {
        return ['k' => $this->kind(), 'atom' => $this->atomId, 'by' => $this->by];
    }
}
