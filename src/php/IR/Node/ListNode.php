<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;
use Thorm\IR\Renderable;

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
final class ListNode extends Node implements AtomCollectable, Renderable
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

    public function render(callable $renderer): string
    {
        $items = $this->evalExpr($this->node['items'] ?? null, $this->ctx);
        $arr = is_array($items) ? $items : [];
        $out = $this->comment('repeat:start');

        $tpl = $this->node['tpl'] ?? ($this->node['child'] ?? null);
        if ($tpl instanceof \JsonSerializable) {
            $tpl = $tpl->jsonSerialize();
        }
        if (!is_array($tpl)) {
            $tpl = is_array($this->node['children'] ?? null) ? ($this->node['children'][0] ?? null) : null;
            if ($tpl instanceof \JsonSerializable) {
                $tpl = $tpl->jsonSerialize();
            }
        }

        foreach ($arr as $index => $item) {
            $rowCtx = $this->ctx;
            $rowCtx['item'] = $item;
            $rowCtx['index'] = $index;
            $keyRaw = $this->evalExpr($this->node['key'] ?? null, $rowCtx);
            $key = $this->stringifyKey($keyRaw);
            $out .= $this->comment('repeat:row:' . $key . ':start');
            if (is_array($tpl)) {
                $out .= $this->renderNodes([$tpl], $rowCtx);
            }
            $out .= $this->comment('repeat:row:' . $key . ':end');
        }

        $out .= $this->comment('repeat:end');
        return $out;
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
