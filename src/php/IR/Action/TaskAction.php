<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use InvalidArgumentException;

/**
 * Task action: compose a list of actions sequentially.
 *
 * @group IR/Action
 * @example
 * $action = new TaskAction([new IncAction(1, 1), new DelayAction(200, [new IncAction(1, 1)])]);
 */
final class TaskAction implements Action
{
    /**
     * Build a task action.
     *
     * @param array<int, Action> $actions Action list executed in order.
     */
    public function __construct(
        public readonly array $actions
    ) {
        foreach ($this->actions as $a) {
            if (!$a instanceof Action) {
                $type = is_object($a) ? get_class($a) : gettype($a);
                throw new InvalidArgumentException("TaskAction expects Action items, got {$type}.");
            }
        }
    }

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'task'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'k' => $this->kind(),
            'actions' => $this->actions,
        ];
    }
}
