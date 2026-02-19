<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * Capability (runtime) invocation action.
 *
 * JSON (IR):
 *   { k: 'cap', name: 'Date.getFullYear', args?: any, to?: atomId, error?: atomId }
 *
 * @group IR/Action
 * @example
 * $action = new RuntimeAction('Caps.CopyToClipboard', ['hello']);
 */
final class RuntimeAction implements Action, AtomCollectable
{
    /**
     * Build a runtime capability action.
     *
     * @param string $name Capability name.
     * @param mixed $args Capability arguments.
     * @param Atom|int|null $to Target atom for result.
     * @param Atom|int|null $error Target atom for errors.
     */
    public function __construct(
        public readonly string $name,
        /** @var mixed|null scalar|array|Expr (arrays may contain Expr) */
        public readonly mixed $args = null,
        public readonly Atom|int|null $to = null,
        public readonly Atom|int|null $error = null,
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'cap'; }

    /**
     * Collect atom dependencies referenced by this action.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        if ($this->to instanceof Atom) $collect($this->to);
        if ($this->error instanceof Atom) $collect($this->error);

        $walk = function (mixed $v) use (&$walk, $collect): void {
            if ($v instanceof Expr) {
                $collect($v);
                return;
            }
            if (is_array($v)) {
                foreach ($v as $vv) $walk($vv);
            }
        };

        $walk($this->args);
    }

    /**
     * Serialize nested arguments recursively.
     *
     * @param mixed $v Input value.
     * @return mixed
     */
    private static function ser(mixed $v): mixed
    {
        if ($v instanceof \JsonSerializable) return $v->jsonSerialize();
        if (is_array($v)) {
            $out = [];
            foreach ($v as $k => $vv) $out[$k] = self::ser($vv);
            return $out;
        }
        return $v; // scalar|null
    }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $out = [
            'k'    => $this->kind(),
            'name' => $this->name,
        ];

        if ($this->args !== null) $out['args'] = self::ser($this->args);
        if ($this->to !== null) $out['to'] = $this->to instanceof Atom ? $this->to->id : $this->to;
        if ($this->error !== null) $out['error'] = $this->error instanceof Atom ? $this->error->id : $this->error;

        return $out;
    }
}
