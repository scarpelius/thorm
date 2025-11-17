<?php 
namespace PhpJs\IR\Node;

use PhpJs\IR\Expr\Expr;

/**
 * LinkNode
 *
 * Declarative client-side navigation link (renders an <a>).
 * - Computes href from an Expr|string.
 * - Intercepts same-origin clicks (no modifiers) → pushState + reroute.
 * - Passes through props like attrs/cls/on and nests children as content.
 */
final class ListNode extends Node {
    public function __construct(
        public Expr $items,
        public Expr $key,
        public Node $template
    ) {}

    public function jsonSerialize(): mixed {
        return [
            'k'    => 'repeat',
            'items'=> $this->items,
            'key'  => $this->key,
            'tpl'  => $this->template,
        ];
    }
}
