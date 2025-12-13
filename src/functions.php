<?php
declare(strict_types=1);

namespace PhpJs;

use PhpJs\IR\Action\{Listener, Action, IncAction, AddAction,
    DelayAction,
    SetAction, HttpAction, NavigateAction,
    RedirectAction
};
use PhpJs\IR\Atom as AtomDef;

use PhpJs\IR\Expr\{Expr, ExprOp, ExprParam, ExprQuery, ExprProp, ExprStringify};

use PhpJs\IR\Node\{Node, ElNode, TextNode, HtmlNode, ShowNode, ListNode, 
    FragmentNode, LinkNode, RouteNode, SlotNode, ComponentNode,
    EffectNode
};

use PhpJs\IR\Effect\{EffectTrigger,EffectTarget,MountTrigger,WatchTrigger,
    IntervalTrigger,TimeoutTrigger,VisibleTrigger,EventTrigger,
    WindowTarget,DocumentTarget,SelectorTarget
};

/**
 * DSL entry points (functions) for building the IR in PHP.
 * These are autoloaded via composer "files".
 */

function state(mixed $initial): AtomDef {
    return new AtomDef($initial);
}

function read(AtomDef $atom): Expr {
    return Expr::read($atom);
}

/**
 * Read a property or index from an object/array expression.
 *
 * Examples:
 *   text(get(read($out), 'message'))      // object prop
 *   text(get(read($list), 0))             // array index
 *
 * @param Expr|mixed $obj  Object/array expression (often read($atom))
 * @param Expr|int|string $key Property name or numeric index (literal or Expr)
 * @return Expr
 */
function get($obj, $key): Expr {
    $objExpr = $obj instanceof Expr ? $obj : Expr::val($obj);
    $keyExpr = $key instanceof Expr ? $key : Expr::val($key);
    return new ExprOp('get', $objExpr, $keyExpr); // uses [a, b] slots
}

function val(mixed $v): Expr {
    return Expr::val($v);
}

function mod(Expr|int|float $a, Expr|int|float $b): Expr {
    return Expr::op('mod', $a, $b);
}

function eq(Expr|int|float|string|bool $a, Expr|int|float|string|bool $b): Expr {
    return Expr::op('eq', $a, $b);
}

function concat(Expr|string ...$parts): Expr {
    return Expr::concat(...$parts);
}

// Node builders
/**
 * @param array<string, mixed> $props
 * @param array<int, Node> $children
 */
function el(string $tag, array $props = [], array $children = []): Node {
    // Props keys: 'attrs' => [name => string|Expr], 'cls' => string|Expr, 'style' => [name => string],
    // 'on' => [ [event, Listener] , ...]
    return new ElNode($tag, $props, $children);
}

function text(Expr|string $value): Node {
    return new TextNode($value);
}

function html(Expr|string $value): Node {
    return new HtmlNode($value);
}

function show(Expr|bool $cond, Node $child): Node {
    return new ShowNode($cond, $child);
}

// Event helpers
function on(string $event, Listener $action): array {
    return ['on', $event, $action];
}

/** Increase atom by a non-zero value (int/float). */
function inc(AtomDef $atom, int|float $by = 1, bool $asAction = false): Listener|Action {
    if($asAction)
        return new IncAction($atom->id, $by);
    else
        return Listener::inc($atom, $by);
}

function set(AtomDef $atom, Expr|int|float|string|bool $to, bool $asAction = false): Listener|Action {
    if($asAction)
        return new SetAction($atom->id, $to);
    else
        return Listener::set($atom, $to);
}

function add(AtomDef $atom, Expr|int|float $by, bool $asAction = false): Expr|Action {
    if($asAction)
        return new AddAction($atom->id, $by);
    else
        return Listener::add($atom, $by);
}

function not(Expr|int|float|string|bool|null $x): Expr {
    return Expr::not(Expr::ensure($x));
}

