<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use InvalidArgumentException;

/**
 * Task action: compose a list of actions sequentially.
 *
 * @param Action[] $actions
 */
final class TaskAction implements Action
{
    /** @param Action[] $actions */
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

    public function kind(): string { return 'task'; }

    public function jsonSerialize(): array
    {
        return [
            'k' => $this->kind(),
            'actions' => $this->actions,
        ];
    }
}
