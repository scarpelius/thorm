<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * Action that persists an atom value to a storage source.
 *
 * @group IR/Action
 * @example
 * $action = new PersistAction($atom, 'local:count');
 */
final class PersistAction implements Action, AtomCollectable
{
    /**
     * Build a persist action.
     *
     * @param Atom $atom Atom to persist.
     * @param Expr|string $source Persistence source key/expression.
     */
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|string $source
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'persist'; }

    /**
     * Collect atom dependencies referenced by this action.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        if ($this->source instanceof Expr) $collect($this->source);
    }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
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
