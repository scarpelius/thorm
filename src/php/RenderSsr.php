<?php
declare(strict_types=1);

namespace Thorm;

use Thorm\IR\Node\Node;

final class RenderSsr
{
    private Renderer $renderer;
    /** @var array<int, mixed> */
    private array $atoms = [];

    /** @var array<string, bool> */
    private const VOID_TAGS = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    public function __construct(?Renderer $renderer = null)
    {
        $this->renderer = $renderer ?? new Renderer();
    }

    /** @return array{html:string,state:array{atoms:array<int,mixed>},ir:array} */
    public function render(Node $root, array $opts = []): array
    {
        $ir = $this->renderer->toIR($root);
        $this->atoms = $this->buildAtoms($ir['atoms'] ?? [], $opts['atoms'] ?? null);
        $ctx = $opts['ctx'] ?? [];
        $html = $this->renderNode($ir['root'], $ctx);
        return ['html' => $html, 'state' => ['atoms' => $this->atoms], 'ir' => $ir];
    }

    public function renderHtml(Node $root, array $opts = []): string
    {
        return $this->render($root, $opts)['html'];
    }

    /** @return array{html:string,state:array{atoms:array<int,mixed>},ir:array} */
    public function renderIr(array $ir, array $opts = []): array
    {
        $this->atoms = $this->buildAtoms($ir['atoms'] ?? [], $opts['atoms'] ?? null);
        $ctx = $opts['ctx'] ?? [];
        $html = $this->renderNode($ir['root'], $ctx);
        return ['html' => $html, 'state' => ['atoms' => $this->atoms], 'ir' => $ir];
    }

    /** @param array<int, mixed> $irAtoms */
    private function buildAtoms(array $irAtoms, mixed $override): array
    {
        $out = [];
        foreach ($irAtoms as $a) {
            if (is_array($a) && array_key_exists('id', $a)) {
                $out[(int)$a['id']] = $a['initial'] ?? null;
            }
        }

        if (is_array($override)) {
            $isList = $override === [] || array_keys($override) === range(0, count($override) - 1);
            if ($isList) {
                foreach ($override as $item) {
                    if (is_array($item) && array_key_exists('id', $item)) {
                        $out[(int)$item['id']] = $item['value'] ?? ($item['initial'] ?? null);
                    }
                }
            } else {
                foreach ($override as $id => $value) {
                    $out[(int)$id] = $value;
                }
            }
        }

        return $out;
    }

    /** @param array<int, mixed> $nodes */
    private function renderNodes(array $nodes, array $ctx): string
    {
        $out = '';
        foreach ($nodes as $n) {
            if ($n instanceof \JsonSerializable) {
                $n = $n->jsonSerialize();
            }
            if (is_array($n)) {
                $out .= $this->renderNode($n, $ctx);
            }
        }
        return $out;
    }