/** Pretty JSON string of any value/expr. Default 2 spaces. */
function stringify(Expr|int|float|string|bool|null $value, int $space = 2): Expr {
    return new ExprStringify($value, $space);
}

function http(
    Expr|string $url,
    string $method = 'GET',
    ?AtomDef $to = null,
    ?AtomDef $status = null,
    array|AtomDef|null $headers = null,
    Expr|int|float|string|bool|array|null $body = null,
    string $parse = 'json',
    bool $asAction = false,
    ?AtomDef $resHeaders = null
): Listener|Action {
    $reqHeaders = is_array($headers) ? $headers : null;
    $resHdrAtom = $resHeaders?->id ?? ($headers instanceof AtomDef ? $headers->id : null);

    if ($asAction) {
        return new HttpAction(
            $url,
            $method,
            $to?->id,
            $status?->id,
            $reqHeaders,
            $resHdrAtom,
            $body,
            $parse
        );
    }
    return Listener::http([
        'url' => $url, 
        'method' => $method, 
        'to' => $to, 
        'body' => $body,
        'parse' => $parse,
        'status' => $status, 
        'headers' => $reqHeaders
    ]);
}

function navigate(Expr|string $to, bool $asAction = false): Listener|Action
{
    if ($asAction) {
        return new NavigateAction($to);
    }
    return Listener::navigate($to);
}

function redirect (Expr|string $to, bool $replace = false): Action {
    return new RedirectAction($to, $replace);
}

/** delay an action by given mili-secons */
function delay(int $ms, array $actions): Action {
    return new DelayAction($ms, $actions);
}

// Props helpers
/**
 * @param array<string, Expr|string|int|float|bool> $map
 */
function attrs(array $map): array {
    return ['attrs', $map];
}

function cls(Expr|string $value): array {
    $expr = $value instanceof Expr ? $value : Expr::val($value);
    return ['cls', $expr];
}

/**
 * @param array<string, string|int|float|bool> $map
 */
function style(array|Expr $value): array {
    return ['style', $value];
}

function ev(string $path): Expr 
{
    return Expr::event($path); 
}

function num(Expr|int|float|string|bool $x): Expr 
{ 
    return Expr::num(Expr::ensure($x)); 
}

function str(Expr|int|float|string|bool $x): Expr 
{
    return Expr::str(Expr::ensure($x));
}

// if you added add() as an action earlier:
function addTo(\PhpJs\IR\Atom $atom, Expr|int|float $by): Expr 
{
    return Listener::add($atom, $by instanceof Expr ? $by : Expr::val($by));
}

/**
 * repeat($itemsExpr, $keyExpr, Node $template)
 *
 * $itemsExpr: Expr that evaluates to array
 * $keyExpr:   Expr (often item('id')) evaluated per item to a stable key (string|int)
 * $template:  Node template for ONE item (may contain item(...) and normal exprs)
 */
function repeat(Expr $itemsExpr, Expr $keyExpr, Node $template): ListNode {
    return new ListNode($itemsExpr, $keyExpr, $template);
}

// Reads from the current list item during render.
function item(string $path): Expr {
    return Expr::item($path);
}

// if->then->else ternary condition 
function cond(Expr $if, Expr|string $then, Expr|string $else): Expr {
    $thenExpr = $then instanceof Expr ? $then : Expr::val($then);
    $elseExpr = $else instanceof Expr ? $else : Expr::val($else);
    
    return new ExprOp('cond', $if, $thenExpr, $elseExpr);
}

