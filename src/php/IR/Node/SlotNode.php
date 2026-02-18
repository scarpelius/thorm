<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

/**
 * SlotNode represents a placeholder inside a component template
 * where children content will be injected at runtime.
 *
 * JSON shape:
 * {
 *   "k": "slot",
 *   "name": "default"   // optional; omitted for default slot
 * }
 *
 * @group IR/Node
 * @example
 * $node = new SlotNode('header');
 */
final class SlotNode extends Node
{
    /**
     * Build a slot placeholder node.
     *
     * @param string|null $name Slot name, or null for default slot.
     */
    public function __construct(public readonly ?string $name = null) {}

    /**
     * Return a slot node with normalized name.
     *
     * @param string $name Slot name.
     * @return self
     */
    public function withName(string $name): self {
        $name = trim($name);

        return new self($name === '' ? null : $name);
    }

    /**
     * Encode this slot node as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): mixed
    {
        $out = ['k' => 'slot'];
        $out['name'] = $this->name ? $this->name : 'default';
        
        return $out;
    }
}
