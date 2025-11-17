<?php
declare(strict_types=1);

namespace PhpJs\IR\Node;

/**
 * SlotNode represents a placeholder inside a component template
 * where children content will be injected at runtime.
 *
 * JSON shape:
 * {
 *   "k": "slot",
 *   "name": "default"   // optional; omitted for default slot
 * }
 */
final class SlotNode extends Node
{
    public function __construct(public readonly ?string $name = null) {}

    public function withName(string $name): self {
        $name = trim($name);

        return new self($name === '' ? null : $name);
    }

    public function jsonSerialize(): mixed
    {
        $out = ['k' => 'slot'];
        $out['name'] = $this->name ? $this->name : 'default';
        
        return $out;
    }
}
