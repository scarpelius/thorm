<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

final class SetAction implements Action, AtomCollectable
{
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|int|float|string|bool|null $to
    ) {}

    public function kind(): string { return 'set'; }

    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        if($this->to instanceof Expr) $collect($this->to);
    }

    public function jsonSerialize(): array
    {
        $to = $this->to;
        if ($to instanceof \JsonSerializable) $to = $to->jsonSerialize();

        return ['k' => $this->kind(), 'atom' => $this->atom->id, 'to' => $to];
    }
}
