<?php
declare(strict_types=1);

namespace PhpJs\IR\Action;

final class IncAction implements Action
{
    public function __construct(
        public readonly int $atomId,
        public readonly int|float $by = 1
    ) {}

    public function kind(): string { return 'inc'; }

    public function jsonSerialize(): array
    {
        return ['k' => $this->kind(), 'atom' => $this->atomId, 'by' => $this->by];
    }
}
