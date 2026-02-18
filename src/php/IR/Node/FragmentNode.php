<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use InvalidArgumentException;
use JsonSerializable;
use Thorm\IR\AtomCollectable;

/**
 * IR node for grouping children without creating a wrapper element.
 *
 * Fragment nodes are transparent at the DOM level and only serve as a
 * structural container in the IR tree.
 *
 * @group IR/Node
 * @example
 * $node = new FragmentNode([
 *     new TextNode('A'),
 *     new TextNode('B'),
 * ]);
 */
final class FragmentNode extends Node implements JsonSerializable, AtomCollectable
{
    /**
     * Build a fragment IR node.
     *
     * @param array<int, Node> $children Child nodes rendered in sequence.
     */
    public function __construct(public array $children)
    {
        foreach ($children as $child) {
            if (!($child instanceof Node)) {
                throw new InvalidArgumentException(
                    'FragmentNode children must be Node instance, got ' . get_debug_type($child)
                );
            }
        }
    }

    /**
     * Collect atom dependencies from child nodes.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        foreach ($this->children as $c) {
            $collect($c);
        }
    }

    /**
     * Encode this fragment node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return [
            'k' => 'fragment',
            'children' => $this->children,
        ];
    }
}
