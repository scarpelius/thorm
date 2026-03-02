<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;
use Thorm\IR\Renderable;

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
final class ShowNode extends Node implements AtomCollectable, Renderable {
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

    public function render(callable $renderer): string
    {
        $condExpr = $this->node['when'] ?? ($this->node['cond'] ?? null);
        $visible = (bool)$this->evalExpr($condExpr, $this->ctx);
        $child = '';

        if ($visible && isset($this->node['child'])) {
            $childNode = $this->node['child'];
            if ($childNode instanceof \JsonSerializable) {
                $childNode = $childNode->jsonSerialize();
            }
            if (is_array($childNode)) {
                $child = $this->renderNodes([$childNode], $this->ctx);
            }
        }

        return $this->comment('show:start') . $child . $this->comment('show:end');
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
