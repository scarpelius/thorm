<?php
declare(strict_types=1);

namespace PhpJs\IR\Action;

use PhpJs\IR\Expr\Expr;

final class AddAction implements Action
{
    public function __construct(
        public readonly int $atomId,
        public readonly int|float|Expr $by
    ) {}

    public function kind(): string { return 'add'; }

    public function jsonSerialize(): array
    {
        $by = $this->by;
        if ($by instanceof \JsonSerializable) $by = $by->jsonSerialize();

        return ['k' => $this->kind(), 'atom' => $this->atomId, 'by' => $by];
    }
}
