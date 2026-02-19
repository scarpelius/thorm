<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * Action that hydrates an atom from a persisted source.
 *
 * @group IR/Action
 * @example
 * $action = new HydrateAction($atom, 'local:count', 0);
 */
final class HydrateAction implements Action, AtomCollectable
{
    /**
     * Build a hydrate action.
     *
     * @param Atom $atom Atom to hydrate.
     * @param Expr|string $source Hydration source key/expression.
     * @param mixed $default Default fallback value.
     */
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|string $source,
        public readonly mixed $default = null
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'hydrate'; }

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
        if ($this->default instanceof Expr) $collect($this->default);
    }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
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
