<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * IR node for conditional rendering.
 *
 * Renders a child node only when the condition evaluates to truthy.
 *
 * @group IR/Node
 * @example
 * $node = new ShowNode(
 *     Expr::val(true),
 *     new TextNode('Visible')
 * );
 */
final class ShowNode extends Node implements AtomCollectable{
    /**
     * Build a conditional IR node.
     *
     * @param Expr|bool $cond Condition expression or literal.
     * @param Node $child Node rendered when condition is true.
     */
    public function __construct(public Expr|bool $cond, public Node $child) {}

    /**
     * Collect atom dependencies used by condition and child node.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->cond);
        $collect($this->child);
    }

    /**
     * Encode this conditional node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return [
            'k' => 'show',
            'when' => $this->cond instanceof Expr 
                ? $this->cond 
                : ['k'=>'val','v'=>(bool)$this->cond],
            'child' => $this->child
        ];
    }
}