/**
 * Two-way binding helper for form elements.
 *
 * `bind()` connects a state atom to a form input’s value, generating both
 * the initial attribute and the event listener required to keep them in sync.
 * It expands internally to a combination of `attrs(...)`, `on(...)`, and `set(...)`
 * primitives, so it introduces no new runtime concepts.
 *
 * Example:
 * ```php
 * $name = state('Alice');
 *
 * el('input', [
 *   attrs(['type' => 'text']),
 *   ...bind($name)
 * ]);
 * ```
 *
 * Supported input types:
 * - **text** (default) — bound via `value` and `input` events
 * - **number** — coerces input to numeric with `num(ev('target.value'))`
 * - **checkbox** — bound via `checked` and `change` events
 *
 * Optional options:
 * - `'type' => 'text'|'number'|'checkbox'` — determines binding mode.
 * - `'debounceMs' => int` — milliseconds to debounce `input` events.
 *
 * Returned value:
 * - An **array of attributes and event handlers** suitable to spread into `el()`:
 *   ```php
 *   el('input', [
 *     ...bind($amount, ['type' => 'number'])
 *   ])
 *   ```
 * 
 * * Debounce explanation: //
 * --------------------- //
 * Normally, every keystroke fires an `input` event that instantly updates //
 * the bound atom. When `'debounceMs'` is set, the update is delayed until //
 * the user stops typing for that many milliseconds. This reduces excessive //
 * re-renders or network calls (for example, when live-searching). //
 * //
 * Example: //
 * ```php
 * el('input', [ //
 *   ...bind($query, ['debounceMs' => 300]) //
 * ]); //
 * ``` //
 * In this case, `$query` updates only 300 ms after the last keypress. //
 * //
 * Implementation note: current runtime ignores `debounceMs` for simplicity, //
 * but the option is documented for forward compatibility. //
 *
 * Implementation notes:
 * - This is syntactic sugar: it simply emits `[attrs(...), on(..., set(...))]`.
 * - Works with any reactive atom created via `state()`.
 * - Does not require runtime changes — handled entirely by existing `attrs` and `on`.
 *
 * @param \PhpJs\IR\Atom $a     Reactive atom representing input state.
 * @param array                 $opts Optional binding configuration (type, debounceMs).
 * @return array                Attribute/event definitions to spread into `el()`.
 */

function bind(AtomDef $a, array $opts = []): array {
    $type = $opts['type'] ?? 'text';
    $debounceMs = (int)($opts['debounceMs'] ?? 0);

    if ($type === 'checkbox') {
        return [
            attrs(['checked' => read($a)]),
            on('change', set($a, ev('target.checked')))
        ];
    }

    // default: text/number/etc.
    $valueExpr = read($a);
    $incoming  = ($type === 'number') ? num(ev('target.value')) : str(ev('target.value'));

    if ($debounceMs > 0) {
        // optional: simple debounce wrapper (can be added later);
        // for now, keep it immediate:
        return [
            attrs(['value' => $valueExpr]),
            on('input', set($a, $incoming))
        ];
    }

    return [
        attrs(['value' => $valueExpr]),
        on('input', set($a, $incoming))
    ];
}

/**
 * param
 *
 * Create an expression that reads a path parameter from the current route
 * (e.g., for '/auction/:id', param('id') → '42').
 *
 * @param  string $name Route parameter name.
 * @return Expr         Expression node to be evaluated in the route context.
 */
function param(string $name): Expr { 
    return new ExprParam($name); 
}

/**
 * query
 *
 * Create an expression that reads a query string parameter from the current URL
 * (e.g., for '?q=chair', query('q') → 'chair').
 *
 * @param  string $name Query parameter name.
 * @return Expr         Expression node to be evaluated in the route context.
 */
function query(string $name): Expr { 
    return new ExprQuery($name); 
}

/**
 * link
 *
 * Declarative navigation link that renders an <a> and intercepts in-app clicks.
 * - Computes href from $to (Expr|string).
 * - Accepts el-like $props and $children as link content.
 *
 * @param  Expr|string     $to        Target URL.
 * @param  array<int,mixed> $props     Props: ['attrs', map], ['cls', expr], ['on', event, action].
 * @param  array<int,Node> $children  Child nodes (text or elements inside the link).
 * @return Node                        Link node for the IR tree.
 */
function link(Expr|string $to, array $props = [], array $children = []): Node {
    $expr = $to instanceof Expr ? $to : Expr::val($to);
    return new LinkNode($expr, $props, $children);
}

