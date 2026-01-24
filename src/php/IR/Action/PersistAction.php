<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

final class PersistAction implements Action, AtomCollectable
{
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|string $source
    ) {}

    public function kind(): string { return 'persist'; }

    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        if ($this->source instanceof Expr) $collect($this->source);
    }

    public function jsonSerialize(): array
    {
        $src = $this->source instanceof \JsonSerializable ? $this->source->jsonSerialize() : $this->source;
        return [
            'k' => $this->kind(),
            'atom' => $this->atom->id,
            'source' => $src,
        ];
    }
}
