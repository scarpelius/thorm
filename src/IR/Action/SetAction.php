<?php
declare(strict_types=1);

namespace PhpJs\IR\Action;

use PhpJs\IR\Expr\Expr;

final class SetAction implements Action
{
    public function __construct(
        public readonly int $atomId,
        public readonly Expr|int|float|string|bool|null $to
    ) {}

    public function kind(): string { return 'set'; }

    public function jsonSerialize(): array
    {
        $to = $this->to;
        if ($to instanceof \JsonSerializable) $to = $to->jsonSerialize();

        return ['k' => $this->kind(), 'atom' => $this->atomId, 'to' => $to];
    }
}
