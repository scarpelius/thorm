<?php
declare(strict_types=1);

namespace PhpJs\IR\Node;

use InvalidArgumentException;
use JsonSerializable;

/**
 * A FragmentNode is an IR (intermediate representation) node that represents
 * a **logical container** for multiple child nodes **without producing
 * an actual DOM element wrapper** in the output. Instead, its children
 * are rendered in sequence in the parent DOM position.
 *
 * Usage:
 *   fragment([$child1, $child2, …])
 *
 * IR shape (jsonSerialize):
 *   {
 *     "k": "fragment",
 *     "children": [ … child IR nodes … ]
 *   }
 *
 * Behaviors:
 * - Fragments are transparent in the DOM: they do not create their own node.
 * - At runtime, they are mounted via comment anchors (start / end markers).
 * - On updates, the runtime diff algorithm patches their children in place.
 * - On unmount, the runtime disposes of all children and cleans up markers.
 */
final class FragmentNode extends Node implements JsonSerializable
{
    /** @param Node[] $children */
    public function __construct(public array $children) 
    {
        foreach($children as $child) {
            if( !($child instanceof Node) ) {
                throw new InvalidArgumentException(
                    "FragmentNode children must be Node instance, got "  . get_debug_type($child)
                );
            }
        }
    }

    public function jsonSerialize(): mixed 
    {
        return [
            'k'         => 'fragment',
            'children'  => $this->children,
        ];
    }
}