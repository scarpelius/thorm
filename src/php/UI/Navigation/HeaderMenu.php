<?php
declare(strict_types=1);

namespace Thorm\UI\Navigation;

use Thorm\IR\Node\Node;
use Thorm\UI\Components\Nav;

use function Thorm\attrs;
use function Thorm\cls;
use function Thorm\el;
use function Thorm\onLeftViewport;
use function Thorm\onVisible;
use function Thorm\selectorTarget;
use function Thorm\set;
use function Thorm\state;
use function Thorm\val;

final class HeaderMenu
{
    public static function get(array $props = []): Node
    {
        $visible = state(true);
        $slots = ['brand', 'toggle-nav', 'navbar'];
        $props = [
            'style' => [
                'brand-anchor'  => 'navbar-brand d-flex align-items-center gap-2 fw-semibold',
                'brand-image'   => '',
                'nav1'          => 'navbar navbar-expand-lg p-0 site-header',
                'nav1-bs-theme' => 'dark',
                'nav2'          => 'navbar navbar-expand-lg p-0 bg-thorm-header',
                'nav2-bs-theme' => 'dark',
                'navbar'        => 'collapse navbar-collapse',
                'navbar-list'   => 'navbar-nav me-lg-auto mb-2 mb-lg-0',
            ],
            'brand-image'   => 'images/thorm-builder_64x64.webp',
            'navbar-list'   => $props['menu'],
            'right-side-actions' => [
                [
                    'style1' => 'btn btn-warning',
                    'style2' => 'btn btn-warning',
                    'attrs' => ['href' => '/docs/getting-started',],
                    'text'  => 'Get Started',
                ],
                [
                    'style1' => 'btn btn-secondary', 
                    'style2' => 'btn btn-secondary', 
                    'attrs' => [
                        'href'      => 'https://github.com/scarpelius/thorm',
                        'target'    => '_blank',
                        'rel'       => 'noopener'
                    ],
                    'text' => 'GitHub',
                ],
            ],
            'atoms' => [
                'visible' => $visible,
            ],
        ];
        $node = el('header', [
            cls('fixed-top shadow'),
            attrs(['id'=>'siteHeader']),
        ], [
            Nav::get($slots, $props),
            // EFFECT: when #sentinel becomes visible, set status
            onVisible(
                [ set($visible, val(true), true) ],   // asAction = true
                threshold: 0.1,
                rootMargin: '0px 0px -10% 0%',
                target: selectorTarget('#sentinel')
            ),
            // EFFECT: when #sentinel goes out of viewport, set status
            onLeftViewport(
                [ set($visible, val(false), true) ],   // asAction = true
                threshold: 0.1,
                rootMargin: '0px',
                target: selectorTarget('#sentinel')
            ),
        ]);

        return $node;
    }
}
