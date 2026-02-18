<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;

/**
 * IR node for text content.
 *
 * Stores either a literal string or an expression resolved at runtime.
 *
 * @group IR/Node
 * @example
 * $node = new TextNode('Hello');
 */
final class TextNode extends Node implements AtomCollectable {
    /**
     * Build a text node.
     *
     * @param Expr|string $value Text value or expression.
     */
    public function __construct(public Expr|string $value) {}

    /**
     * Collect atom dependencies from text expression values.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect):void
    {
        if($this->value instanceof Expr) {
            $collect($this->value);
        }
    }

    /**
     * Encode this text node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return [
            'k' => 'text', 
            'value' => $this->value instanceof Expr 
                ? $this->value 
                : ['k'=>'val','v'=>(string)$this->value]
        ];
    }
}
