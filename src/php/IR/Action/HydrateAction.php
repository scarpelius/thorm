<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

final class HydrateAction implements Action, AtomCollectable
{
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|string $source,
        public readonly mixed $default = null
    ) {}

    public function kind(): string { return 'hydrate'; }

    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        if ($this->source instanceof Expr) $collect($this->source);
        if ($this->default instanceof Expr) $collect($this->default);
    }

    public function jsonSerialize(): array
    {
        $src = $this->source instanceof \JsonSerializable ? $this->source->jsonSerialize() : $this->source;
        $def = $this->default instanceof \JsonSerializable ? $this->default->jsonSerialize() : $this->default;

        $out = [
            'k' => $this->kind(),
            'atom' => $this->atom->id,
            'source' => $src,
        ];
        if ($this->default !== null) {
            $out['default'] = $def;
        }
        return $out;
    }
}
