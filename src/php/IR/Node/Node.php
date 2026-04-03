<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use JsonSerializable;

/**
 * Base IR node type.
 *
 * All IR node variants extend this class and must be JSON serializable
 * so they can be encoded and sent to the runtime.
 *
 * @group IR/Node
 * @example
 * final class MyNode extends Node {
 *     public function jsonSerialize(): mixed {
 *         return ['k' => 'my'];
 *     }
 * }
 */
abstract class Node implements JsonSerializable 
{
    /** @var array<string, mixed> */
    protected array $node = [];

    /** @var array<string, mixed> */
    protected array $ctx = [];

    /** @var array<int, mixed> */
    protected array $atoms = [];

    /** @var callable|null */
    protected $renderNodeFn = null;

    
    /** @var array<string, bool> */
    protected const VOID_TAGS = [
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

    /** @param array<string,mixed> $node @param array<string,mixed> $ctx @param array<int,mixed> $atoms */
    public function setRenderContext(array $node, array $ctx, array $atoms, callable $renderNodeFn): void
    {
        $this->node = $node;
        $this->ctx = $ctx;
        $this->atoms = $atoms;
        $this->renderNodeFn = $renderNodeFn;
    }

    /** @param array<int, mixed> $nodes */
    public function renderNodes(array $nodes, array $ctx): string
    {
        $out = '';
        foreach ($nodes as $n) {
            if ($n instanceof \JsonSerializable) {
                $n = $n->jsonSerialize();
            }
            if (is_array($n)) {
                if (is_callable($this->renderNodeFn)) {
                    $out .= ($this->renderNodeFn)($n, $ctx);
                }
            } elseif (is_string($n)) {
                $out .= $this->escape($n);
            }
        }
        return $out;
    }

    /** @return array{attrs:array<string,mixed>,cls:mixed,style:array<string,mixed>} */
    protected function normalizeProps(mixed $props): array
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

    protected function renderProps(mixed $props, array $ctx): string
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

    protected function evalExpr(mixed $expr, array $ctx): mixed
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

    protected function evalOp(string $name, mixed $a, mixed $b, mixed $c): mixed
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
            case 'sub':
                return (float)$a - (float)$b;
            case 'mul':
                return (float)$a * (float)$b;
            case 'div':
                if ((float)$b == 0.0) return null;
                return (float)$a / (float)$b;
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
            case 'abs':
                return abs((float)$a);
            case 'min':
                if (is_array($a) && $b === null) return min($a);
                return min((float)$a, (float)$b);
            case 'max':
                if (is_array($a) && $b === null) return max($a);
                return max((float)$a, (float)$b);
            case 'round':
                return round((float)$a);
            case 'floor':
                return floor((float)$a);
            case 'ceil':
                return ceil((float)$a);
            case 'sqrt':
                return sqrt((float)$a);
            case 'pow':
                return pow((float)$a, (float)$b);
            case 'trunc':
                return (float)$a < 0 ? ceil((float)$a) : floor((float)$a);
            case 'sign':
                return (float)$a < 0 ? -1 : ((float)$a > 0 ? 1 : 0);
            case 'log':
                return log((float)$a);
            case 'log10':
                return log10((float)$a);
            case 'log2':
                return log((float)$a, 2);
            case 'exp':
                return exp((float)$a);
            case 'strlen':
                return strlen((string)$a);
            case 'substr':
                if ($b === null) return (string)$a;
                return $c === null
                    ? substr((string)$a, (int)$b)
                    : substr((string)$a, (int)$b, (int)$c);
            case 'strpos': {
                $pos = strpos((string)$a, (string)$b);
                return $pos === false ? -1 : $pos;
            }
            case 'str_replace':
                return str_replace((string)$a, (string)$b, (string)$c);
            case 'strtolower':
                return strtolower((string)$a);
            case 'strtoupper':
                return strtoupper((string)$a);
            case 'trim':
                return trim((string)$a);
            case 'ltrim':
                return ltrim((string)$a);
            case 'rtrim':
                return rtrim((string)$a);
            case 'explode':
                return explode((string)$a, (string)$b);
            case 'implode':
                return is_array($b) ? implode((string)$a, $b) : '';
            case 'in_array':
                return is_array($b) ? in_array($a, $b, true) : false;
            case 'count':
                if (is_array($a)) return count($a);
                if (is_object($a)) return count(get_object_vars($a));
                return 0;
            case 'array_keys':
                return is_array($a) ? array_keys($a) : (is_object($a) ? array_keys(get_object_vars($a)) : []);
            case 'array_values':
                return is_array($a) ? array_values($a) : (is_object($a) ? array_values(get_object_vars($a)) : []);
            case 'json_encode':
                return json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            case 'parseInt':
                return (int)$a;
            case 'parseFloat':
                return (float)$a;
            case 'intval':
                return (int)$a;
            case 'floatval':
                return (float)$a;
            case 'boolval':
                return (bool)$a;
            case 'strval':
                return (string)$a;
            case 'is_numeric':
                return is_numeric($a);
            case 'is_string':
                return is_string($a);
            case 'is_array':
                return is_array($a);
            case 'num':
                return (float)$a;
            default:
                return null;
        }
    }

    protected function escape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function comment(string $label): string
    {
        return '<!--' . $label . '-->';
    }

    protected function stringifyKey(mixed $key): string
    {
        if (is_scalar($key) || $key === null) {
            $s = (string)($key ?? '');
        } else {
            $s = json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        }
        return str_replace('--', '- -', $s);
    }

    protected function getByPath(mixed $value, string $path): mixed
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
