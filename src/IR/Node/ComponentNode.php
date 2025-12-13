<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use InvalidArgumentException;
use Thorm\IR\Expr\Expr;

/**
 * ComponentNode represents a reusable IR subtree instantiated with its own
 * set of props and (optionally) slot children.
 *
 * JSON shape:
 * {
 *   "k": "component",
 *   "template": { ... Node ... },
 *   "props": { "propName": Expr, ... },
 *   "slots": [ ... Node ... ]
 *   "key": [ ... Expr ... ]
 * }
 */
final class ComponentNode extends Node
{
    /** @param Node[] $children */
    public function __construct(
        public readonly Node $template,
        /** @var array<string, Expr> */
        public readonly array $props = [],
        public readonly array $slots = [],
        public readonly ?Expr $key = null,
    ) {
        // Sanity checks
        foreach ($this->props as $key => $value) {
            if ($key === '' || !is_string($key)) {
                throw new InvalidArgumentException('ComponentNode: prop keys must be non-empty strings');
            }
            if (!($value instanceof Expr)) {
                throw new InvalidArgumentException("ComponentNode: prop '$key' must be Expr");
            }
        }

        foreach ($this->slots as $name => $nodes) {
            if (!is_string($name) || $name === '') {
                throw new \InvalidArgumentException('ComponentNode: slot names must be non-empty strings');
            }
            if (!is_array($nodes)) {
                throw new \InvalidArgumentException("ComponentNode: slot '$name' must be an array of Node");
            }
            foreach ($nodes as $n) {
                if (!($n instanceof Node)) {
                    throw new \InvalidArgumentException("ComponentNode: slot '$name' contains non-Node");
                }
            }
        }
    }

    public function jsonSerialize(): mixed
    {
        $out = [
            'k' => 'component',
            'tpl' => $this->template
        ];
        if ($this->props)   { $out['props'] = $this->props; }
        if ($this->slots)   { $out['slots'] = $this->slots; }
        if ($this->key)     { $out['key'] = $this->key; }

        return $out;
    }

    public function withProp(string $name, Expr $expr): self {
        $clone = clone $this;
        $clone->props[$name] = $expr;
        return $clone;
    }

    public function withSlots(Node ...$slots): self {
        $clone = clone $this;
        $clone->slots = $slots;
        return $clone;
    }

}
