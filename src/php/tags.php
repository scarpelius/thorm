<?php
declare(strict_types=1);

namespace Thorm;

use Thorm\IR\Node\{Node, ElNode};

/**
 * HTML tag helpers (element nodes).
 * Common attrs (allowed on all tags): id, class, title, lang, dir, tabindex, accesskey, draggable, hidden, spellcheck, contenteditable, translate, role, style, slot, autocapitalize, enterkeyhint, inputmode, inert, is, part, exportparts, nonce.
 * Also allowed on all tags: data-*, aria-*.
 * Note: html(), link(), and style() already exist in functions.php,
 * so use htmlTag(), linkTag(), and styleTag() for those tags.
 */

/** @return string[] */
function _tag_common_attrs(): array {
    return ['id', 'class', 'title', 'lang', 'dir', 'tabindex', 'accesskey', 'draggable', 'hidden', 'spellcheck', 'contenteditable', 'translate', 'role', 'style', 'slot', 'autocapitalize', 'enterkeyhint', 'inputmode', 'inert', 'is', 'part', 'exportparts', 'nonce'];
}

/** @return string[] */
function _tag_voids(): array {
    return ['br', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source'];
}

/** @return array<string, array<string, mixed>> */
function _tag_rules(): array {
    static $rules = null;
    if ($rules !== null) return $rules;
    $rules = [
        'html' => ['attrs' => ['lang', 'dir', 'manifest'], 'children' => ['head', 'body']],
        'head' => ['attrs' => [], 'children' => ['title', 'meta', 'link', 'style', 'script', 'base', 'noscript']],
        'title' => ['attrs' => []],
        'body' => ['attrs' => []],
        'div' => ['attrs' => []],
        'span' => ['attrs' => []],
        'p' => ['attrs' => []],
        'h1' => ['attrs' => []],
        'h2' => ['attrs' => []],
        'h3' => ['attrs' => []],
        'h4' => ['attrs' => []],
        'h5' => ['attrs' => []],
        'h6' => ['attrs' => []],
        'a' => ['attrs' => ['href', 'target', 'rel', 'download', 'hreflang', 'type', 'referrerpolicy', 'ping']],
        'img' => ['attrs' => ['src', 'alt', 'width', 'height', 'loading', 'decoding', 'srcset', 'sizes', 'referrerpolicy', 'fetchpriority', 'crossorigin', 'usemap', 'ismap']],
        'ul' => ['attrs' => [], 'children' => ['li']],
        'ol' => ['attrs' => [], 'children' => ['li']],
        'li' => ['attrs' => ['value']],
        'table' => ['attrs' => ['border', 'cellpadding', 'cellspacing', 'summary', 'width'], 'children' => ['caption', 'colgroup', 'thead', 'tbody', 'tfoot', 'tr']],
        'thead' => ['attrs' => [], 'children' => ['tr']],
        'tbody' => ['attrs' => [], 'children' => ['tr']],
        'tfoot' => ['attrs' => [], 'children' => ['tr']],
        'tr' => ['attrs' => [], 'children' => ['th', 'td']],
        'td' => ['attrs' => ['colspan', 'rowspan', 'headers']],
        'th' => ['attrs' => ['scope', 'colspan', 'rowspan', 'headers', 'abbr']],
        'form' => ['attrs' => ['action', 'method', 'enctype', 'novalidate', 'target', 'autocomplete', 'name']],
        'input' => ['attrs' => ['type', 'name', 'value', 'placeholder', 'checked', 'disabled', 'readonly', 'required', 'min', 'max', 'step', 'minlength', 'maxlength', 'pattern', 'multiple', 'size', 'autocomplete', 'autofocus', 'list', 'form', 'inputmode', 'accept', 'capture']],
        'button' => ['attrs' => ['type', 'name', 'value', 'disabled', 'autofocus', 'form', 'formaction', 'formenctype', 'formmethod', 'formnovalidate', 'formtarget']],
        'label' => ['attrs' => ['for', 'form']],
        'textarea' => ['attrs' => ['name', 'rows', 'cols', 'placeholder', 'disabled', 'readonly', 'required', 'minlength', 'maxlength', 'wrap', 'autocomplete', 'autofocus', 'form']],
        'select' => ['attrs' => ['name', 'multiple', 'size', 'disabled', 'required', 'autofocus', 'form'], 'children' => ['option', 'optgroup']],
        'option' => ['attrs' => ['value', 'selected', 'disabled', 'label']],
        'script' => ['attrs' => ['src', 'type', 'async', 'defer', 'crossorigin', 'integrity', 'nomodule', 'referrerpolicy']],
        'link' => ['attrs' => ['rel', 'href', 'as', 'type', 'media', 'crossorigin', 'integrity', 'referrerpolicy', 'sizes', 'imagesrcset', 'imagesizes', 'disabled', 'fetchpriority', 'title']],
        'meta' => ['attrs' => ['charset', 'name', 'content', 'http-equiv', 'property']],
        'style' => ['attrs' => ['type', 'media', 'nonce']],
        'header' => ['attrs' => []],
        'footer' => ['attrs' => []],
        'nav' => ['attrs' => []],
        'main' => ['attrs' => []],
        'section' => ['attrs' => []],
        'article' => ['attrs' => []],
        'aside' => ['attrs' => []],
        'figure' => ['attrs' => []],
        'figcaption' => ['attrs' => []],
        'strong' => ['attrs' => []],
        'em' => ['attrs' => []],
        'b' => ['attrs' => []],
        'i' => ['attrs' => []],
        'small' => ['attrs' => []],
        'br' => ['attrs' => []],
        'hr' => ['attrs' => []],
        'pre' => ['attrs' => []],
        'code' => ['attrs' => []],
        'blockquote' => ['attrs' => ['cite']],
        'dl' => ['attrs' => [], 'children' => ['dt', 'dd']],
        'dt' => ['attrs' => []],
        'dd' => ['attrs' => []],
        'video' => ['attrs' => ['src', 'controls', 'autoplay', 'loop', 'muted', 'playsinline', 'poster', 'preload', 'width', 'height', 'crossorigin', 'controlslist', 'disablepictureinpicture', 'disableremoteplayback'], 'children' => ['source', 'track']],
        'audio' => ['attrs' => ['src', 'controls', 'autoplay', 'loop', 'muted', 'preload', 'crossorigin', 'controlslist'], 'children' => ['source', 'track']],
        'source' => ['attrs' => ['src', 'type', 'media', 'srcset', 'sizes']],
        'canvas' => ['attrs' => ['width', 'height']],
        'svg' => ['attrs' => [], 'attrs_any' => true],
        'picture' => ['attrs' => [], 'children' => ['source', 'img']],
        'details' => ['attrs' => ['open']],
        'summary' => ['attrs' => []],
        'dialog' => ['attrs' => ['open']],
        'fieldset' => ['attrs' => ['disabled', 'form', 'name']],
        'legend' => ['attrs' => []],
        'optgroup' => ['attrs' => ['label', 'disabled'], 'children' => ['option']],
        'datalist' => ['attrs' => [], 'children' => ['option']],
        'output' => ['attrs' => ['for', 'form', 'name']],
        'progress' => ['attrs' => ['value', 'max']],
        'meter' => ['attrs' => ['value', 'min', 'max', 'low', 'high', 'optimum']],
        'time' => ['attrs' => ['datetime']],
        'mark' => ['attrs' => []],
        'abbr' => ['attrs' => ['title']],
        'cite' => ['attrs' => []],
        'kbd' => ['attrs' => []],
        'samp' => ['attrs' => []],
        'var' => ['attrs' => []],
        'sup' => ['attrs' => []],
        'sub' => ['attrs' => []],
        'iframe' => ['attrs' => ['src', 'title', 'name', 'width', 'height', 'allow', 'allowfullscreen', 'loading', 'referrerpolicy', 'sandbox', 'srcdoc']],
        'embed' => ['attrs' => ['src', 'type', 'width', 'height']],
        'object' => ['attrs' => ['data', 'type', 'name', 'usemap', 'width', 'height', 'form']],
        'param' => ['attrs' => ['name', 'value']],
        'noscript' => ['attrs' => []],
        'template' => ['attrs' => []],
        'address' => ['attrs' => []],
    ];
    return $rules;
}

/** @param array<int, mixed> $props @return array<string, mixed> */
function _tag_extract_attrs(array $props): array {
    $attrs = [];
    foreach ($props as $item) {
        if (is_array($item) && isset($item[0]) && $item[0] === 'attrs') {
            $map = $item[1] ?? [];
            if (is_array($map)) $attrs = array_replace($attrs, $map);
        }
    }
    if (isset($props['attrs']) && is_array($props['attrs'])) {
        $attrs = array_replace($attrs, $props['attrs']);
    }
    return $attrs;
}

/** @param array<string, mixed> $attrs */
function _tag_validate_attrs(string $tag, array $attrs, array $allowed, bool $allowAny): void {
    if ($allowAny) return;
    $common = _tag_common_attrs();
    $allowedSet = array_fill_keys(array_merge($common, $allowed), true);
    foreach ($attrs as $name => $_) {
        if (!is_string($name)) continue;
        if (str_starts_with($name, 'data-') || str_starts_with($name, 'aria-')) continue;
        if (isset($allowedSet[$name])) continue;
        throw new \InvalidArgumentException("Tag <$tag> does not allow attribute '$name'.");
    }
}

/** @param array<int, Node> $children */
function _tag_validate_children(string $tag, array $children, array $allowed): void {
    foreach ($children as $child) {
        if ($child instanceof ElNode) {
            if (!in_array($child->tag, $allowed, true)) {
                throw new \InvalidArgumentException("Tag <$tag> does not allow <{$child->tag}> as a direct child.");
            }
        }
    }
}

/** @param array<int, Node> $children */
function _tag_validate(string $tag, array $props, array $children): void {
    if (in_array($tag, _tag_voids(), true) && count($children) > 0) {
        throw new \InvalidArgumentException("Tag <$tag> does not allow children.");
    }
    $rules = _tag_rules();
    $rule = $rules[$tag] ?? null;
    if ($rule === null) {
        $attrs = _tag_extract_attrs($props);
        _tag_validate_attrs($tag, $attrs, [], false);
        return;
    }
    $attrs = _tag_extract_attrs($props);
    $allowAny = (bool)($rule['attrs_any'] ?? false);
    $allowed = (array)($rule['attrs'] ?? []);
    _tag_validate_attrs($tag, $attrs, $allowed, $allowAny);
    if (isset($rule['children'])) {
        _tag_validate_children($tag, $children, (array)$rule['children']);
    }
}

/**
 * Create <html> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/html
 * Props (attrs): lang, dir, manifest.
 * Children: head, body.
 */
function htmlTag(array $props = [], array $children = []): Node {
    _tag_validate('html', $props, $children);
    return new ElNode('html', $props, $children);
}

/**
 * Create <head> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/head
 * Props (attrs): none beyond common attrs.
 * Children: title, meta, link, style, script, base, noscript.
 */
function head(array $props = [], array $children = []): Node {
    _tag_validate('head', $props, $children);
    return new ElNode('head', $props, $children);
}

/**
 * Create <title> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/title
 * Props (attrs): none beyond common attrs.
 * Children: text.
 */
function title(array $props = [], array $children = []): Node {
    _tag_validate('title', $props, $children);
    return new ElNode('title', $props, $children);
}

/**
 * Create <body> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/body
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function body(array $props = [], array $children = []): Node {
    _tag_validate('body', $props, $children);
    return new ElNode('body', $props, $children);
}

/**
 * Create <div> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/div
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function div(array $props = [], array $children = []): Node {
    _tag_validate('div', $props, $children);
    return new ElNode('div', $props, $children);
}

/**
 * Create <span> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/span
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function span(array $props = [], array $children = []): Node {
    _tag_validate('span', $props, $children);
    return new ElNode('span', $props, $children);
}

/**
 * Create <p> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/p
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function p(array $props = [], array $children = []): Node {
    _tag_validate('p', $props, $children);
    return new ElNode('p', $props, $children);
}

/**
 * Create <h1> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h1
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h1(array $props = [], array $children = []): Node {
    _tag_validate('h1', $props, $children);
    return new ElNode('h1', $props, $children);
}

/**
 * Create <h2> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h2
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h2(array $props = [], array $children = []): Node {
    _tag_validate('h2', $props, $children);
    return new ElNode('h2', $props, $children);
}

/**
 * Create <h3> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h3
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h3(array $props = [], array $children = []): Node {
    _tag_validate('h3', $props, $children);
    return new ElNode('h3', $props, $children);
}

/**
 * Create <h4> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h4
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h4(array $props = [], array $children = []): Node {
    _tag_validate('h4', $props, $children);
    return new ElNode('h4', $props, $children);
}

/**
 * Create <h5> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h5
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h5(array $props = [], array $children = []): Node {
    _tag_validate('h5', $props, $children);
    return new ElNode('h5', $props, $children);
}

/**
 * Create <h6> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/h6
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function h6(array $props = [], array $children = []): Node {
    _tag_validate('h6', $props, $children);
    return new ElNode('h6', $props, $children);
}

/**
 * Create <a> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a
 * Props (attrs): href, target, rel, download, hreflang, type, referrerpolicy, ping.
 * Children: phrasing content.
 */
function a(array $props = [], array $children = []): Node {
    _tag_validate('a', $props, $children);
    return new ElNode('a', $props, $children);
}

/**
 * Create <img> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img
 * Props (attrs): src, alt, width, height, loading, decoding, srcset, sizes, referrerpolicy, fetchpriority, crossorigin, usemap, ismap.
 * Children: none.
 */
function img(array $props = []): Node {
    _tag_validate('img', $props, []);
    return new ElNode('img', $props, []);
}

/**
 * Create <ul> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/ul
 * Props (attrs): none beyond common attrs.
 * Children: li.
 */
function ul(array $props = [], array $children = []): Node {
    _tag_validate('ul', $props, $children);
    return new ElNode('ul', $props, $children);
}

/**
 * Create <ol> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/ol
 * Props (attrs): none beyond common attrs.
 * Children: li.
 */
function ol(array $props = [], array $children = []): Node {
    _tag_validate('ol', $props, $children);
    return new ElNode('ol', $props, $children);
}

/**
 * Create <li> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/li
 * Props (attrs): value.
 * Children: flow content.
 */
function li(array $props = [], array $children = []): Node {
    _tag_validate('li', $props, $children);
    return new ElNode('li', $props, $children);
}

/**
 * Create <table> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/table
 * Props (attrs): border, cellpadding, cellspacing, summary, width.
 * Children: caption, colgroup, thead, tbody, tfoot, tr.
 */
function table(array $props = [], array $children = []): Node {
    _tag_validate('table', $props, $children);
    return new ElNode('table', $props, $children);
}

/**
 * Create <thead> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/thead
 * Props (attrs): none beyond common attrs.
 * Children: tr.
 */
function thead(array $props = [], array $children = []): Node {
    _tag_validate('thead', $props, $children);
    return new ElNode('thead', $props, $children);
}

/**
 * Create <tbody> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/tbody
 * Props (attrs): none beyond common attrs.
 * Children: tr.
 */
function tbody(array $props = [], array $children = []): Node {
    _tag_validate('tbody', $props, $children);
    return new ElNode('tbody', $props, $children);
}

/**
 * Create <tfoot> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/tfoot
 * Props (attrs): none beyond common attrs.
 * Children: tr.
 */
function tfoot(array $props = [], array $children = []): Node {
    _tag_validate('tfoot', $props, $children);
    return new ElNode('tfoot', $props, $children);
}

/**
 * Create <tr> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/tr
 * Props (attrs): none beyond common attrs.
 * Children: th, td.
 */
function tr(array $props = [], array $children = []): Node {
    _tag_validate('tr', $props, $children);
    return new ElNode('tr', $props, $children);
}

/**
 * Create <td> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/td
 * Props (attrs): colspan, rowspan, headers.
 * Children: flow content.
 */
function td(array $props = [], array $children = []): Node {
    _tag_validate('td', $props, $children);
    return new ElNode('td', $props, $children);
}

/**
 * Create <th> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/th
 * Props (attrs): scope, colspan, rowspan, headers, abbr.
 * Children: phrasing content.
 */
function th(array $props = [], array $children = []): Node {
    _tag_validate('th', $props, $children);
    return new ElNode('th', $props, $children);
}

/**
 * Create <form> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/form
 * Props (attrs): action, method, enctype, novalidate, target, autocomplete, name.
 * Children: flow content.
 */
function form(array $props = [], array $children = []): Node {
    _tag_validate('form', $props, $children);
    return new ElNode('form', $props, $children);
}

/**
 * Create <input> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input
 * Props (attrs): type, name, value, placeholder, checked, disabled, readonly, required, min, max, step, minlength, maxlength, pattern, multiple, size, autocomplete, autofocus, list, form, inputmode, accept, capture.
 * Children: none.
 */
function input(array $props = []): Node {
    _tag_validate('input', $props, []);
    return new ElNode('input', $props, []);
}

/**
 * Create <button> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button
 * Props (attrs): type, name, value, disabled, autofocus, form, formaction, formenctype, formmethod, formnovalidate, formtarget.
 * Children: phrasing content.
 */
function button(array $props = [], array $children = []): Node {
    _tag_validate('button', $props, $children);
    return new ElNode('button', $props, $children);
}

/**
 * Create <label> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/label
 * Props (attrs): for, form.
 * Children: phrasing content.
 */
function label(array $props = [], array $children = []): Node {
    _tag_validate('label', $props, $children);
    return new ElNode('label', $props, $children);
}

/**
 * Create <textarea> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/textarea
 * Props (attrs): name, rows, cols, placeholder, disabled, readonly, required, minlength, maxlength, wrap, autocomplete, autofocus, form.
 * Children: text.
 */
function textarea(array $props = [], array $children = []): Node {
    _tag_validate('textarea', $props, $children);
    return new ElNode('textarea', $props, $children);
}

/**
 * Create <select> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/select
 * Props (attrs): name, multiple, size, disabled, required, autofocus, form.
 * Children: option, optgroup.
 */
function select(array $props = [], array $children = []): Node {
    _tag_validate('select', $props, $children);
    return new ElNode('select', $props, $children);
}

/**
 * Create <option> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/option
 * Props (attrs): value, selected, disabled, label.
 * Children: text.
 */
function option(array $props = [], array $children = []): Node {
    _tag_validate('option', $props, $children);
    return new ElNode('option', $props, $children);
}

/**
 * Create <script> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script
 * Props (attrs): src, type, async, defer, crossorigin, integrity, nomodule, referrerpolicy.
 * Children: text.
 */
function script(array $props = [], array $children = []): Node {
    _tag_validate('script', $props, $children);
    return new ElNode('script', $props, $children);
}

/**
 * Create <link> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link
 * Props (attrs): rel, href, as, type, media, crossorigin, integrity, referrerpolicy, sizes, imagesrcset, imagesizes, disabled, fetchpriority, title.
 * Children: none.
 */
function linkTag(array $props = []): Node {
    _tag_validate('link', $props, []);
    return new ElNode('link', $props, []);
}

/**
 * Create <meta> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
 * Props (attrs): charset, name, content, http-equiv, property.
 * Children: none.
 */
function meta(array $props = []): Node {
    _tag_validate('meta', $props, []);
    return new ElNode('meta', $props, []);
}

/**
 * Create <style> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/style
 * Props (attrs): type, media, nonce.
 * Children: text.
 */
function styleTag(array $props = [], array $children = []): Node {
    _tag_validate('style', $props, $children);
    return new ElNode('style', $props, $children);
}

/**
 * Create <header> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/header
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function header(array $props = [], array $children = []): Node {
    _tag_validate('header', $props, $children);
    return new ElNode('header', $props, $children);
}

/**
 * Create <footer> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/footer
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function footer(array $props = [], array $children = []): Node {
    _tag_validate('footer', $props, $children);
    return new ElNode('footer', $props, $children);
}

/**
 * Create <nav> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/nav
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function nav(array $props = [], array $children = []): Node {
    _tag_validate('nav', $props, $children);
    return new ElNode('nav', $props, $children);
}

/**
 * Create <main> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/main
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function main(array $props = [], array $children = []): Node {
    _tag_validate('main', $props, $children);
    return new ElNode('main', $props, $children);
}

/**
 * Create <section> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/section
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function section(array $props = [], array $children = []): Node {
    _tag_validate('section', $props, $children);
    return new ElNode('section', $props, $children);
}

/**
 * Create <article> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/article
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function article(array $props = [], array $children = []): Node {
    _tag_validate('article', $props, $children);
    return new ElNode('article', $props, $children);
}

/**
 * Create <aside> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/aside
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function aside(array $props = [], array $children = []): Node {
    _tag_validate('aside', $props, $children);
    return new ElNode('aside', $props, $children);
}

/**
 * Create <figure> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/figure
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function figure(array $props = [], array $children = []): Node {
    _tag_validate('figure', $props, $children);
    return new ElNode('figure', $props, $children);
}

/**
 * Create <figcaption> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/figcaption
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function figcaption(array $props = [], array $children = []): Node {
    _tag_validate('figcaption', $props, $children);
    return new ElNode('figcaption', $props, $children);
}

/**
 * Create <strong> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/strong
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function strong(array $props = [], array $children = []): Node {
    _tag_validate('strong', $props, $children);
    return new ElNode('strong', $props, $children);
}

/**
 * Create <em> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/em
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function em(array $props = [], array $children = []): Node {
    _tag_validate('em', $props, $children);
    return new ElNode('em', $props, $children);
}

/**
 * Create <b> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/b
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function b(array $props = [], array $children = []): Node {
    _tag_validate('b', $props, $children);
    return new ElNode('b', $props, $children);
}

/**
 * Create <i> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/i
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function i(array $props = [], array $children = []): Node {
    _tag_validate('i', $props, $children);
    return new ElNode('i', $props, $children);
}

/**
 * Create <small> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/small
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function small(array $props = [], array $children = []): Node {
    _tag_validate('small', $props, $children);
    return new ElNode('small', $props, $children);
}

/**
 * Create <br> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/br
 * Props (attrs): none beyond common attrs.
 * Children: none.
 */
function br(array $props = []): Node {
    _tag_validate('br', $props, []);
    return new ElNode('br', $props, []);
}

/**
 * Create <hr> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/hr
 * Props (attrs): none beyond common attrs.
 * Children: none.
 */
function hr(array $props = []): Node {
    _tag_validate('hr', $props, []);
    return new ElNode('hr', $props, []);
}

/**
 * Create <pre> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/pre
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function pre(array $props = [], array $children = []): Node {
    _tag_validate('pre', $props, $children);
    return new ElNode('pre', $props, $children);
}

/**
 * Create <code> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/code
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function code(array $props = [], array $children = []): Node {
    _tag_validate('code', $props, $children);
    return new ElNode('code', $props, $children);
}

/**
 * Create <blockquote> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
 * Props (attrs): cite.
 * Children: flow content.
 */
function blockquote(array $props = [], array $children = []): Node {
    _tag_validate('blockquote', $props, $children);
    return new ElNode('blockquote', $props, $children);
}

/**
 * Create <dl> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dl
 * Props (attrs): none beyond common attrs.
 * Children: dt, dd.
 */
function dl(array $props = [], array $children = []): Node {
    _tag_validate('dl', $props, $children);
    return new ElNode('dl', $props, $children);
}

/**
 * Create <dt> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dt
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function dt(array $props = [], array $children = []): Node {
    _tag_validate('dt', $props, $children);
    return new ElNode('dt', $props, $children);
}

/**
 * Create <dd> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dd
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function dd(array $props = [], array $children = []): Node {
    _tag_validate('dd', $props, $children);
    return new ElNode('dd', $props, $children);
}

/**
 * Create <video> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video
 * Props (attrs): src, controls, autoplay, loop, muted, playsinline, poster, preload, width, height, crossorigin, controlslist, disablepictureinpicture, disableremoteplayback.
 * Children: source, track.
 */
function video(array $props = [], array $children = []): Node {
    _tag_validate('video', $props, $children);
    return new ElNode('video', $props, $children);
}

/**
 * Create <audio> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/audio
 * Props (attrs): src, controls, autoplay, loop, muted, preload, crossorigin, controlslist.
 * Children: source, track.
 */
function audio(array $props = [], array $children = []): Node {
    _tag_validate('audio', $props, $children);
    return new ElNode('audio', $props, $children);
}

/**
 * Create <source> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/source
 * Props (attrs): src, type, media, srcset, sizes.
 * Children: none.
 */
function source(array $props = []): Node {
    _tag_validate('source', $props, []);
    return new ElNode('source', $props, []);
}

/**
 * Create <canvas> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas
 * Props (attrs): width, height.
 * Children: text (fallback).
 */
function canvas(array $props = [], array $children = []): Node {
    _tag_validate('canvas', $props, $children);
    return new ElNode('canvas', $props, $children);
}

/**
 * Create <svg> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/svg
 * Props (attrs): SVG attrs (e.g., width, height, viewBox, xmlns, fill, stroke).
 * Children: svg content.
 */
function svg(array $props = [], array $children = []): Node {
    _tag_validate('svg', $props, $children);
    return new ElNode('svg', $props, $children);
}

/**
 * Create <picture> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/picture
 * Props (attrs): none beyond common attrs.
 * Children: source, img.
 */
function picture(array $props = [], array $children = []): Node {
    _tag_validate('picture', $props, $children);
    return new ElNode('picture', $props, $children);
}

/**
 * Create <details> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details
 * Props (attrs): open.
 * Children: summary plus flow content.
 */
function details(array $props = [], array $children = []): Node {
    _tag_validate('details', $props, $children);
    return new ElNode('details', $props, $children);
}

/**
 * Create <summary> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/summary
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function summary(array $props = [], array $children = []): Node {
    _tag_validate('summary', $props, $children);
    return new ElNode('summary', $props, $children);
}

/**
 * Create <dialog> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog
 * Props (attrs): open.
 * Children: flow content.
 */
function dialog(array $props = [], array $children = []): Node {
    _tag_validate('dialog', $props, $children);
    return new ElNode('dialog', $props, $children);
}

/**
 * Create <fieldset> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/fieldset
 * Props (attrs): disabled, form, name.
 * Children: legend plus flow content.
 */
function fieldset(array $props = [], array $children = []): Node {
    _tag_validate('fieldset', $props, $children);
    return new ElNode('fieldset', $props, $children);
}

/**
 * Create <legend> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/legend
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function legend(array $props = [], array $children = []): Node {
    _tag_validate('legend', $props, $children);
    return new ElNode('legend', $props, $children);
}

/**
 * Create <optgroup> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/optgroup
 * Props (attrs): label, disabled.
 * Children: option.
 */
function optgroup(array $props = [], array $children = []): Node {
    _tag_validate('optgroup', $props, $children);
    return new ElNode('optgroup', $props, $children);
}

/**
 * Create <datalist> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/datalist
 * Props (attrs): none beyond common attrs.
 * Children: option.
 */
function datalist(array $props = [], array $children = []): Node {
    _tag_validate('datalist', $props, $children);
    return new ElNode('datalist', $props, $children);
}

/**
 * Create <output> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/output
 * Props (attrs): for, form, name.
 * Children: phrasing content.
 */
function output(array $props = [], array $children = []): Node {
    _tag_validate('output', $props, $children);
    return new ElNode('output', $props, $children);
}

/**
 * Create <progress> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/progress
 * Props (attrs): value, max.
 * Children: phrasing content.
 */
function progress(array $props = [], array $children = []): Node {
    _tag_validate('progress', $props, $children);
    return new ElNode('progress', $props, $children);
}

/**
 * Create <meter> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meter
 * Props (attrs): value, min, max, low, high, optimum.
 * Children: phrasing content.
 */
function meter(array $props = [], array $children = []): Node {
    _tag_validate('meter', $props, $children);
    return new ElNode('meter', $props, $children);
}

/**
 * Create <time> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/time
 * Props (attrs): datetime.
 * Children: phrasing content.
 */
function time(array $props = [], array $children = []): Node {
    _tag_validate('time', $props, $children);
    return new ElNode('time', $props, $children);
}

/**
 * Create <mark> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/mark
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function mark(array $props = [], array $children = []): Node {
    _tag_validate('mark', $props, $children);
    return new ElNode('mark', $props, $children);
}

/**
 * Create <abbr> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/abbr
 * Props (attrs): title.
 * Children: phrasing content.
 */
function abbr(array $props = [], array $children = []): Node {
    _tag_validate('abbr', $props, $children);
    return new ElNode('abbr', $props, $children);
}

/**
 * Create <cite> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function cite(array $props = [], array $children = []): Node {
    _tag_validate('cite', $props, $children);
    return new ElNode('cite', $props, $children);
}

/**
 * Create <kbd> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/kbd
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function kbd(array $props = [], array $children = []): Node {
    _tag_validate('kbd', $props, $children);
    return new ElNode('kbd', $props, $children);
}

/**
 * Create <samp> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/samp
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function samp(array $props = [], array $children = []): Node {
    _tag_validate('samp', $props, $children);
    return new ElNode('samp', $props, $children);
}

/**
 * Create <var> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/var
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function varTag(array $props = [], array $children = []): Node {
    _tag_validate('var', $props, $children);
    return new ElNode('var', $props, $children);
}

/**
 * Create <sup> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/sup
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function sup(array $props = [], array $children = []): Node {
    _tag_validate('sup', $props, $children);
    return new ElNode('sup', $props, $children);
}

/**
 * Create <sub> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/sub
 * Props (attrs): none beyond common attrs.
 * Children: phrasing content.
 */
function sub(array $props = [], array $children = []): Node {
    _tag_validate('sub', $props, $children);
    return new ElNode('sub', $props, $children);
}

/**
 * Create <iframe> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe
 * Props (attrs): src, title, name, width, height, allow, allowfullscreen, loading, referrerpolicy, sandbox, srcdoc.
 * Children: text (fallback).
 */
function iframe(array $props = [], array $children = []): Node {
    _tag_validate('iframe', $props, $children);
    return new ElNode('iframe', $props, $children);
}

/**
 * Create <embed> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/embed
 * Props (attrs): src, type, width, height.
 * Children: none.
 */
function embed(array $props = []): Node {
    _tag_validate('embed', $props, []);
    return new ElNode('embed', $props, []);
}

/**
 * Create <object> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/object
 * Props (attrs): data, type, name, usemap, width, height, form.
 * Children: param plus fallback.
 */
function objectTag(array $props = [], array $children = []): Node {
    _tag_validate('object', $props, $children);
    return new ElNode('object', $props, $children);
}

/**
 * Create <param> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/param
 * Props (attrs): name, value.
 * Children: none.
 */
function paramTag(array $props = []): Node {
    _tag_validate('param', $props, []);
    return new ElNode('param', $props, []);
}

/**
 * Create <noscript> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/noscript
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function noscript(array $props = [], array $children = []): Node {
    _tag_validate('noscript', $props, $children);
    return new ElNode('noscript', $props, $children);
}

/**
 * Create <template> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/template
 * Props (attrs): none beyond common attrs.
 * Children: any.
 */
function template(array $props = [], array $children = []): Node {
    _tag_validate('template', $props, $children);
    return new ElNode('template', $props, $children);
}

/**
 * Create <address> element node.
 * MDN: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/address
 * Props (attrs): none beyond common attrs.
 * Children: flow content.
 */
function address(array $props = [], array $children = []): Node {
    _tag_validate('address', $props, $children);
    return new ElNode('address', $props, $children);
}

