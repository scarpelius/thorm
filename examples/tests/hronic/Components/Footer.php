<?php
declare(strict_types=1);

namespace Hronic\Components;

use function PhpJs\{state,el,text,on,eq,read,val,concat,addTo,set,attrs,num,ev,cls,repeat,item,cond};
use PhpJs\IR\Node\Node;

class Footer
{
    public function __construct(){}

    public static function get(): Node
    {
        $uri = '/';
        $menu_items = state([
            ['id'=> 'home-menu-item', 'link' => '/', 'icon' => 'home', 'text' => 'Home'],
            ['id'=> 'povesti-menu-item', 'link' => '/pove%C8%99ti', 'icon' => 'two_pager', 'text' => 'Povești'],
            ['id'=> 'steaua_polara-menu-item', 'link' => 'steaua_polara', 'icon' => 'rocket_launch', 'text' => 'Cenaclu'],
            ['id'=> 'contact-menu-item', 'link' => '/contact', 'icon' => 'groups', 'text' => 'Contact'],
        ]);

        $menu_item = el('li', [
            cls('nav-item mx-2'),
            attrs(['style' => 'max-width:64px;']),
        ], [
            el('a', [
                cls(concat(
                    cond(
                        eq(item('link'), val($uri)),
                        'text-primary ',
                        ''
                    ),
                    'd-flex align-items-center flex-column'
                )),
                attrs([ 'href' => item('link'), 'id' => item('id') ]),
            ], [
                el('div', [ 
                    cls(concat(
                        cond(
                            eq(item('link'), val($uri)),
                            'icon-fill ',
                            ''
                        ),
                        'material-symbols-outlined'
                    )), 
                    attrs(['id'=> concat(item('id'),'-icon'), 'width'=>'64', 'height'=>'64', 'style'=>'font-size:64px' ])
                ], [
                    text(item('icon'))
                ]),
                el('div', [cls('menu-text fs-5'), ['style:line-height:30px']], [text(item('text'))]),
            ]),
        ]);

        $component = el('ul', [
            cls('nav justify-content-center fixed-bottom bg-body-tertiary py-0'),
            attrs([ 'id' => 'main-menu']),
        ], [
            repeat(
                read($menu_items),
                item('id'),
                $menu_item
            ),
        ]);

        return $component;
    }
}
