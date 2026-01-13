<?php
declare(strict_types=1);

namespace Thorm\UI\Components;

use Thorm\IR\Node\Node;

use function Thorm\attrs;
use function Thorm\cls;
use function Thorm\cond;
use function Thorm\el;
use function Thorm\read;
use function Thorm\text;

final class Nav
{
    public static function get(array $slots, array $props = []): Node
    {
        $children = [];
        if(in_array('brand', $slots)) {
            $children[] = el('a', [
                cls($props['style']['brand-anchor']),
                attrs([
                    'href'  => '/',
                ]),
            ], [
                el('img', [ 
                    cls($props['style']['brand-image']),
                    attrs([
                        'src'   => $props['brand-image'],
                        'style' => 'width: 64px; height: 64px;',
                    ]),
                ]),
                el('span', [], [ text('Thorm') ]),
            ]);
        }
        if(in_array('toggle-nav', $slots)) {
            $children[] = el('button', [
                cls('navbar-toggler'),
                attrs([
                    'type'  => 'button',
                    'data-bs-toggle'    => 'collapse',
                    'data-bs-target'    => '#thormNavbar',
                    'aria-controls'     => 'thormNavbar',
                    'aria-expanded'     => 'false',
                    'aria-label'        => 'Toggle navigation',
                ]),
            ], [
                el('span', [ cls('navbar-toggler-icon') ]),
            ]);
        }
        if(in_array('navbar', $slots)) {
            $listItems = [];
            foreach($props['navbar-list'] as $listItem) {
                $items = [];
                $items[] = el('a', [
                    cls('nav-link'),
                    attrs(['href' => $listItem['href']])
                ], [text($listItem['text'])]);
                if(array_key_exists('submenu', $listItem)) {
                    $items[] = self::_submenu($listItem['submenu']);
                }
                $listItems[] = el('li', [cls('nav-item')], $items);
            }
            
            $navbarChildren[] = el('ul', [ 
                cls($props['style']['navbar-list']) 
            ], $listItems);

            if(array_key_exists('right-side-actions', $props)){
                $actions = [];
                foreach($props['right-side-actions'] as $action){
                    $actions[] = el('a', [
                        cls(cond(read($props['atoms']['visible']), $action['style1'], $action['style2'])),
                        attrs($action['attrs'])
                    ], [
                        text($action['text'])
                    ]);
                }
                $navbarChildren[] = el('div', [cls('d-flex gap-2')], $actions);
            }
            $children[] = el('div', [
                cls($props['style']['navbar']),
                attrs(['id' => 'thormNavbar']),
            ], $navbarChildren);
        }

        $node = el('nav', [
            cls(cond(read($props['atoms']['visible']), $props['style']['nav1'], $props['style']['nav2'])),
            attrs([
                'data-bs-theme' => cond(read($props['atoms']['visible']), $props['style']['nav1-bs-theme'], $props['style']['nav2-bs-theme'])
            ]),
        ], [
            el('div', [
                cls('container')
            ], $children),
        ]);

        return $node;
    }

    /**
     * Recursive build the submenu
     */
    private static function _submenu(array $menu): Node
    {
        $return = [];
        foreach($menu as $listItem) {
            $items = [];
            $items[] = text($listItem['text']);
            if(array_key_exists('submenu', $listItem)) {
                $items[] = self::_submenu($listItem['submenu']);
            }

            if(array_key_exists('title', $listItem)){
                $return[] = el('li', [cls('nav-item')], [
                    el('div', [
                        cls('text-uppercase small text-secondary my-2'),
                    ], [
                        text($listItem['text'])
                    ])
                ]);
            } else {
                $return[] = el('li', [cls('nav-item ps-2')], [
                    el('a', [
                        cls('nav-link py-1'),
                        attrs(['href' => $listItem['href']]),
                    ], $items)
                ]);
            }
        }
        
        return el('ul', [
            cls('navbar-nav ms-3 mt-1 d-lg-none'),
        ], $return);
    }
}