/**
 * route
 *
 * Top-level router node that mounts the first matching view for the current path.
 * - $table preserves author order (priority).
 * - $fallback renders when no route matches.
 * - $base (optional) is stripped before matching (default '/').
 *
 * @param  array<string,Node> $table    Map of path pattern → view Node.
 * @param  Node               $fallback  Fallback view (404).
 * @param  string             $base      Optional base path (default '/').
 * @return Node                          Route node for the IR tree.
 */
function route(array $table, Node $fallback, string $base = '/'): Node {
    return new RouteNode($table, $fallback, $base);
}

/**
 * 
 */
function fragment(array $children): Node {
    return new FragmentNode($children);
}

function prop(string $name): Expr {
    return new ExprProp($name);
}

function slot(?string $name = null): Node { 
    return new SlotNode($name); 
}

/**
 * @param array<string, Expr> $props
 * @param array<int, Node>|array<string, Node[]> $childrenOrSlots
 */
function component(Node $tpl, array $props = [], array $childrenOrSlots = []): Node
{
    $slots = [];

    // if associative → named slots
    $isAssoc = static function(array $a): bool {
        foreach (array_keys($a) as $k) if (!is_int($k)) return true;
        return false;
    };

    if ($childrenOrSlots === []) {
        // no slots provided
    } elseif ($isAssoc($childrenOrSlots)) {
        // named
        foreach ($childrenOrSlots as $name => $nodes) {
            $slots[$name] = $nodes;
        }
    } else {
        // unnamed children → default
        /** @var Node[] $childrenOrSlots */
        $slots['default'] = $childrenOrSlots;
    }

    return new ComponentNode($tpl, $props, $slots);
}


/** Events and Actions */
function effect(array $triggers, array $actions, ?EffectTarget $target = null): EffectNode {
    return new EffectNode($triggers, $actions, $target);
}

function onMount(array $actions, ?EffectTarget $target = null): EffectNode {
    return effect([new MountTrigger()], $actions, $target);
}

function watch(
    Expr $expr, 
    array $actions, 
    bool $immediate=false, 
    int|Expr|null $debounceMs=null, 
    int|Expr|null $throttleMs=null, 
    ?EffectTarget $target = null
): EffectNode {
    return effect([new WatchTrigger($expr, $immediate, $debounceMs, $throttleMs)], $actions, $target);
}

function every(int $ms, array $actions, ?EffectTarget $target = null): EffectNode {
    return effect([new IntervalTrigger($ms)], $actions, $target);
}

function after(int $ms, array $actions, ?EffectTarget $target = null): EffectNode {
    return effect([new TimeoutTrigger($ms)], $actions, $target);
}

function onVisible(array $actions, float $threshold=0.0, ?string $rootMargin=null, ?EffectTarget $target = null): EffectNode {
    return effect([new VisibleTrigger($threshold, $rootMargin)], $actions, $target);
}

function onLeftViewport(
    array $actions, 
    float $threshold=0.0, 
    ?string $rootMargin=null, 
    ?EffectTarget $target = null
): EffectNode  {
    return effect([new VisibleTrigger($threshold, $rootMargin, 'exit')], $actions, $target);
}

function onWindow(string $event, array $actions, ?array $options=null): EffectNode {
    return effect([new EventTrigger('window',$event,$options)], $actions, null);
}

function onDocument(string $event, array $actions, ?array $options=null): EffectNode {
    return effect([new EventTrigger('document',$event,$options)], $actions, null);
}

function onSelf(string $event, array $actions, ?array $options=null, ?EffectTarget $target = null): EffectNode {
    return effect([new EventTrigger('self',$event,$options)], $actions, $target);
}

/* target creators */
function windowTarget(): EffectTarget   { return new WindowTarget(); }
function documentTarget(): EffectTarget { return new DocumentTarget(); }
function selectorTarget(string $sel): EffectTarget { return new SelectorTarget($sel); }
