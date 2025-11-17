<?php
declare(strict_types=1);

namespace Hronic\Components;

use function PhpJs\{el,text,attrs,cls};
use PhpJs\IR\Node\Node;

class Header
{
    public function __construct(){}

    public static function get(): Node
    {
        $logo = el('a', [
            attrs([
                'href'  => '/pages/hronic/hronic.html',
                'id'    => 'header-top-logo-link',
            ]),
        ], [
            el('img', [
                cls('img-fluid'),
                attrs(['src' => 'images/logo.webp']),
            ]),
        ]);

        $top_user = el('a', [
            cls('material-symbols-outlined material-filled fs-1 text-primary'),
            attrs([
                'href'  => 'user',
                'id'    => 'header-top-user',
                'data-bs-toggle' => '',
                'data-bs-target' => '#login-box',
            ]),
        ], [
            text('account_circle'),
        ]);

        $component = el('div', [
            cls('container-fluid fixed-top p-3 d-flex justify-content-between header'),
        ], [
            $logo,
            $top_user,
        ]);
        

        return $component;
    }
}
