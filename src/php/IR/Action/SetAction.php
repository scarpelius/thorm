<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * Action that sets an atom to a value or expression result.
 *
 * @group IR/Action
 * @example
 * $action = new SetAction($atom, Expr::val('done'));
 */
final class SetAction implements Action, AtomCollectable
{
    /**
     * Build a set action.
     *
     * @param Atom $atom Target atom.
     * @param Expr|int|float|string|bool|null $to Target value.
     */
    public function __construct(
        public readonly Atom $atom,
        public readonly Expr|int|float|string|bool|null $to
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'set'; }

    /**
     * Collect atom dependencies referenced by this action.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        if($this->to instanceof Expr) $collect($this->to);
    }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $to = $this->to;
        if ($to instanceof \JsonSerializable) $to = $to->jsonSerialize();

        return ['k' => $this->kind(), 'atom' => $this->atom->id, 'to' => $to];
    }
}
