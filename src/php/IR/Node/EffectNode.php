<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use InvalidArgumentException;
use Thorm\IR\Effect\EffectTrigger;
use Thorm\IR\Effect\EffectTarget;
use Thorm\IR\Action\Action;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * IR node for declarative effects.
 *
 * Binds one or more triggers to one or more runtime actions, optionally
 * scoped to a specific effect target.
 *
 * @group IR/Node
 * @example
 * $node = new EffectNode(
 *     [$trigger],
 *     [$action]
 * );
 */
final class EffectNode extends Node implements \JsonSerializable, AtomCollectable
{
    /**
     * Build an effect IR node.
     *
     * @param array<int, EffectTrigger> $triggers Effect trigger list.
     * @param array<int, Action> $actions Effect action list.
     * @param EffectTarget|null $target Optional effect target.
     */
    public function __construct(
        public readonly array $triggers,
        public readonly array $actions,
        public readonly ?EffectTarget $target = null
    ) {
        if ($this->triggers === []) {
            throw new InvalidArgumentException('EffectNode: triggers cannot be empty.');
        }
        if ($this->actions === []) {
            throw new InvalidArgumentException('EffectNode: actions cannot be empty.');
        }
        foreach ($this->triggers as $i => $t) {
            if (!$t instanceof EffectTrigger) {
                throw new InvalidArgumentException("EffectNode: trigger at index {$i} must implement EffectTrigger.");
            }
        }
        foreach ($this->actions as $i => $a) {
            if (!$a instanceof Action) {
                throw new InvalidArgumentException("EffectNode: action at index {$i} must implement Action.");
            }
        }
    }

    /**
     * Collect atom dependencies referenced by actions and watch triggers.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        foreach ($this->actions as $a) $collect($a);
        foreach ($this->triggers as $t) {
            if (is_array($t) && ($t['type'] ?? null) === 'watch') {
                $expr = $t['expr'] ?? null;
                if ($expr instanceof Expr) $collect($expr);
            }
        }
    }

    /**
     * Encode this effect node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $out = [
            'k'        => 'effect',
            'triggers' => array_map(fn(EffectTrigger $t) => $t->jsonSerialize(), $this->triggers),
            'actions'  => array_map(fn(Action $a) => $a->jsonSerialize(), $this->actions),
        ];
        if ($this->target !== null) {
            $t = $this->target->jsonSerialize();
            if ($t !== null) $out['target'] = $t; // implicit self when omitted
        }
        return $out;
    }
}