    private function renderNode(array $node, array $ctx): string
    {
        $kind = $node['k'] ?? '';
        switch ($kind) {
            case 'text': {
                $v = $this->evalExpr($node['value'] ?? null, $ctx);
                return $this->escape((string)($v ?? ''));
            }
            case 'el': {
                $tag = (string)($node['tag'] ?? 'div');
                $props = $node['props'] ?? [];
                $attrs = $this->renderProps($props, $ctx);
                $children = $this->renderNodes($node['children'] ?? [], $ctx);
                $lower = strtolower($tag);
                if (isset(self::VOID_TAGS[$lower])) {
                    return '<' . $tag . $attrs . '>';
                }
                return '<' . $tag . $attrs . '>' . $children . '</' . $tag . '>';
            }
            case 'fragment': {
                $children = $this->renderNodes($node['children'] ?? [], $ctx);
                return $this->comment('fragment:start') . $children . $this->comment('fragment:end');
            }
            case 'show': {
                $condExpr = $node['when'] ?? ($node['cond'] ?? null);
                $visible = (bool)$this->evalExpr($condExpr, $ctx);
                $child = '';
                if ($visible && isset($node['child']) && is_array($node['child'])) {
                    $child = $this->renderNode($node['child'], $ctx);
                }
                return $this->comment('show:start') . $child . $this->comment('show:end');
            }
            case 'repeat': {
                $items = $this->evalExpr($node['items'] ?? null, $ctx);
                $arr = is_array($items) ? $items : [];
                $out = $this->comment('repeat:start');
                $tpl = $node['tpl'] ?? ($node['child'] ?? null);
                if (!is_array($tpl)) {
                    $tpl = is_array($node['children'] ?? null) ? ($node['children'][0] ?? null) : null;
                }
                foreach ($arr as $index => $item) {
                    $rowCtx = $ctx;
                    $rowCtx['item'] = $item;
                    $rowCtx['index'] = $index;
                    $keyRaw = $this->evalExpr($node['key'] ?? null, $rowCtx);
                    $key = $this->stringifyKey($keyRaw);
                    $out .= $this->comment('repeat:row:' . $key . ':start');
                    if (is_array($tpl)) {
                        $out .= $this->renderNode($tpl, $rowCtx);
                    }
                    $out .= $this->comment('repeat:row:' . $key . ':end');
                }
                $out .= $this->comment('repeat:end');
                return $out;
            }
            case 'html': {
                $v = $this->evalExpr($node['value'] ?? null, $ctx);
                $markup = $v == null ? '' : (string)$v;
                return $this->comment('html:start') . $markup . $this->comment('html:end');
            }
            case 'link': {
                $props = $node['props'] ?? [];
                $attrs = $this->renderProps($props, $ctx);
                $href = $this->evalExpr($node['to'] ?? null, $ctx);
                $hrefVal = $this->escape((string)($href ?? ''));
                $attrs .= ' href="' . $hrefVal . '"';
                $children = $this->renderNodes($node['children'] ?? [], $ctx);
                return '<a' . $attrs . '>' . $children . '</a>';
            }
            case 'route': {
                $child = '';
                if (isset($node['fallback']) && is_array($node['fallback'])) {
                    $child = $this->renderNode($node['fallback'], $ctx);
                }
                return $this->comment('route:start') . $child . $this->comment('route:end');
            }
            case 'component': {
                $propsExpr = is_array($node['props'] ?? null) ? $node['props'] : [];
                $propsVal = [];
                foreach ($propsExpr as $name => $expr) {
                    $propsVal[(string)$name] = $this->evalExpr($expr, $ctx);
                }
                $childCtx = $ctx;
                $childCtx['props'] = $propsVal;
                $childCtx['__propsExpr'] = $propsExpr;
                $childCtx['__slots'] = is_array($node['slots'] ?? null) ? $node['slots'] : [];
                $tpl = $node['tpl'] ?? null;
                $inner = is_array($tpl) ? $this->renderNode($tpl, $childCtx) : '';
                return $this->comment('component:start') . $inner . $this->comment('component:end');
            }
            case 'slot': {
                $name = $node['name'] ?? 'default';
                $slots = $ctx['__slots'] ?? [];
                $inner = '';
                if (is_array($slots) && isset($slots[$name]) && is_array($slots[$name])) {
                    $inner = $this->renderNodes($slots[$name], $ctx);
                } elseif (isset($node['children']) && is_array($node['children'])) {
                    $inner = $this->renderNodes($node['children'], $ctx);
                }
                return $this->comment('slot:start') . $inner . $this->comment('slot:end');
            }
            case 'effect':
                return '';
            default:
                return '';
        }
    }

    /** @return array{attrs:array<string,mixed>,cls:mixed,style:array<string,mixed>} */
    private function normalizeProps(mixed $props): array
    {
        $out = ['attrs' => [], 'cls' => null, 'style' => []];
        if (!is_array($props)) return $out;

        if (array_key_exists('attrs', $props) || array_key_exists('cls', $props) || array_key_exists('style', $props)) {
            if (isset($props['attrs']) && is_array($props['attrs'])) {
                foreach ($props['attrs'] as $name => $val) $out['attrs'][(string)$name] = $val;
            }
            if (array_key_exists('cls', $props)) $out['cls'] = $props['cls'];
            if (isset($props['style']) && is_array($props['style'])) {
                foreach ($props['style'] as $name => $val) $out['style'][(string)$name] = $val;
            }
            return $out;
        }

        foreach ($props as $p) {
            if (!is_array($p)) continue;
            $kind = $p[0] ?? null;
            if ($kind === 'attrs' && isset($p[1]) && is_array($p[1])) {
                foreach ($p[1] as $name => $val) $out['attrs'][(string)$name] = $val;
            } elseif ($kind === 'cls') {
                $out['cls'] = $p[1] ?? null;
            } elseif ($kind === 'style' && isset($p[1]) && is_array($p[1])) {
                foreach ($p[1] as $name => $val) $out['style'][(string)$name] = $val;
            }
        }

        return $out;
    }

    private function renderProps(mixed $props, array $ctx): string
    {
        $norm = $this->normalizeProps($props);
        $attrs = [];

        if ($norm['cls'] !== null) {
            $cls = $this->evalExpr($norm['cls'], $ctx);
            $clsStr = trim((string)($cls ?? ''));
            if ($clsStr !== '') $attrs['class'] = $clsStr;
        }

        if (!empty($norm['style'])) {
            $pairs = [];
            foreach ($norm['style'] as $name => $valExpr) {
                $val = $this->evalExpr($valExpr, $ctx);
                if ($val === null || $val === false) continue;
                $pairs[] = $name . ':' . (string)$val;
            }
            if ($pairs) $attrs['style'] = implode(';', $pairs);
        }

        foreach ($norm['attrs'] as $name => $valExpr) {
            $val = $this->evalExpr($valExpr, $ctx);
            if ($val === null || $val === false) continue;
            $attrs[(string)$name] = $val;
        }

        $out = '';
        foreach ($attrs as $name => $val) {
            if ($val === true) {
                $out .= ' ' . $name;
                continue;
            }
            $out .= ' ' . $name . '="' . $this->escape((string)$val) . '"';
        }

        return $out;
    }

