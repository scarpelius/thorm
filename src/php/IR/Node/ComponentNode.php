<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use InvalidArgumentException;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * IR node for mounting a component template with props and slots.
 *
 * The template node is reused as the component body, while props and slots
 * are passed as runtime inputs for each component instance.
 *
 * @group IR/Node
 * @example
 * $node = new ComponentNode(
 *     $cardTemplate,
 *     ['title' => Expr::val('Docs')],
 *     ['default' => [new TextNode('Body')]]
 * );
 */
final class ComponentNode extends Node implements AtomCollectable
{
    /**
     * Build a component IR node.
     *
     * @param Node $template Component template node.
     * @param array<string, Expr> $props Component prop expressions.
     * @param array<string, array<int, Node>> $slots Slot content keyed by slot name.
     * @param Expr|null $key Optional stable key expression.
     */
    public function __construct(
        public readonly Node $template,
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

    /**
     * Collect atom-related dependencies used by template, props, slots, and key.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->template);
        foreach ($this->props as $expr) $collect($expr);
        foreach ($this->slots as $nodes) foreach ($nodes as $n) $collect($n);
        if ($this->key) $collect($this->key);
    }

    /**
     * Encode this component node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
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

    /**
     * Return a cloned component node with one prop updated.
     *
     * @param string $name Prop name.
     * @param Expr $expr Prop expression.
     * @return self
     */
    public function withProp(string $name, Expr $expr): self {
        $clone = clone $this;
        $clone->props[$name] = $expr;
        return $clone;
    }

    /**
     * Return a cloned component node with slot children replaced.
     *
     * @param Node ...$slots Slot node list.
     * @return self
     */
    public function withSlots(Node ...$slots): self {
        $clone = clone $this;
        $clone->slots = $slots;
        return $clone;
    }

}
