<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * IR node for list/repeat rendering.
 *
 * Repeats a template node for each item in an items expression with a key
 * expression used for stable identity.
 *
 * @group IR/Node
 * @example
 * $node = new ListNode(
 *     Expr::read($items),
 *     Expr::val('id'),
 *     new TextNode('Item')
 * );
 */
final class ListNode extends Node implements AtomCollectable
{
    /**
     * Build a list/repeat IR node.
     *
     * @param Expr $items Items expression.
     * @param Expr $key Key expression for each item.
     * @param Node $template Template node rendered per item.
     */
    public function __construct(
        public Expr $items,
        public Expr $key,
        public Node $template
    ) {}

    /**
     * Collect atom dependencies used by items, key, and template.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->items);
        $collect($this->key);
        $collect($this->template);
    }

    /**
     * Encode this list node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed
    {
        return [
            'k' => 'repeat',
            'items' => $this->items,
            'key' => $this->key,
            'tpl' => $this->template,
        ];
    }
}
