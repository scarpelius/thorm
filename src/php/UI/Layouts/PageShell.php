<?php
declare(strict_types=1);

namespace Thorm\UI\Layouts;

use InvalidArgumentException;
use Thorm\IR\Node\Node;

use function Thorm\attrs;
use function Thorm\cls;
use function Thorm\el;
use function Thorm\fragment;
use function Thorm\text;

final class PageShell
{
    private static array $props = [];

    public static function get(array $slots, array $props = []): Node
    {
        $nodes = [];

        if (count($props) > 0) {
            self::$props = $props;
        }

        if (count($slots) === 0) {
            throw new InvalidArgumentException('PageShell: slots needs at leas one element');
        }

        foreach($slots as $key => $value) {
            $value = count($value) > 0 ? $value : [text('Lorem ipsum')];
            switch($key) {
                case 'header':
                    $nodes[] = self::_header($value);
                    break;
                case 'main':
                    $nodes[] = self::_main($value);
                    break;
                case 'footer':
                    $nodes[] = self::_footer($value);
                    break;
            }
        }

        return fragment($nodes);
    }
    
    private static function _header(array $children): Node {
        return el('div', [
            attrs(['id' => 'header'])
        ], $children);
    }

    private static function _main(array $children): Node {
        $props[] = attrs(['id' => 'main']);
        if (array_key_exists('main', self::$props)) {
            $props[] = cls(self::$props['main']['style']['main']);
        }
        
        return el('main', $props, $children);
    }

    private static function _footer(array $children): Node {
        return el('div', [
            attrs(['id' => 'footer'])
        ], $children);
    }
}
