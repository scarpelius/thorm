<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Renderable;

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
final class SlotNode extends Node implements Renderable
{
    /**
     * Build a slot placeholder node.
     *
     * @param string|null $name Slot name, or null for default slot.
     */
    public function __construct(public readonly ?string $name = null) {}

    public function render(callable $renderer): string
    {
        $name = $this->node['name'] ?? 'default';
        $slots = $this->ctx['__slots'] ?? [];
        $inner = '';

        if (is_array($slots) && isset($slots[$name]) && is_array($slots[$name])) {
            $inner = $this->renderNodes($slots[$name], $this->ctx);
        } elseif (isset($this->node['children']) && is_array($this->node['children'])) {
            $inner = $this->renderNodes($this->node['children'], $this->ctx);
        }

        return $this->comment('slot:start') . $inner . $this->comment('slot:end');
    }

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