    private function evalExpr(mixed $expr, array $ctx): mixed
    {
        if ($expr instanceof \JsonSerializable) {
            $expr = $expr->jsonSerialize();
        }
        if (!is_array($expr) || !isset($expr['k'])) return $expr;
        switch ($expr['k']) {
            case 'val':
                return $expr['v'] ?? null;
            case 'read': {
                $id = (int)($expr['atom'] ?? 0);
                return $this->atoms[$id] ?? null;
            }
            case 'concat': {
                $parts = $expr['parts'] ?? [];
                $out = '';
                foreach ($parts as $p) {
                    $v = $this->evalExpr($p, $ctx);
                    $out .= $v == null ? '' : (string)$v;
                }
                return $out;
            }
            case 'prop': {
                $name = $expr['name'] ?? null;
                if (is_string($name) && isset($ctx['__propsExpr']) && is_array($ctx['__propsExpr']) && array_key_exists($name, $ctx['__propsExpr'])) {
                    return $this->evalExpr($ctx['__propsExpr'][$name], $ctx);
                }
                if (is_string($name) && isset($ctx['props']) && is_array($ctx['props']) && array_key_exists($name, $ctx['props'])) {
                    return $ctx['props'][$name];
                }
                return null;
            }
            case 'item': {
                $item = $ctx['item'] ?? null;
                $path = is_string($expr['path'] ?? null) ? $expr['path'] : '';
                return $this->getByPath($item, $path);
            }
            case 'param': {
                $name = $expr['name'] ?? null;
                if (!is_string($name)) return null;
                return $ctx['route']['params'][$name] ?? null;
            }
            case 'query': {
                $name = $expr['name'] ?? null;
                if (!is_string($name)) return null;
                return $ctx['route']['query'][$name] ?? null;
            }
            case 'num': {
                $v = $this->evalExpr($expr['x'] ?? null, $ctx);
                return (float)$v;
            }
            case 'str': {
                $v = $this->evalExpr($expr['x'] ?? null, $ctx);
                return (string)$v;
            }
            case 'not': {
                $v = $this->evalExpr($expr['x'] ?? null, $ctx);
                return !$v;
            }
            case 'event':
                return null;
            case 'stringify': {
                $v = $this->evalExpr($expr['value'] ?? null, $ctx);
                return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            case 'op': {
                $name = $expr['name'] ?? '';
                $a = $this->evalExpr($expr['a'] ?? null, $ctx);
                $b = $this->evalExpr($expr['b'] ?? null, $ctx);
                $c = $this->evalExpr($expr['c'] ?? null, $ctx);
                return $this->evalOp($name, $a, $b, $c);
            }
            default:
                return null;
        }
    }

    private function evalOp(string $name, mixed $a, mixed $b, mixed $c): mixed
    {
        switch ($name) {
            case 'get': {
                if (is_array($a) && $b !== null && array_key_exists((string)$b, $a)) return $a[(string)$b];
                if (is_array($a) && is_int($b) && array_key_exists($b, $a)) return $a[$b];
                if (is_object($a) && $b !== null && isset($a->{$b})) return $a->{$b};
                return null;
            }
            case 'cond':
                return $a ? $b : $c;
            case 'add':
                return (float)$a + (float)$b;
            case 'eq':
                return $a == $b;
            case 'mod':
                return ((int)$a) % ((int)$b);
            case 'gt':
                return (float)$a > (float)$b;
            case 'lt':
                return (float)$a < (float)$b;
            case 'gte':
                return (float)$a >= (float)$b;
            case 'lte':
                return (float)$a <= (float)$b;
            case 'num':
                return (float)$a;
            default:
                return null;
        }
    }

    private function escape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function comment(string $label): string
    {
        return '<!--' . $label . '-->';
    }

    private function stringifyKey(mixed $key): string
    {
        if (is_scalar($key) || $key === null) {
            $s = (string)($key ?? '');
        } else {
            $s = json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        }
        return str_replace('--', '- -', $s);
    }

    private function getByPath(mixed $value, string $path): mixed
    {
        if ($path === '') return $value;
        $parts = explode('.', $path);
        $cur = $value;
        foreach ($parts as $part) {
            if ($part === '') continue;
            if (is_array($cur)) {
                if (array_key_exists($part, $cur)) {
                    $cur = $cur[$part];
                    continue;
                }
                if (ctype_digit($part)) {
                    $idx = (int)$part;
                    if (array_key_exists($idx, $cur)) {
                        $cur = $cur[$idx];
                        continue;
                    }
                }
                return null;
            }
            if (is_object($cur) && isset($cur->{$part})) {
                $cur = $cur->{$part};
                continue;
            }
            return null;
        }
        return $cur;
    }
}
